<?php

declare(strict_types=1);

namespace App\StateProcessors;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Step\ProductStep;
use App\Message\DppCreateProcessMessage;
use App\Repository\Step\ProductStepRepository;
use App\Services\Qr\QrService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @template T1 of ProductStep
 * @template T2 of ProductStep
 * @implements ProcessorInterface<ProductStep, ProductStep>
 */
readonly class UpdateProductStepProcessor implements ProcessorInterface
{
    /**
     * @param ProductStepRepository<ProductStep> $productStepRepository
     */
    public function __construct(
        private EntityManagerInterface $entityManager,
        private QrService $qrService,
        private MessageBusInterface $messageBus,
        private RequestStack $requestStack,
        private ProductStepRepository $productStepRepository,
    ) {
    }

    /**
     * @param ProductStep $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if (!$operation instanceof Patch) {
            throw new BadRequestException();
        }

        $this->entityManager->persist($data);

        $qrId = $this->qrService->generateQrId($data, $this->entityManager);

        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            throw new BadRequestException('Request not available.');
        }

        $requestData = json_decode($request->getContent(), true);

        if (!is_array($requestData)) {
            throw new BadRequestException('Request data is empty or invalid JSON.');
        }

        if (array_key_exists('unitMeasurement', $requestData)) {
            $data->setUnitMeasurement((string) $requestData['unitMeasurement']);
        }

        if (array_key_exists('measurementType', $requestData)) {
            $data->setMeasurementType((string) $requestData['measurementType']);
        }

        if (array_key_exists('measurementValue', $requestData)) {
            $data->setMeasurementValue((float) $requestData['measurementValue']);
        }

        if ($qrId === null && !$this->hasMeasurementData($requestData)) {
            throw new ConflictHttpException(get_class($data) . ' with given qrId already exists.');
        }

        $this->initTransactions($data, $requestData);

        if (array_key_exists('productStepsWithConsumption', $requestData)) {
            $this->updateConsumptionsInPreviousProductSteps($requestData);
        }

        if ($data->isCreateQr() || $data->isCreateEmptyDpp()) {
            if ($qrId === null && ($data->getQrImage() === null || $data->getQrImage() === '')) {
                throw new ConflictHttpException(get_class($data) . ' with given qrId already exists.');
            }

            $data->setQrId($qrId);
        }

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        if ($data->isCreateQr() || $data->isCreateEmptyDpp()) {
            $this->messageBus->dispatch(new DppCreateProcessMessage($data->getId(), ProductStep::class));
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $requestData
     */
    private function hasMeasurementData(array $requestData): bool
    {
        $requiredKeys = ['unitMeasurement', 'measurementType', 'measurementValue'];

        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $requestData) || $requestData[$key] === null) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<string, mixed> $requestData
     */
    private function initTransactions(ProductStep $productStep, array $requestData): void
    {
        if ($productStep->getQuantityIndex() === 0 && $this->hasMeasurementData($requestData)) {
            if (!isset($requestData['measurementValue']) || !is_numeric($requestData['measurementValue'])) {
                throw new BadRequestException('Invalid measurement value.');
            }

            $initializationValues = ['value' => (float) $requestData['measurementValue'], 'inputs' => []];

            foreach ($productStep->getProductInputs() as $productInput) {
                if ($productInput->getType() === 'numerical') {
                    $initializationValues['inputs'][] = ['uuid' => $productInput->getId()->jsonSerialize(), 'value' => $productInput->getNumericalValue()];
                }
            }

            $transactions = [
                'initializationValues' => $initializationValues,
                'consumptions' => [],
                'remainingValues' => $initializationValues,
            ];

            $productStep->setTransactions($transactions);
        }
    }

    /**
     * @param array{
     *     productStepsWithConsumption: array<int, array{
     *         uuid: string,
     *         value: float,
     *         inputs: array<int, array{
     *             uuid: string,
     *             value: float
     *         }>
     *     }>
     * } $requestData
     */
    private function updateConsumptionsInPreviousProductSteps(array $requestData): void
    {
        foreach ($requestData['productStepsWithConsumption'] as $productStepWithConsumption) {
            $productStep = $this->productStepRepository->find($productStepWithConsumption['uuid']);

            if (!$productStep) {
                throw new NotFoundHttpException('ProductStep from consumption not found.');
            }

            $this->updateConsumptionsInProductStep($productStep, $productStepWithConsumption);
        }
    }

    /**
     * @param array{uuid: string, value: float, inputs: array<int, array{uuid: string, value: float}>} $consumption
     */
    private function updateConsumptionsInProductStep(ProductStep $productStep, array $consumption): void
    {
        $productStepInputs = $productStep->getProductInputs();
        $productStepTransactions = $productStep->getTransactions();

        if (!empty($productStepTransactions)) {
            if ($productStepTransactions['remainingValues']['value'] < $consumption['value']) {
                throw new BadRequestException('Quantity is higher than remaining amount.');
            }

            $measurementValue = $productStep->getMeasurementValue();
            if ($measurementValue == 0) {
                throw new BadRequestException('Measurement value cannot be zero.');
            }

            $ratioForAutomaticCalculation = $consumption['value'] / $measurementValue;
            $productStepTransactions['remainingValues']['value'] -= $consumption['value'];

            foreach ($consumption['inputs'] as $input) {
                if (trim($input['uuid']) === '') {
                    throw new BadRequestException('UUID cannot be empty.');
                }
            }

            foreach ($productStepTransactions['remainingValues']['inputs'] as &$productStepInputFromTransactions) {
                foreach ($consumption['inputs'] as &$input) {
                    if ($input['uuid'] === $productStepInputFromTransactions['uuid']) {
                        $productStepInput = $productStepInputs->filter(function ($productInput) use ($input) {
                            return $productInput->getId()->jsonSerialize() === $input['uuid'];
                        })->first();

                        if ($productStepInput) {
                            if ($productStepInput->isAutomaticCalculation()) {
                                $calculatedValueForConsumption = $ratioForAutomaticCalculation * $productStepInput->getNumericalValue();
                                $input['value'] = $calculatedValueForConsumption;

                                if ($productStepInputFromTransactions['value'] < $input['value']) {
                                    throw new BadRequestException('Input value is higher than remaining amount.');
                                }

                                $productStepInputFromTransactions['value'] -= $input['value'];
                            }
                        }
                    }
                }
            }

            $productStepTransactions['consumptions'][] = $consumption;

            $productStep->setTransactions($productStepTransactions);
            $this->entityManager->persist($productStep);
        }
    }
}

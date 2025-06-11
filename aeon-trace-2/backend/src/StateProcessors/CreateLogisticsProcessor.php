<?php

declare(strict_types=1);

namespace App\StateProcessors;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Dpp\Dpp;
use App\Entity\Logistics\Logistics;
use App\Services\Qr\QrService;
use App\Services\Security\TimestampService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Vich\UploaderBundle\Handler\UploadHandler;

/**
 * @template T1 of object
 * @template T2 of object
 * @implements ProcessorInterface<T1, T2>
 */
readonly class CreateLogisticsProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private QrService $qrService,
        private TimestampService $timestampService,
        private UploadHandler $uploadHandler,
    ) {
    }

    /**
     * @param Logistics $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Logistics
    {
        $this->entityManager->persist($data);
        $this->entityManager->flush();

        if (!$data->getFromDpps()->isEmpty()) {
            foreach ($data->getFromDpps() as $dpp) {
                $data->addFromDpp($dpp);
            }
        }

        $logisticsUuid = $data->getId();
        $logisticsName = "{$logisticsUuid} - {$data->getName()}";
        $data->setName($logisticsName);

        $qrId = $this->qrService->generateQrId($data, $this->entityManager);
        if ($qrId === null) {
            throw new ConflictHttpException(get_class($data) . ' with given qrId already exists.');
        }

        $dpps = $data->getFromDpps();

        foreach ($dpps as $dpp) {
            $productSteps = $dpp->getProductSteps();
            foreach ($productSteps as $step) {
                $step->setState(Dpp::STATE_LOGISTICS);

                $this->entityManager->persist($step);
            }
        }

        $data->setQrId($qrId);
        $timeStamp = $this->timestampService->createTimestamp($data);
        if ($timeStamp instanceof DateTime) {
            $data->setTsaVerifiedAt($timeStamp);
        }

        $qr = $this->qrService->generateQrCode($data);
        $data->setQrFile($qr);
        $this->uploadHandler->inject($data, 'qrFile');
        $this->entityManager->flush();

        return $data;
    }
}

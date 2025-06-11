<?php

declare(strict_types=1);

namespace App\Controller\Dpp;

use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\DataTransferObjects\Product\CreateProductInput;
use App\Entity\Company\Company;
use App\Entity\Dpp\Dpp;
use App\Entity\Dpp\DppConnector;
use App\Entity\Logistics\Logistics;
use App\Entity\Product\Product;
use App\Entity\Product\ProductInput;
use App\Entity\Product\ProductTemplate;
use App\Entity\Step\ProductStep;
use App\Entity\SupplyChain\Node;
use App\Entity\SupplyChain\SupplyChainTemplate;
use App\Services\Security\TimestampService;
use App\StateProcessors\CreateProductProcessor;
use App\StateProcessors\DppCreateProcessor;
use App\StateProcessors\UpdateProductStepProcessor;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class JsonStepsToDppController extends AbstractController
{
    /**
     * @param UpdateProductStepProcessor<ProductStep, ProductStep> $updateProcessor
     * @param DppCreateProcessor<Dpp, Dpp> $dppCreateProcessor
     * @param CreateProductProcessor<CreateProductInput, Product> $createProductProcessor
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UpdateProductStepProcessor $updateProcessor,
        private readonly TimestampService $timestampService,
        private readonly DppCreateProcessor $dppCreateProcessor,
        private readonly CreateProductProcessor $createProductProcessor,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $content = json_decode($request->getContent());
        $jsonResponse = json_encode(['success' => 'Inputs added successfully.']);
        $responseCode = Response::HTTP_OK;

        if (!is_object($content)) {
            throw new InvalidArgumentException('Invalid JSON: Expected a creation body object.');
        }

        if (!isset($content->dpp)) {
            throw new InvalidArgumentException('Invalid JSON: Expected DPP object.');
        }

        $dppBody = $content->dpp;

        if (
            !property_exists($dppBody, 'id')
            || !property_exists($dppBody, 'steps')
            || $dppBody->id !== null && !is_string($dppBody->id)
            || !is_array($dppBody->steps)
            || count($dppBody->steps) == 0
        ) {
            throw new InvalidArgumentException('Invalid data in JSON structure.');
        }

        $dpp = null;
        if ($dppBody->id !== null) {
            $dpp = $this->entityManager->find(Dpp::class, $dppBody->id);
        }

        if ($dpp === null) {
            $productsForDpp = [];
            $productStepsForDpp = [];
            foreach ($dppBody->steps as $step) {
                if (!array_key_exists((string) $step->productTemplate, $productsForDpp)) {
                    $productTemplate = $this->entityManager->find(ProductTemplate::class, $step->productTemplate);

                    if ($productTemplate === null) {
                        continue;
                    }

                    $createProductInput = new CreateProductInput($productTemplate, [$step->id], $dppBody->node, $dppBody->company);

                    $post = new Post();
                    $product = $this->createProductProcessor->process($createProductInput, $post);
                } else {
                    $product = $productsForDpp[(string) $step->productTemplate];
                }

                $productStepsResult = $this->entityManager->getRepository(ProductStep::class)->findBy([
                    'product' => $product->getId(),
                    'stepTemplateReference' => $step->id,
                ]);

                $productStepsForDpp = array_merge($productStepsForDpp, $productStepsResult);
                $productsForDpp[(string) $step->productTemplate] = $product;
            }

            $company = $this->entityManager->find(Company::class, $dppBody->company);
            if ($company === null) {
                throw new InvalidArgumentException("Company with ID {$dppBody->company} not found.");
            }

            $node = $this->entityManager->find(Node::class, $dppBody->node);
            if ($node === null) {
                throw new InvalidArgumentException("Node with ID {$dppBody->node} not found.");
            }

            $logistics = [];
            foreach ($dppBody->logistics as $logisticId) {
                $logistic = $this->entityManager->find(Logistics::class, $logisticId);

                if (!$logistic) {
                    throw new InvalidArgumentException("Logistic with ID {$logisticId} not found.");
                }

                $logistics[] = $logistic;
            }

            $fromDpps = [];
            foreach ($dppBody->fromDpps as $sourceDpp) {
                $fromDpp = $this->entityManager->find(Dpp::class, $sourceDpp);

                if (!$fromDpp) {
                    throw new InvalidArgumentException("From Dpp with ID {$sourceDpp} not found.");
                }

                $fromDpps[] = $fromDpp;
            }

            $supplyChainTemplate = $this->entityManager->find(SupplyChainTemplate::class, $dppBody->supplyChainTemplate);
            if ($supplyChainTemplate === null) {
                throw new InvalidArgumentException("Node with ID {$dppBody->supplyChainTemplate} not found.");
            }

            $dpp = new Dpp();
            $dpp->setCreateQr(true);
            $dpp->setOngoingDpp(true);
            $dpp->setCreateEmptyDpp(false);
            $dpp->setLocked(false);
            $dpp->setDescription(isset($dppBody->dppDescription) ? $dppBody->dppDescription : '');
            $dpp->setCompany($company);
            $dpp->setSupplyChainTemplate($supplyChainTemplate);
            $dpp->setNode($node);

            foreach ($logistics as $logistic) {
                $dpp->addMaterialsReceivedFrom($logistic);
            }

            $post = new Post();
            $dpp = $this->dppCreateProcessor->process($dpp, $post);

            foreach ($fromDpps as $fromDpp) {
                $dppConnector = new DppConnector();
                $dppConnector->setTargetDpp($dpp);
                $dppConnector->setSourceDpp($fromDpp);

                $this->entityManager->persist($dppConnector);
            }

            foreach ($productsForDpp as $product) {
                $product->setDpp($dpp);
                $this->entityManager->persist($product);
            }

            /** @var ProductStep $productStep */
            foreach ($productStepsForDpp as $productStep) {
                $productStep->setDpp($dpp);
                foreach ($dppBody->steps as $step) {
                    if ((string) $productStep->getStepTemplateReference()->getId() === $step->id && $productStep->getQuantityIndex() == $step->quantityIndex) {
                        $step->id = $productStep->getId();
                    }
                }
                $this->entityManager->persist($productStep);
            }

            $jsonResponse = json_encode(['success' => 'Dpp with inputs has been created successfully.', 'uuid' => $dpp->getId()]);
        }

        $this->entityManager->flush();

        foreach ($dppBody->steps as $step) {
            if ($step->verified) {
                continue;
            }

            if ($step->confirm == false || $step->confirm == null) {
                continue;
            }

            $productStep = $this->entityManager->find(ProductStep::class, $step->id);

            if ($productStep === null) {
                continue;
            }

            if ($dpp == null) {
                throw new InvalidArgumentException("DPP with ID {$dppBody->id} not found.");
            }

            $productStepDpp = $productStep->getDpp();
            if (isset($productStepDpp) && $productStepDpp->getId() !== $dpp->getId()) {
                continue;
            }

            try {
                if ($this->validatedProductStep($productStep)) {
                    throw new InvalidArgumentException("Product step {$step->id} is already validated. Load latest JSON.");
                }

                if ($this->validatedDpp($dpp)) {
                    throw new InvalidArgumentException("DPP {$dppBody->id} is already validated. Load latest JSON.");
                }

                foreach ($step->inputs as $input) {
                    $newInput = new ProductInput();
                    $newInput->setProductStep($productStep);
                    $newInput->setDpp($dpp);
                    $newInput->setName($input->name);
                    $newInput->setType($input->type);

                    switch ($input->type) {
                        case 'numerical':
                            if (empty($input->numericalValue)) {
                                throw new InvalidArgumentException('Numerical value is required.');
                            }
                            $newInput->setNumericalValue($input->numericalValue);
                            break;

                        case 'text':
                            if (empty($input->textValue)) {
                                throw new InvalidArgumentException('Text value is required.');
                            }
                            $newInput->setTextValue($input->textValue);
                            break;

                        case 'textarea':
                            if (empty($input->textAreaValue)) {
                                throw new InvalidArgumentException('Text area value is required.');
                            }
                            $newInput->setTextAreaValue($input->textAreaValue);
                            break;

                        case 'datetime':
                            if (empty($input->dateTimeFrom)) {
                                throw new InvalidArgumentException('dateTimeFrom is required.');
                            }
                            $newInput->setDateTimeFrom($input->dateTimeFrom);
                            break;

                        case 'datetimerange':
                            if (empty($input->dateTimeFrom) || empty($input->dateTimeTo)) {
                                throw new InvalidArgumentException('Both dateTimeFrom and dateTimeTo are required.');
                            }
                            $newInput->setDateTimeFrom($input->dateTimeFrom);
                            $newInput->setDateTimeTo($input->dateTimeTo);
                            break;

                        case 'coordinates':
                            if (empty($input->latitudeValue) || empty($input->longitudeValue)) {
                                throw new InvalidArgumentException('Both latitude and longitude are required.');
                            }
                            $newInput->setLatitudeValue($input->latitudeValue);
                            $newInput->setLongitudeValue($input->longitudeValue);
                            break;

                        case 'radio':
                            if (empty($input->radioValue)) {
                                throw new InvalidArgumentException('Radio value is required.');
                            }
                            $newInput->setRadioValue($input->radioValue);
                            break;

                        case 'checkbox':
                            if (empty($input->checkboxValue)) {
                                throw new InvalidArgumentException('Checkbox value is required.');
                            }
                            $newInput->setCheckboxValue($input->checkboxValue);
                            break;

                        default:
                            break;
                    }
                    $this->entityManager->persist($newInput);
                }

                $patch = new Patch();
                $productStep->setCreateQr(true);
                $productStep->setLocked(true);
                $this->updateProcessor->process($productStep, $patch);

                $timeStamp = $this->timestampService->createTimestamp($productStep);
                if ($timeStamp instanceof DateTime) {
                    $productStep->setTsaVerifiedAt($timeStamp);
                }

                $this->timestampService->verify($productStep);
                $this->timestampService->getTimestampInfo($productStep);

                if (empty($dpp->getQrImage())) {
                    $dpp->setCreateQr(true);
                    $this->dppCreateProcessor->process($dpp, $patch);
                }
            } catch (\InvalidArgumentException $e) {
                $jsonResponse = json_encode(['error' => $e->getMessage()]);
                $responseCode = Response::HTTP_BAD_REQUEST;
            } catch (\Exception $e) {
                $jsonResponse = json_encode(['error' => 'An unexpected error occurred: ' . $e->getMessage()]);
                $responseCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            }
        }

        $closeDpp = true;
        foreach ($dpp->getProductSteps() as $productStep) {
            if (empty($productStep->getQrImage())) {
                $closeDpp = false;
                break;
            }
        }

        if ($closeDpp) {
            $timeStamp = $this->timestampService->createTimestamp($dpp);
            if ($timeStamp instanceof DateTime) {
                $dpp->setTsaVerifiedAt($timeStamp);
            }

            $this->timestampService->verify($dpp);
            $this->timestampService->getTimestampInfo($dpp);

            $dpp->setLocked(true);
            $dpp->setOngoingDpp(false);
        }

        $this->entityManager->flush();

        if ($jsonResponse === false) {
            throw new \RuntimeException('Failed to encode JSON: ' . json_last_error_msg());
        }

        return new Response(
            $jsonResponse,
            $responseCode,
            ['Content-Type' => 'application/json']
        );
    }

    private function validatedProductStep(ProductStep $productStep): bool
    {
        return !empty($productStep->getQrImage());
    }

    private function validatedDpp(Dpp $dpp): bool
    {
        return !$dpp->isOngoingDpp();
    }
}

<?php

declare(strict_types=1);

namespace App\Controller\Dpp;

use App\Controller\Helper\ProcessedMaterialTraceHelper;
use App\Entity\Dpp\Dpp;
use App\Entity\Step\ProductStep;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GetProcessedMaterialsTraceController extends AbstractController
{
    use ProcessedMaterialTraceHelper;

    public function __invoke(Request $request, Dpp $dpp): Response
    {
        $pairs = [];
        foreach($dpp->getProductSteps() as $productStep) {
            foreach ($productStep->getProcessedMaterials() as $processedMaterial) {
                $pairs[$processedMaterial->getId()->toRfc4122()] = [];
                if ($dpp->getNode()) {
                    $pairs[$processedMaterial->getId()->toRfc4122()] = array_merge(
                        $this->getParentPair($dpp->getNode(), $processedMaterial),
                        [[$productStep, $processedMaterial]],
                    );
                } else {
                    $pairs[$processedMaterial->getId()->toRfc4122()][] = [$productStep, $processedMaterial];
                }
            }
        }

        $outputData = [];
        foreach ($pairs as $processedMaterialId => $processedMaterialPairs) {
            $outputData[$processedMaterialId] = [];
            foreach ($processedMaterialPairs as $pair) {
                if (count($pair) === 2) {
                    /** @var ProductStep $productStep */
                    $productStep = $pair[0];
                    /** @var ProductStep $processedMaterial */
                    $processedMaterial = $pair[1];

                    $productStepPlace = $productStep->getCompanySite() ?: $productStep->getCompany();
                    if ($productStepPlace === null) {
                        $productStepPlace = $dpp->getCompanySite() ?: $dpp->getCompany();
                    }
                    $processedMaterialPlace = $processedMaterial->getCompanySite() ?: $processedMaterial->getCompany();
                    if ($processedMaterialPlace === null) {
                        $processedMaterialPlace = $dpp->getCompanySite() ?: $dpp->getCompany();
                    }
                    if ($productStepPlace !== null && $processedMaterialPlace !== null) {
                        $outputData[$processedMaterialId][] = [
                            [
                                'productStepId' => $processedMaterial->getId()->toRfc4122(),
                                'companyName' => $processedMaterialPlace->getName(),
                                'coords' => [
                                    'lat' => $processedMaterialPlace->getLatitude(),
                                    'lng' => $processedMaterialPlace->getLongitude(),
                                ],
                            ],
                            [
                                'productStepId' => $productStep->getId()->toRfc4122(),
                                'companyName' => $productStepPlace->getName(),
                                'coords' => [
                                    'lat' => $productStepPlace->getLatitude(),
                                    'lng' => $productStepPlace->getLongitude(),
                                ],
                            ],
                        ];
                    }
                }
            }
        }

        $jsonResponse = json_encode($outputData);
        $responseCode = Response::HTTP_OK;

        if ($jsonResponse === false) {
            throw new RuntimeException('Failed to encode JSON: ' . json_last_error_msg());
        }

        return new Response(
            $jsonResponse,
            $responseCode,
            ['Content-Type' => 'application/json']
        );
    }
}

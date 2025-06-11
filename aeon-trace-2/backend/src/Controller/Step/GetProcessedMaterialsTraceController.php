<?php

declare(strict_types=1);

namespace App\Controller\Step;

use App\Controller\Helper\ProcessedMaterialTraceHelper;
use App\Entity\Step\ProductStep;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GetProcessedMaterialsTraceController extends AbstractController
{
    use ProcessedMaterialTraceHelper;

    public function __invoke(Request $request, ProductStep $productStep): Response
    {
        $pairs = [];
        foreach ($productStep->getProcessedMaterials() as $processedMaterial) {
            $pairs[$processedMaterial->getId()->toRfc4122()] = [];
            if ($productStep->getNode()) {
                $pairs[$processedMaterial->getId()->toRfc4122()] = array_merge(
                    $this->getParentPair($productStep->getNode(), $processedMaterial),
                    [[$productStep, $processedMaterial]],
                );
            } else {
                $pairs[$processedMaterial->getId()->toRfc4122()][] = [$productStep, $processedMaterial];
            }
        }

        $outputData = [];
        foreach ($pairs as $processedMaterialId => $processedMaterialPairs) {
            $outputData[$processedMaterialId] = [];
            foreach ($processedMaterialPairs as $pair) {
                /** @var ProductStep $productStep */
                $productStep = $pair[0];
                /** @var ProductStep $processedMaterial */
                $processedMaterial = $pair[1];

                $productStepPlace = $productStep->getCompanySite() ?: $productStep->getCompany();
                $processedMaterialPlace = $processedMaterial->getCompanySite() ?: $processedMaterial->getCompany();
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

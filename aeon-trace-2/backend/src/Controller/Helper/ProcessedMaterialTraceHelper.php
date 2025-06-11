<?php

declare(strict_types=1);

namespace App\Controller\Helper;

use App\Entity\Step\ProductStep;
use App\Entity\SupplyChain\Node;

trait ProcessedMaterialTraceHelper
{
    /**
     * @return array<int, array{0: ProductStep, 1: ProductStep}>
     */
    protected function getParentPair(Node $node, ProductStep $searchedProcessedMaterial): array
    {
        foreach ($node->getParents() as $parentNode) {
            /** @var array<ProductStep> $productSteps */
            $productSteps = $parentNode->getProductSteps()->toArray();
            foreach ($parentNode->getDpps() as $dpp) {
                $productSteps = array_merge($productSteps, $dpp->getProductSteps()->toArray());
            }

            foreach ($productSteps as $productStep) {
                foreach ($productStep->getProcessedMaterials() as $processedMaterial) {
                    if (
                        $searchedProcessedMaterial->getId()->toRfc4122() === $productStep->getId()->toRfc4122() ||
                        $searchedProcessedMaterial->getId()->toRfc4122() === $processedMaterial->getId()->toRfc4122()
                    ) {
                        $parentPairs = $this->getParentPair($parentNode, $searchedProcessedMaterial);
                        $thisPair = [[$productStep, $processedMaterial]];

                        return array_merge($parentPairs, $thisPair);
                    }
                }
            }
        }

        return [];
    }
}

<?php

declare(strict_types=1);

namespace App\DataProviders;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\SupplyChain\SupplyChainTemplate;
use App\Repository\SupplyChain\SupplyChainTemplateRepository;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @template T of SupplyChainTemplate
 * @implements ProviderInterface<JsonResponse>
 */
readonly class VerifySupplyChainDataProvider implements ProviderInterface
{
    /**
     * @var SupplyChainTemplateRepository<T>
     */
    private SupplyChainTemplateRepository $repository;

    /**
     * @param SupplyChainTemplateRepository<T> $repository
     */
    public function __construct(SupplyChainTemplateRepository $repository)
    {
        $this->repository = $repository;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $supplyChainTemplateId = $uriVariables['id'] ?? null;

        if (!$supplyChainTemplateId) {
            throw new BadRequestException('Missing SupplyChainTemplate ID.');
        }

        $supplyChainTemplate = $this->repository->find($supplyChainTemplateId);

        if (!$supplyChainTemplate) {
            return new JsonResponse([
                'verified' => false,
                'info' => [],
            ], Response::HTTP_NOT_FOUND);
        }

        $usedSteps = [];
        foreach ($supplyChainTemplate->getNodes() as $node) {
            foreach ($node->getSteps() as $step) {
                $stepId = $step->getId()->jsonSerialize();
                $usedSteps[$stepId] = true;
            }
        }

        $productsSteps = [];
        foreach ($supplyChainTemplate->getProductTemplates() as $productTemplate) {
            if (!$productTemplate->getStepsTemplate()) {
                continue;
            }

            $unusedSteps = [];
            foreach ($productTemplate->getStepsTemplate()->getSteps() as $step) {
                $stepId = $step->getId()->jsonSerialize();
                if (!isset($usedSteps[$stepId])) {
                    $unusedSteps[] = [
                        'id' => $stepId,
                        'name' => $step->getName(),
                    ];
                }
            }

            if (!empty($unusedSteps)) {
                $productsSteps[] = [
                    'id' => $productTemplate->getId()->jsonSerialize(),
                    'name' => $productTemplate->getName(),
                    'steps' => $unusedSteps,
                ];
            }
        }

        return new JsonResponse([
            'verified' => empty($productsSteps),
            'info' => $productsSteps,
        ], Response::HTTP_OK);
    }
}

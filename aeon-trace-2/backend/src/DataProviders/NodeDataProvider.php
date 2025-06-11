<?php

declare(strict_types=1);

namespace App\DataProviders;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Dpp\Dpp;
use App\Entity\Logistics\Logistics;
use App\Entity\Step\ProductStep;
use App\Entity\SupplyChain\Node;
use App\Repository\SupplyChain\NodeRepository;

/**
 * @template T of Node
 * @implements ProviderInterface<Node>
 */
readonly class NodeDataProvider implements ProviderInterface
{
    /**
     * @var NodeRepository<T>
     */
    private NodeRepository $repository;

    /**
     * @param NodeRepository<T> $repository
     */
    public function __construct(NodeRepository $repository)
    {
        $this->repository = $repository;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $nodeId = $uriVariables['id'] ?? null;

        if (!$nodeId) {
            throw new \InvalidArgumentException('Node ID is required.');
        }

        $node = $this->repository->find($nodeId);

        if (!$node) {
            return null;
        }

        $this->calculateAndSetDppCounts($node);
        $this->calculateAndSetLogisticsCounts($node);

        return $node;
    }

    /**
     * Calculate and set the DPP for a given Node.
     */
    private function calculateAndSetDppCounts(Node $node): void
    {
        $nodeDpps = $node->getDpps();

        $nodeDppNotAssigned = $this->filterDppsByState($nodeDpps, [Dpp::STATE_NOT_ASSIGNED]);
        $nodeDppInUse = $this->filterDppsByState($nodeDpps, [Dpp::STATE_DPP_IN_USE]);
        $nodeDppsLogistics = $this->filterDppsByState($nodeDpps, [Dpp::STATE_LOGISTICS]);
        $nodeDppsExport = $this->filterDppsByState($nodeDpps, [Dpp::STATE_EXPORT_DPP]);
        $nodeDppsOngoing = $this->filterDppsByOngoing($nodeDpps, true);
        $nodeDppsEmpty = $this->filterDppsByEmpty($nodeDpps, true);

        $countDppData = [
            'notAssignedDpp' => [
                'count' => 0,
                'data' => $nodeDppNotAssigned,
            ],
            'dppInUse' => [
                'count' => 0,
                'data' => $nodeDppInUse,
            ],
            'logistics' => [
                'count' => 0,
                'data' => $nodeDppsLogistics,
            ],
            'exportDpp' => [
                'count' => 0,
                'data' => $nodeDppsExport,
            ],
            'ongoingDpp' => [
                'count' => 0,
                'data' => $nodeDppsOngoing,
            ],
            'emptyDpp' => [
                'count' => 0,
                'data' => $nodeDppsEmpty,
            ],
        ];

        $this->addDppCounts($nodeDpps, $countDppData);
        foreach ($nodeDpps as $nodeDpp) {
            $dppProductSteps = $nodeDpp->getProductSteps();
            $this->addDppCounts($dppProductSteps, $countDppData);
        }

        $node->setCountDppData($countDppData);
    }

    /**
     * Calculate and set the logistic numbers for a given Node.
     */
    private function calculateAndSetLogisticsCounts(Node $node): void
    {

        $filteredAssignedToDpp = $this->filterLogisticsByState($node->getToNodeLogistics(), [Logistics::STATE_ASSIGNED_TO_DPP]);
        $filteredExportLogistics = $this->filterLogisticsByState($node->getToNodeLogistics(), [Logistics::STATE_EXPORT_LOGISTICS]);
        $filteredInUseLogistics = $this->filterLogisticsByState($node->getToNodeLogistics(), [Logistics::STATE_IN_USE_LOGISTICS]);

        $node->setCountLogistics([
            'assignedToDppData' => $filteredAssignedToDpp,
            'exportLogisticsData' => $filteredExportLogistics,
            'inUseLogisticsData' => $filteredInUseLogistics,
            'assignedToDpp' => count($filteredAssignedToDpp),
            'exportLogistics' => count($filteredExportLogistics),
            'inUseLogistics' => count($filteredInUseLogistics),
        ]);
    }

    /**
     * Filters DPP by status.
     *
     * @param iterable<Dpp>|iterable<ProductStep> $dpps
     * @param array<string> $states
     * @return array<Dpp>|array<ProductStep>
     */
    private function filterDppsByState(iterable $dpps, array $states): array
    {
        return array_filter(iterator_to_array($dpps), fn (Dpp|ProductStep $dpp) => in_array($dpp->getState(), $states, true));
    }

    /**
     * Filtruje DPP podľa prebiehajúceho stavu.
     *
     * @param iterable<Dpp>|iterable<ProductStep> $dpps
     * @return array<Dpp>|array<ProductStep>
     */
    private function filterDppsByOngoing(iterable $dpps, bool $ongoing): array
    {
        return array_filter(iterator_to_array($dpps), fn (Dpp|ProductStep $dpp) => $dpp->isOngoingDpp() === $ongoing);
    }

    /**
     * Filter DPPs by the ongoing property.
     *
     * @param iterable<Dpp>|iterable<ProductStep> $dpps
     * @return array<Dpp>|array<ProductStep>
     */
    private function filterDppsByEmpty(iterable $dpps, bool $empty): array
    {
        return array_filter(iterator_to_array($dpps), function (Dpp|ProductStep $dpp) use ($empty) {
            return $dpp->isCreateEmptyDpp() === $empty;
        });
    }

    /**
     * Filters logistics by status.
     *
     * @param iterable<Logistics>|iterable<Logistics> $logistics
     * @param array<string> $states
     * @return array<Logistics>|array<Logistics>
     */
    private function filterLogisticsByState(iterable $logistics, array $states): array
    {
        $logisticsArray = iterator_to_array($logistics);
        $filteredLogistics = [];
        $seenParentIds = [];

        foreach ($logisticsArray as $logistic) {
            $parent = $logistic->getLogisticsParent();

            if ($parent && in_array($parent->getState(), $states, true)) {
                $parentId = $parent->getId();

                if (!in_array($parentId, $seenParentIds, true)) {
                    $filteredLogistics[] = $logistic;
                    $seenParentIds[] = $parentId;
                }
            }
        }

        return $filteredLogistics;
    }

    /**
     * @param iterable<Dpp>|iterable<ProductStep> $collection
     * @param array<string, array{count: int, data: array<int, Dpp>}> $counts
     */
    private function addDppCounts(iterable $collection, array &$counts): void
    {
        $dppsExport = $this->filterDppsByState($collection, [Dpp::STATE_EXPORT_DPP]);
        $dppsNotAssigned = $this->filterDppsByState($collection, [Dpp::STATE_NOT_ASSIGNED]);
        $dppsInUse = $this->filterDppsByState($collection, [Dpp::STATE_DPP_IN_USE]);
        $dppsLogistics = $this->filterDppsByState($collection, [Dpp::STATE_LOGISTICS]);
        $dppsOngoing = $this->filterDppsByOngoing($collection, true);
        $dppsEmpty = $this->filterDppsByEmpty($collection, true);

        $dppsNotAssigned = array_filter($dppsNotAssigned, fn ($dpp) => !$dpp->isOngoingDpp());
        $dppsLogistics = array_filter($dppsLogistics, fn ($dpp) => !$dpp->isOngoingDpp());
        $dppsExport = array_filter($dppsExport, fn ($dpp) => !$dpp->isOngoingDpp());

        $dppsNotAssigned = array_filter($dppsNotAssigned, fn ($dpp) => !$dpp->isCreateEmptyDpp());
        $dppsLogistics = array_filter($dppsLogistics, fn ($dpp) => !$dpp->isCreateEmptyDpp());
        $dppsExport = array_filter($dppsExport, fn ($dpp) => !$dpp->isCreateEmptyDpp());

        $counts['notAssignedDpp']['count'] += count($dppsNotAssigned);
        $counts['dppInUse']['count'] += count($dppsInUse);
        $counts['logistics']['count'] += count($dppsLogistics);
        $counts['exportDpp']['count'] += count($dppsExport);
        $counts['ongoingDpp']['count'] += count($dppsOngoing);
        $counts['emptyDpp']['count'] += count($dppsEmpty);
    }
}

<?php

declare(strict_types=1);

namespace App\DataProviders;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Dpp\Dpp;
use App\Entity\Logistics\Logistics;
use App\Entity\Step\ProductStep;
use App\Entity\SupplyChain\SupplyChainTemplate;
use App\Repository\SupplyChain\SupplyChainTemplateRepository;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

/**
 * @template T of SupplyChainTemplate
 * @implements ProviderInterface<SupplyChainTemplate>
 */
readonly class SupplyChainDataProvider implements ProviderInterface
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
            throw new BadRequestException();
        }

        $supplyChainTemplate = $this->repository->find($supplyChainTemplateId);

        if (!$supplyChainTemplate) {
            return [];
        }

        foreach ($supplyChainTemplate->getNodes() as $node) {
            $nodeDpps = $node->getDpps();
            $nodeProductSteps = $node->getProductSteps();

            $filteredLogisticsExport = $this->filterLogisticsByState($node->getFromNodeLogistics(), [Logistics::STATE_EXPORT_LOGISTICS]);
            $filteredInUseLogistics = $this->filterLogisticsByState($node->getFromNodeLogistics(), [Logistics::STATE_IN_USE_LOGISTICS]);
            $filteredAssignedToDpp = $this->filterLogisticsByState($node->getFromNodeLogistics(), [Logistics::STATE_ASSIGNED_TO_DPP]);

            $countDpps = [
                'notAssignedDpp' => 0,
                'dppInUse' => 0,
                'logistics' => 0,
                'exportDpp' => 0,
                'ongoingDpp' => 0,
                'emptyDpp' => 0,
            ];

            $this->addDppCounts($nodeDpps, $countDpps);
            $this->addDppCounts($nodeProductSteps, $countDpps);

            $node->setCountDpp($countDpps);

            $node->setCountLogistics([
                'assignedToDppData' => $filteredAssignedToDpp,
                'exportLogisticsData' => $filteredLogisticsExport,
                'inUseLogisticsData' => $filteredInUseLogistics,
                'assignedToDpp' => count($filteredAssignedToDpp),
                'exportLogistics' => count($filteredLogisticsExport),
                'inUseLogistics' => count($filteredInUseLogistics),
            ]);

            $countDpps = $node->getCountDpp();

            if((array_key_exists('notAssignedDpp', $countDpps) && $countDpps['notAssignedDpp'] > 0) || (array_key_exists('dppLogistics', $countDpps) && $countDpps['dppLogistics'] > 0)) {
                $node->setExistAssignedDpp(true);
            }

            if(!$node->getParents()->isEmpty()) {
                foreach ($node->getParents() as $nodeParent) {
                    $countLogistics = $nodeParent->getCountLogistics();

                    if (array_key_exists('assignedToDpp', $countLogistics) && $countLogistics['assignedToDpp'] > 0) {
                        $node->setExistLogisticsAssignedToDpp(true);
                        break;
                    }
                }
            }
        }

        return $supplyChainTemplate;
    }

    /**
     * Filter DPPs by state.
     *
     * @param iterable<Dpp>|iterable<ProductStep> $dpps
     * @param array<string> $states
     * @return array<Dpp>|array<ProductStep>
     */
    private function filterDppsByState(iterable $dpps, array $states): array
    {
        return array_filter(iterator_to_array($dpps), function (Dpp|ProductStep $dpp) use ($states) {
            return in_array($dpp->getState(), $states);
        });
    }

    /**
     * Filter Logistics by state.
     *
     * @param iterable<Logistics> $logistics
     * @param array<string> $states
     * @return array<Logistics>
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
     * Filter DPPs by the ongoing property.
     *
     * @param iterable<Dpp>|iterable<ProductStep> $dpps
     * @return array<Dpp>|array<ProductStep>
     */
    private function filterDppsByOngoing(iterable $dpps, bool $ongoing): array
    {
        return array_filter(iterator_to_array($dpps), function (Dpp|ProductStep $dpp) use ($ongoing) {
            return $dpp->isOngoingDpp() === $ongoing;
        });
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
     * @param iterable<Dpp>|iterable<ProductStep> $collection
     * @param array<string,int> $counts
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

        $counts['notAssignedDpp'] += count($dppsNotAssigned);
        $counts['dppInUse'] += count($dppsInUse);
        $counts['logistics'] += count($dppsLogistics);
        $counts['exportDpp'] += count($dppsExport);
        $counts['ongoingDpp'] += count($dppsOngoing);
        $counts['emptyDpp'] += count($dppsEmpty);
    }
}

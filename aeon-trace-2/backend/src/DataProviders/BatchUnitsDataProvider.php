<?php

declare(strict_types=1);

namespace App\DataProviders;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Collection\BatchUnit;
use App\Enums\Steps\BatchTypeUnits;

/**
 * @template T of BatchUnit
 * @implements ProviderInterface<BatchUnit>
 */
readonly class BatchUnitsDataProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $batch = new BatchUnit();

        // Get grouped units from the enum
        $batchType = array_map(
            fn ($type, $units) => [
                'typeUnit' => $type,
                'units' => $units,
            ],
            array_keys(BatchTypeUnits::groupedByType()),
            BatchTypeUnits::groupedByType()
        );

        $batchType[] = [
            'typeUnit' => 'batchQuantity',
            'units' => [],
        ];

        $batch->setBatchType($batchType);

        return $batch;
    }
}

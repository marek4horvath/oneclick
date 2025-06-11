<?php

declare(strict_types=1);

namespace App\DataProviders;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Collection\Batch;
use App\Enums\Steps\BatchType;

/**
 * @template T of Batch
 * @implements ProviderInterface<Batch>
 */
readonly class BatchDataProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $batch = new Batch();

        $batchType = array_map(fn (BatchType $type) => $type->value, BatchType::cases());

        $batch->setBatchType($batchType);

        return $batch;
    }
}

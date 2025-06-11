<?php

declare(strict_types=1);

namespace App\Entity\Collection;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\DataProviders\BatchDataProvider;

#[ApiResource(
    operations: [
        new GetCollection(
            provider: BatchDataProvider::class
        ),
    ]
)]
class Batch
{
    /** @var array<string> */
    private array $batchType = [];

    /**
     * @return array<string>
     */
    public function getBatchType(): array
    {
        return $this->batchType;
    }

    /**
     * @param array<string> $batchType
     */
    public function setBatchType(array $batchType): void
    {
        $this->batchType = $batchType;
    }
}

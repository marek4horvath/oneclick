<?php

declare(strict_types=1);

namespace App\Entity\Collection;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\DataProviders\BatchUnitsDataProvider;

#[ApiResource(
    operations: [
        new GetCollection(
            provider: BatchUnitsDataProvider::class
        ),
    ]
)]
class BatchUnit
{
    /**
     * @var array<int, array{
     *     typeUnit: string,
     *     units: array<int, array{
     *         name: string,
     *         value: string,
     *     }>
     * }>
     */
    private array $batchType = [];

    /**
     * @return array<int, array{
     *     typeUnit: string,
     *     units: array<int, array{
     *         name: string,
     *         value: string,
     *     }>
     * }>
     */
    public function getBatchType(): array
    {
        return $this->batchType;
    }

    /**
     * @param array<int, array{
     *     typeUnit: string,
     *     units: array<int, array{
     *         name: string,
     *         value: string,
     *     }>
     * }> $batchType
     */
    public function setBatchType(array $batchType): self
    {
        $this->batchType = $batchType;

        return $this;
    }
}

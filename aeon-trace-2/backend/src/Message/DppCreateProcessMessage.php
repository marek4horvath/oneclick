<?php

declare(strict_types=1);

namespace App\Message;

use App\Entity\Dpp\Dpp;
use App\Entity\Step\ProductStep;
use Symfony\Component\Uid\Uuid;

class DppCreateProcessMessage
{
    private Uuid $uuid;
    private string $entityClass;

    public function __construct(Uuid $uuid, string $entityClass)
    {
        $this->uuid = $uuid;
        $this->entityClass = $entityClass;
    }

    public function getUuid(): Uuid
    {
        return $this->uuid;
    }

    /**
     * @return class-string<Dpp|ProductStep> $entityClass
     */
    public function getEntityClass(): string
    {
        return $this->entityClass === Dpp::class ? Dpp::class : ProductStep::class;
    }
}

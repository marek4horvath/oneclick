<?php

declare(strict_types=1);

namespace App\Entity\Collection;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\DataProviders\RoleDataProvider;

#[ApiResource(
    operations: [
        new GetCollection(
            provider: RoleDataProvider::class,
        ),
    ]
)]
class Role
{
    /**
     * @var array<string>
     */
    private array $roles;

    /**
     * @return array<string>
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param array<string> $roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }
}

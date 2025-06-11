<?php

declare(strict_types=1);

namespace App\DataProviders;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Collection\Role;
use App\Enums\Security\UserRole;

/**
 * @template T of Role
 * @implements ProviderInterface<Role>
 */
class RoleDataProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $category = new Role();

        /** @var array<string> $categories */
        $categories = [UserRole::cases()];

        $category->setRoles($categories);

        return [$category];
    }
}

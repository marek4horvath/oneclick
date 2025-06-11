<?php

declare(strict_types=1);

namespace App\Api\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Dpp\DppListingView;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\PropertyInfo\Type;

class ListingViewUserOrderFilter extends AbstractFilter
{
    /**
     * @param mixed $value
     */
    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        if ($property !== 'order' || !is_array($value) || !isset($value['userData']) || $resourceClass !== DppListingView::class) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $sortDirection = strtolower($value['userData']) === 'desc' ? 'DESC' : 'ASC';

        $queryBuilder->orderBy("{$rootAlias}.userLastName", $sortDirection)
            ->addOrderBy("{$rootAlias}.userFirstName", $sortDirection);
    }

    public function getDescription(string $resourceClass): array
    {
        if (!$this->properties) {
            return [];
        }

        return array_map(function ($strategy) {
            return [
                'property' => 'userData',
                'type' => Type::BUILTIN_TYPE_STRING,
                'required' => false,
                'description' => 'Sort by user (last name, first name)',
                'schema' => [
                    'type' => 'string',
                    'enum' => ['asc', 'desc'],
                ],
            ];
        }, $this->properties);
    }
}

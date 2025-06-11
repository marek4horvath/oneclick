<?php declare(strict_types=1);

namespace App\Api\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;

final class DateNullFilter extends AbstractFilter
{
    protected function filterProperty(
        string $property,
        mixed $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string|Operation $operation = null,
        array $context = []
    ): void {
        if ($property !== 'deletedAt') {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];

        if ($value === 'null') {
            $queryBuilder->andWhere(sprintf('%s.deletedAt IS NULL', $alias));
        } elseif ($value === 'notNull') {
            $queryBuilder->andWhere(sprintf('%s.deletedAt IS NOT NULL', $alias));
        }
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            'deletedAt' => [
                'property' => 'deletedAt',
                'type' => 'string',
                'required' => false,
                'description' => 'Filter entities by null or not null deletedAt value. Possible values: (null, notNull)',
            ],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Api\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\Uid\Uuid;

final class ProductInputImageByInputIdFilter extends AbstractFilter
{
    public function getDescription(string $resourceClass): array
    {
        return [
            'input.id' => [
                'property' => 'input.id',
                'type' => Type::BUILTIN_TYPE_STRING,
                'required' => false,
                'description' => 'Filter ProductInputImage by Input ID',
            ],
        ];
    }

    protected function filterProperty(
        string $property,
        mixed $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        Operation $operation = null,
        array $context = []
    ): void {
        if ($property !== 'input.id' || $value === null) {
            return;
        }

        if (!is_string($value)) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $inputAlias = $queryNameGenerator->generateJoinAlias('input');

        $queryBuilder->leftJoin("{$alias}.input", $inputAlias, Join::WITH);

        $binaryUuid = Uuid::fromString($value)->toBinary();
        $paramName = $queryNameGenerator->generateParameterName('input_id');

        $queryBuilder
            ->andWhere("{$inputAlias}.id = :{$paramName}")
            ->setParameter($paramName, $binaryUuid);
    }
}

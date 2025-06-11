<?php declare(strict_types=1);

namespace App\Api\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;

class UserFullNameOrderFilter extends AbstractFilter
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
        if ($property !== 'order' || !is_array($value) || !isset($value['name']) || $resourceClass !== User::class) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $fullNameAlias = $queryNameGenerator->generateParameterName('fullName');

        $queryBuilder->addSelect("CONCAT({$rootAlias}.firstName, ' ', {$rootAlias}.lastName) AS HIDDEN {$fullNameAlias}");

        $sortDirection = strtolower($value['name']) === 'desc' ? 'DESC' : 'ASC';

        $queryBuilder->orderBy($fullNameAlias, $sortDirection);
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            'order[name]' => [
                'property' => 'name',
                'type' => 'string',
                'required' => false,
                'description' => 'Sort users by full name (first name + last name).',
                'schema' => ['type' => 'string', 'enum' => ['asc', 'desc']],
                'openapi' => ['allowReserved' => false, 'allowEmptyValue' => true, 'explode' => false],
            ],
        ];
    }
}

<?php declare(strict_types=1);

namespace App\Api\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Dpp\Dpp;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\PropertyInfo\Type;

class DppUserOrderFilter extends AbstractFilter
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
        if ($property !== 'order' || !is_array($value) || !isset($value['userData']) || $resourceClass !== Dpp::class) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $userAlias = $queryNameGenerator->generateJoinAlias('user');

        $queryBuilder->leftJoin("{$rootAlias}.user", $userAlias)
            ->addSelect($userAlias);

        $sortDirection = strtolower($value['userData']) === 'desc' ? 'DESC' : 'ASC';

        $queryBuilder->orderBy("{$userAlias}.lastName", $sortDirection)
            ->addOrderBy("{$userAlias}.firstName", $sortDirection);
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
                'description' => 'Sort by the user who created the DPP (last name, first name).',
                'schema' => [
                    'type' => 'string',
                    'enum' => ['asc', 'desc'],
                ],
                'openapi' => [
                    'allowReserved' => false,
                    'allowEmptyValue' => true,
                    'explode' => false,
                ],
            ];
        }, $this->properties);
    }
}

<?php declare(strict_types=1);

namespace App\Api\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Company\Company;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\PropertyInfo\Type;

class CompanyUsersLengthOrderFilter extends AbstractFilter
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
        if ($property !== 'order' || !is_array($value) || !isset($value['totalUsers']) || $resourceClass !== Company::class) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $userAlias = $queryNameGenerator->generateJoinAlias('users');

        $queryBuilder->leftJoin("{$rootAlias}.users", $userAlias)
            ->addSelect($userAlias);

        $subQuery = $queryBuilder->getEntityManager()->createQueryBuilder()
            ->select('COUNT(u.id)')
            ->from(User::class, 'u')
            ->where("u.company = {$rootAlias}")
            ->getDQL();

        $sortDirection = strtolower($value['totalUsers']) === 'desc' ? 'DESC' : 'ASC';
        $queryBuilder->addSelect("({$subQuery}) as HIDDEN usersCount");
        $queryBuilder->orderBy('usersCount', $sortDirection);
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            'order[totalUsers]' => [
                'property' => 'totalUsers',
                'type' => Type::BUILTIN_TYPE_STRING,
                'required' => false,
                'description' => 'Sort companies by the number of users.',
                'schema' => ['type' => 'string', 'enum' => ['asc', 'desc']],
                'openapi' => ['allowReserved' => false, 'allowEmptyValue' => true, 'explode' => false],
            ],
        ];
    }
}

<?php declare(strict_types=1);

namespace App\Api\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Company\Company;
use App\Entity\Dpp\Dpp;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\PropertyInfo\Type;

class CompanyDppsLengthOrderFilter extends AbstractFilter
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
        if ($property !== 'order' || !is_array($value) || !isset($value['totalDpps']) || $resourceClass !== Company::class) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $dppAlias = $queryNameGenerator->generateJoinAlias('dpps');

        $queryBuilder->leftJoin("{$rootAlias}.dpps", $dppAlias)
            ->addSelect($dppAlias);

        $subQuery = $queryBuilder->getEntityManager()->createQueryBuilder()
            ->select('COUNT(d.id)')
            ->from(Dpp::class, 'd')
            ->where("d.company = {$rootAlias}")
            ->getDQL();

        $sortDirection = strtolower($value['totalDpps']) === 'desc' ? 'DESC' : 'ASC';
        $queryBuilder->addSelect("({$subQuery}) as HIDDEN dppsCount");
        $queryBuilder->orderBy('dppsCount', $sortDirection);
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            'order[totalDpps]' => [
                'property' => 'totalDpps',
                'type' => Type::BUILTIN_TYPE_STRING,
                'required' => false,
                'description' => 'Sort companies by the number of DPPs.',
                'schema' => ['type' => 'string', 'enum' => ['asc', 'desc']],
                'openapi' => ['allowReserved' => false, 'allowEmptyValue' => true, 'explode' => false],
            ],
        ];
    }
}

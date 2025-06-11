<?php declare(strict_types=1);

namespace App\Api\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Company\Company;
use App\Entity\Product\ProductTemplate;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\PropertyInfo\Type;

class CompanyProductTemplatesLengthOrderFilter extends AbstractFilter
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
        if ($property !== 'order' || !is_array($value) || !isset($value['totalProductTemplate']) || $resourceClass !== Company::class) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $templateAlias = $queryNameGenerator->generateJoinAlias('productTemplates');

        $queryBuilder->leftJoin("{$rootAlias}.productTemplates", $templateAlias)
            ->addSelect($templateAlias);

        $subQuery = $queryBuilder->getEntityManager()->createQueryBuilder()
            ->select('COUNT(pt.id)')
            ->from(ProductTemplate::class, 'pt')
            ->where("pt MEMBER OF {$rootAlias}.productTemplates")
            ->getDQL();

        $sortDirection = strtolower($value['totalProductTemplate']) === 'desc' ? 'DESC' : 'ASC';
        $queryBuilder->addSelect("({$subQuery}) as HIDDEN productTemplatesCount");
        $queryBuilder->orderBy('productTemplatesCount', $sortDirection);
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            'order[totalProductTemplate]' => [
                'property' => 'totalProductTemplate',
                'type' => Type::BUILTIN_TYPE_STRING,
                'required' => false,
                'description' => 'Sort companies by the number of product templates.',
                'schema' => ['type' => 'string', 'enum' => ['asc', 'desc']],
                'openapi' => ['allowReserved' => false, 'allowEmptyValue' => true, 'explode' => false],
            ],
        ];
    }
}

<?php declare(strict_types=1);

namespace App\Api\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Product\Input;
use App\Entity\Product\InputCategory;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\PropertyInfo\Type;

class InputCategoryInputsLengthOrderFilter extends AbstractFilter
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
        if ($property !== 'order' || !is_array($value) || !isset($value['inputs']) || $resourceClass !== InputCategory::class) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $inputAlias = $queryNameGenerator->generateJoinAlias('inputs');

        $queryBuilder->leftJoin("{$rootAlias}.inputs", $inputAlias)
            ->addSelect($inputAlias);

        $subQuery = $queryBuilder->getEntityManager()->createQueryBuilder()
            ->select('COUNT(i.id)')
            ->from(Input::class, 'i')
            ->leftJoin('i.inputCategories', 'ic')
            ->where("ic = {$rootAlias}")
            ->getDQL();

        $sortDirection = strtolower($value['inputs']) === 'desc' ? 'DESC' : 'ASC';
        $queryBuilder->addSelect("({$subQuery}) as HIDDEN inputsCount");
        $queryBuilder->orderBy('inputsCount', $sortDirection);
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            'order[inputs]' => [
                'property' => 'inputs',
                'type' => Type::BUILTIN_TYPE_STRING,
                'required' => false,
                'description' => 'Sort input categories by the number of inputs.',
                'schema' => ['type' => 'string', 'enum' => ['asc', 'desc']],
                'openapi' => ['allowReserved' => false, 'allowEmptyValue' => true, 'explode' => false],
            ],
        ];
    }
}

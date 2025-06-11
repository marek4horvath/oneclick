<?php

declare(strict_types=1);

namespace App\Api\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Logistics\LogisticsTemplate;
use App\Entity\Product\Input;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\PropertyInfo\Type;

class LogisticsTemplateInputsLengthOrderFilter extends AbstractFilter
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
        if ($property !== 'order' || !is_array($value) || !isset($value['numberOfInputs']) || $resourceClass !== LogisticsTemplate::class) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $inputAlias = $queryNameGenerator->generateJoinAlias('inputs');

        $queryBuilder->leftJoin("{$rootAlias}.inputs", $inputAlias)
            ->addSelect($inputAlias);

        $subQuery = $queryBuilder->getEntityManager()->createQueryBuilder()
            ->select('COUNT(i.id)')
            ->from(Input::class, 'i')
            ->where("i.logisticsTemplate = {$rootAlias}")
            ->getDQL();

        $sortDirection = strtolower($value['numberOfInputs']) === 'desc' ? 'DESC' : 'ASC';
        $queryBuilder->addSelect("({$subQuery}) as HIDDEN inputsCount");
        $queryBuilder->orderBy('inputsCount', $sortDirection);
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            'order[numberOfInputs]' => [
                'property' => 'numberOfInputs',
                'type' => Type::BUILTIN_TYPE_STRING,
                'required' => false,
                'description' => 'Sort logistics templates by the number of inputs.',
                'schema' => ['type' => 'string', 'enum' => ['asc', 'desc']],
                'openapi' => ['allowReserved' => false, 'allowEmptyValue' => true, 'explode' => false],
            ],
        ];
    }
}

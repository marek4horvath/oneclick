<?php declare(strict_types=1);

namespace App\Api\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Logistics\Logistics;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\PropertyInfo\Type;

class LogisticsProductInputsWithLogisticsStepsProductInputsOrderFilter extends AbstractFilter
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
        if ($property !== 'order' || !is_array($value) || !isset($value['numberOfInputs']) || $resourceClass !== Logistics::class) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        // Join product inputs.
        $inputAlias = $queryNameGenerator->generateJoinAlias('productInputs');
        $queryBuilder->leftJoin("{$rootAlias}.productInputs", $inputAlias);

        // Join logisticsSteps and then productInputs.
        $stepsAlias = $queryNameGenerator->generateJoinAlias('logisticsSteps');
        $queryBuilder->leftJoin("{$rootAlias}.logisticsSteps", $stepsAlias);
        $stepInputAlias = $queryNameGenerator->generateJoinAlias('logisticsStepsProductInputs');
        $queryBuilder->leftJoin("{$stepsAlias}.productInputs", $stepInputAlias);

        $sortDirection = strtolower($value['numberOfInputs']) === 'desc' ? 'DESC' : 'ASC';

        // Add a select that sums the distinct counts of inputs and step inputs, plus 7.
        $queryBuilder->addSelect(
            "(COUNT(DISTINCT {$inputAlias}.id) + COUNT(DISTINCT {$stepInputAlias}.id) + 7) AS HIDDEN productInputsCount"
        );
        $queryBuilder->groupBy("{$rootAlias}.id");
        $queryBuilder->orderBy('productInputsCount', $sortDirection);
    }

    public function getDescription(string $resourceClass): array
    {
        if (!$this->properties) {
            return [];
        }
        return array_map(function ($strategy) {
            return [
                'property' => 'numberOfInputs',
                'type' => Type::BUILTIN_TYPE_STRING,
                'required' => false,
                'description' => 'Sort by the number of product inputs in logistics.',
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

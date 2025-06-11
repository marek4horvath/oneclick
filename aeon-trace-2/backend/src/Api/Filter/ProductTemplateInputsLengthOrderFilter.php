<?php declare(strict_types=1);

namespace App\Api\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Product\Input;
use App\Entity\Product\ProductTemplate;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\PropertyInfo\Type;

class ProductTemplateInputsLengthOrderFilter extends AbstractFilter
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
        if ($property !== 'order' || !is_array($value) || !isset($value['inputs']) || $resourceClass !== ProductTemplate::class) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $stepsTemplateAlias = $queryNameGenerator->generateJoinAlias('stepsTemplate');
        $stepAlias = $queryNameGenerator->generateJoinAlias('steps');
        $inputAlias = $queryNameGenerator->generateJoinAlias('inputs');

        $queryBuilder->leftJoin("{$rootAlias}.stepsTemplate", $stepsTemplateAlias)
            ->leftJoin("{$stepsTemplateAlias}.steps", $stepAlias)
            ->leftJoin("{$stepAlias}.inputs", $inputAlias)
            ->addSelect($stepsTemplateAlias)
            ->addSelect($stepAlias)
            ->addSelect($inputAlias);

        $subQuery = $queryBuilder->getEntityManager()->createQueryBuilder()
            ->select('COUNT(i.id)')
            ->from(Input::class, 'i')
            ->leftJoin('i.step', 's')
            ->leftJoin('s.stepsTemplate', 'st')
            ->where("st = {$stepsTemplateAlias}")
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
                'description' => 'Sort product templates by the number of inputs in steps.',
                'schema' => ['type' => 'string', 'enum' => ['asc', 'desc']],
                'openapi' => ['allowReserved' => false, 'allowEmptyValue' => true, 'explode' => false],
            ],
        ];
    }
}

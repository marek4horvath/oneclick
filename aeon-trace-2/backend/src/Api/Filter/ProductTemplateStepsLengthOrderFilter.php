<?php declare(strict_types=1);

namespace App\Api\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Product\ProductTemplate;
use App\Entity\Step\Step;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\PropertyInfo\Type;

class ProductTemplateStepsLengthOrderFilter extends AbstractFilter
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
        if ($property !== 'order' || !is_array($value) || !isset($value['steps']) || $resourceClass !== ProductTemplate::class) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $stepsTemplateAlias = $queryNameGenerator->generateJoinAlias('stepsTemplate');
        $stepAlias = $queryNameGenerator->generateJoinAlias('steps');

        $queryBuilder->leftJoin("{$rootAlias}.stepsTemplate", $stepsTemplateAlias)
            ->leftJoin("{$stepsTemplateAlias}.steps", $stepAlias)
            ->addSelect($stepsTemplateAlias)
            ->addSelect($stepAlias);

        $subQuery = $queryBuilder->getEntityManager()->createQueryBuilder()
            ->select('COUNT(s.id)')
            ->from(Step::class, 's')
            ->where("s.stepsTemplate = {$stepsTemplateAlias}")
            ->getDQL();

        $sortDirection = strtolower($value['steps']) === 'desc' ? 'DESC' : 'ASC';
        $queryBuilder->addSelect("({$subQuery}) as HIDDEN stepsCount");
        $queryBuilder->orderBy('stepsCount', $sortDirection);
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            'order[steps]' => [
                'property' => 'steps',
                'type' => Type::BUILTIN_TYPE_STRING,
                'required' => false,
                'description' => 'Sort product templates by the number of steps.',
                'schema' => ['type' => 'string', 'enum' => ['asc', 'desc']],
                'openapi' => ['allowReserved' => false, 'allowEmptyValue' => true, 'explode' => false],
            ],
        ];
    }
}

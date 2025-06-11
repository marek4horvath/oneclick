<?php declare(strict_types=1);

namespace App\Api\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\SupplyChain\Node;
use App\Entity\SupplyChain\SupplyChainTemplate;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\PropertyInfo\Type;

class SupplyChainNodesLengthOrderFilter extends AbstractFilter
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
        if ($property !== 'order' || !is_array($value) || !isset($value['numberOfNodes']) || $resourceClass !== SupplyChainTemplate::class) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $nodeAlias = $queryNameGenerator->generateJoinAlias('nodes');

        $queryBuilder->leftJoin("{$rootAlias}.nodes", $nodeAlias)
            ->addSelect($nodeAlias);

        $subQuery = $queryBuilder->getEntityManager()->createQueryBuilder()
            ->select('COUNT(n.id)')
            ->from(Node::class, 'n')
            ->where("n.supplyChainTemplate = {$rootAlias}")
            ->getDQL();

        $sortDirection = strtolower($value['numberOfNodes']) === 'desc' ? 'DESC' : 'ASC';
        $queryBuilder->addSelect("({$subQuery}) as HIDDEN nodesCount");
        $queryBuilder->orderBy('nodesCount', $sortDirection);
    }

    public function getDescription(string $resourceClass): array
    {
        if (!$this->properties) {
            return [];
        }
        return array_map(function ($strategy) {
            return [
                'property' => 'numberOfNodes',
                'type' => Type::BUILTIN_TYPE_STRING,
                'required' => false,
                'description' => 'Sort by the number of nodes.',
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

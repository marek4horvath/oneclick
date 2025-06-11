<?php declare(strict_types=1);

namespace App\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Product\ProductTemplate;
use Doctrine\ORM\QueryBuilder;

final readonly class CollectionFilter implements QueryCollectionExtensionInterface
{
    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, Operation $operation = null, array $context = []): void
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        $alias = $queryBuilder->getRootAliases()[0];

        if ($resourceClass === ProductTemplate::class) {
            $queryBuilder->andWhere(sprintf('%s.supplyChainTemplate IS NULL', $alias));
        }

    }
}

<?php

declare(strict_types=1);

namespace App\Repository\SupplyChain;

use App\Entity\SupplyChain\NodePosition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method NodePosition|null find($id, $lockMode = null, $lockVersion = null)
 * @method NodePosition|null findOneBy(array $criteria, array $orderBy = null)
 * @method NodePosition[] findAll()
 * @method NodePosition[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @template T of NodePosition
 * @template-extends ServiceEntityRepository<T>
 */
class NodePositionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NodePosition::class);
    }
}

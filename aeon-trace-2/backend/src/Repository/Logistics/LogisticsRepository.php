<?php

declare(strict_types=1);

namespace App\Repository\Logistics;

use App\Entity\Logistics\Logistics;
use App\Entity\SupplyChain\Node;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Logistics|null find($id, $lockMode = null, $lockVersion = null)
 * @method Logistics|null findOneBy(array $criteria, array $orderBy = null)
 * @method Logistics[] findAll()
 * @method Logistics[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @template T of Logistics
 * @template-extends ServiceEntityRepository<T>
 */
class LogisticsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Logistics::class);
    }

    /**
     * @return Logistics[]
     */
    public function findFromNodeLogisticsWithParent(Node $node): array
    {
        $query = $this->createQueryBuilder('l')
            ->where('l.fromNode = :node')
            ->andWhere('l.logisticsParent IS NOT NULL')
            ->setParameter('node', $node)
            ->getQuery();

        /** @var Logistics[] $results */
        $results = $query->getResult();

        return $results;
    }

    public function findByToNode(Node $toNode): mixed
    {
        return $this->createQueryBuilder('l')
            ->select('l', 'f', 't')
            ->join('l.fromDpps', 'f')
            ->leftJoin('f.targetDppConnector', 't')
            ->where('l.toNode = :node')
            ->andWhere('t IS NULL')
            ->setParameter('node', $toNode->getId(), 'uuid')
            ->getQuery()
            ->getArrayResult();
    }
}

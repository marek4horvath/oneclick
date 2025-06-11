<?php

declare(strict_types=1);

namespace App\Repository\Dpp;

use App\Entity\Dpp\DppConnector;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DppConnector|null find($id, $lockMode = null, $lockVersion = null)
 * @method DppConnector|null findOneBy(array $criteria, array $orderBy = null)
 * @method DppConnector[] findAll()
 * @method DppConnector[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @template T of DppConnector
 * @template-extends ServiceEntityRepository<T>
 */
class DppConnectorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent:: __construct($registry, DppConnector::class);
    }
}

<?php

declare(strict_types=1);

namespace App\Repository\Product;

use App\Entity\Product\ProductInputHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProductInputHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductInputHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductInputHistory[] findAll()
 * @method ProductInputHistory[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @template T of ProductInputHistory
 * @template-extends ServiceEntityRepository<T>
 */
class ProductInputHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent:: __construct($registry, ProductInputHistory::class);
    }
}

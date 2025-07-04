<?php

declare(strict_types=1);

namespace App\Repository\Product;

use App\Entity\Product\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[] findAll()
 * @method Product[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @template T of Product
 * @template-extends ServiceEntityRepository<T>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent:: __construct($registry, Product::class);
    }
}

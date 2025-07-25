<?php

declare(strict_types=1);

namespace App\Repository\Product;

use App\Entity\Product\ProductAttribute;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProductAttribute|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductAttribute|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductAttribute[] findAll()
 * @method ProductAttribute[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @template T of ProductAttribute
 * @template-extends ServiceEntityRepository<T>
 */
class ProductAttributeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent:: __construct($registry, ProductAttribute::class);
    }

    public function findByProduct(string $productId): mixed
    {
        return $this->createQueryBuilder('attribute')
            ->andWhere('attribute.product = :productId')
            ->setParameter('productId', $productId)
            ->getQuery()
            ->getResult();
    }
}

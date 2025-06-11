<?php

declare(strict_types=1);

namespace App\Repository\Product;

use App\Entity\Product\ProductTemplate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProductTemplate|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductTemplate|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductTemplate[] findAll()
 * @method ProductTemplate[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @template T of ProductTemplate
 * @template-extends ServiceEntityRepository<T>
 */
class ProductTemplateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent:: __construct($registry, ProductTemplate::class);
    }
}

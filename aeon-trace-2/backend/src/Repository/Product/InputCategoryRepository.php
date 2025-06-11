<?php

declare(strict_types=1);

namespace App\Repository\Product;

use App\Entity\Product\InputCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method InputCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method InputCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method InputCategory[] findAll()
 * @method InputCategory[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @template T of InputCategory
 * @template-extends ServiceEntityRepository<T>
 */
class InputCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent:: __construct($registry, InputCategory::class);
    }
}

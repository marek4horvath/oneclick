<?php

declare(strict_types=1);

namespace App\Repository\Product;

use App\Entity\Product\Input;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Input|null find($id, $lockMode = null, $lockVersion = null)
 * @method Input|null findOneBy(array $criteria, array $orderBy = null)
 * @method Input[] findAll()
 * @method Input[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @template T of Input
 * @template-extends ServiceEntityRepository<T>
 */
class InputRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent:: __construct($registry, Input::class);
    }
}

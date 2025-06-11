<?php

declare(strict_types=1);

namespace App\Repository\Step;

use App\Entity\Step\Step;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Step|null find($id, $lockMode = null, $lockVersion = null)
 * @method Step|null findOneBy(array $criteria, array $orderBy = null)
 * @method Step[] findAll()
 * @method Step[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @template T of Step
 * @template-extends ServiceEntityRepository<T>
 */
class StepRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent:: __construct($registry, Step::class);
    }
}

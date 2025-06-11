<?php

declare(strict_types=1);

namespace App\Repository\Step;

use App\Entity\Step\StepPosition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method StepPosition|null find($id, $lockMode = null, $lockVersion = null)
 * @method StepPosition|null findOneBy(array $criteria, array $orderBy = null)
 * @method StepPosition[] findAll()
 * @method StepPosition[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @template T of StepPosition
 * @template-extends ServiceEntityRepository<T>
 */
class StepPositionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StepPosition::class);
    }
}

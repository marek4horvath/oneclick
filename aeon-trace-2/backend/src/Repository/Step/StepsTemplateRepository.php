<?php

declare(strict_types=1);

namespace App\Repository\Step;

use App\Entity\Step\StepsTemplate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method StepsTemplate|null find($id, $lockMode = null, $lockVersion = null)
 * @method StepsTemplate|null findOneBy(array $criteria, array $orderBy = null)
 * @method StepsTemplate[] findAll()
 * @method StepsTemplate[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @template T of StepsTemplate
 * @template-extends ServiceEntityRepository<T>
 */
class StepsTemplateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent:: __construct($registry, StepsTemplate::class);
    }
}

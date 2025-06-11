<?php

declare(strict_types=1);

namespace App\Repository\Logistics;

use App\Entity\Logistics\LogisticsTemplate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LogisticsTemplate|null find($id, $lockMode = null, $lockVersion = null)
 * @method LogisticsTemplate|null findOneBy(array $criteria, array $orderBy = null)
 * @method LogisticsTemplate[] findAll()
 * @method LogisticsTemplate[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @template T of LogisticsTemplate
 * @template-extends ServiceEntityRepository<T>
 */
class LogisticsTemplateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent:: __construct($registry, LogisticsTemplate::class);
    }
}

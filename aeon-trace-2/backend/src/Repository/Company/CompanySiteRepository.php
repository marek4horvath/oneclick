<?php

declare(strict_types=1);

namespace App\Repository\Company;

use App\Entity\Company\CompanySite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CompanySite|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompanySite|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompanySite[] findAll()
 * @method CompanySite[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @template T of CompanySite
 * @template-extends ServiceEntityRepository<T>
 */
class CompanySiteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent:: __construct($registry, CompanySite::class);
    }
}

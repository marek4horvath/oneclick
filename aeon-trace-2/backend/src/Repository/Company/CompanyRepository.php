<?php

declare(strict_types=1);

namespace App\Repository\Company;

use App\Entity\Company\Company;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Company|null find($id, $lockMode = null, $lockVersion = null)
 * @method Company|null findOneBy(array $criteria, array $orderBy = null)
 * @method Company[] findAll()
 * @method Company[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @template T of Company
 * @template-extends ServiceEntityRepository<T>
 */
class CompanyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent:: __construct($registry, Company::class);
    }
}

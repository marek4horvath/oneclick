<?php

declare(strict_types=1);

namespace App\Repository\SupplyChain;

use App\Entity\SupplyChain\SupplyChainTemplate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SupplyChainTemplate|null find($id, $lockMode = null, $lockVersion = null)
 * @method SupplyChainTemplate|null findOneBy(array $criteria, array $orderBy = null)
 * @method SupplyChainTemplate[] findAll()
 * @method SupplyChainTemplate[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @template T of SupplyChainTemplate
 * @template-extends ServiceEntityRepository<T>
 */
class SupplyChainTemplateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent:: __construct($registry, SupplyChainTemplate::class);
    }
}

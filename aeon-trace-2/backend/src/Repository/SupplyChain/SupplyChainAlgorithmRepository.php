<?php declare(strict_types=1);

namespace App\Repository\SupplyChain;

use App\Entity\SupplyChain\SupplyChainAlgorithm;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SupplyChainAlgorithm>
 */
class SupplyChainAlgorithmRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SupplyChainAlgorithm::class);
    }
}

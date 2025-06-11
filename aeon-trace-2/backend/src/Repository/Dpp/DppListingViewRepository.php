<?php

declare(strict_types=1);

namespace App\Repository\Dpp;

use App\Entity\Dpp\DppListingView;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DppListingView|null find($id, $lockMode = null, $lockVersion = null)
 * @method DppListingView|null findOneBy(array $criteria, array $orderBy = null)
 * @method DppListingView[] findAll()
 * @method DppListingView[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @template T of DppListingView
 * @template-extends ServiceEntityRepository<T>
 */
class DppListingViewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent:: __construct($registry, DppListingView::class);
    }
}

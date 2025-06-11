<?php

declare(strict_types=1);

namespace App\Repository\ImageCollection;

use App\Entity\ImageCollection\DppImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DppImage|null find($id, $lockMode = null, $lockVersion = null)
 * @method DppImage|null findOneBy(array $criteria, array $orderBy = null)
 * @method DppImage[] findAll()
 * @method DppImage[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @template T of DppImage
 * @template-extends ServiceEntityRepository<T>
 */
class DppImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent:: __construct($registry, DppImage::class);
    }
}

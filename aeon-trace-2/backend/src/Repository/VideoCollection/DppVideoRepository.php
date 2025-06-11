<?php

declare(strict_types=1);

namespace App\Repository\VideoCollection;

use App\Entity\VideoCollection\DppVideo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DppVideo|null find($id, $lockMode = null, $lockVersion = null)
 * @method DppVideo|null findOneBy(array $criteria, array $orderBy = null)
 * @method DppVideo[] findAll()
 * @method DppVideo[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @template T of DppVideo
 * @template-extends ServiceEntityRepository<T>
 */
class DppVideoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent:: __construct($registry, DppVideo::class);
    }
}

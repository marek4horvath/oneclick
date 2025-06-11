<?php

declare(strict_types=1);

namespace App\Repository\ImageCollection;

use App\Entity\ImageCollection\CompanySiteImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CompanySiteImage|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompanySiteImage|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompanySiteImage[] findAll()
 * @method CompanySiteImage[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @template T of CompanySiteImage
 * @template-extends ServiceEntityRepository<T>
 */
class CompanySiteImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent:: __construct($registry, CompanySiteImage::class);
    }
}

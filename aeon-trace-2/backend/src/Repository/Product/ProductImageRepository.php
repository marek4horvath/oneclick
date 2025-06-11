<?php declare(strict_types=1);

namespace App\Repository\Product;

use App\Entity\Product\ProductInputImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProductInputImage|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductInputImage|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductInputImage[] findAll()
 * @method ProductInputImage[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @template T of ProductInputImage
 * @template-extends ServiceEntityRepository<T>
 */
class ProductImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent:: __construct($registry, ProductInputImage::class);
    }
}

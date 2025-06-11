<?php declare(strict_types=1);

namespace App\Repository\Product;

use App\Entity\Product\ProductInput;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UuidType;

/**
 * @method ProductInput|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductInput|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductInput[] findAll()
 * @method ProductInput[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @template T of ProductInput
 * @template-extends ServiceEntityRepository<T>
 */
class ProductInputRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent:: __construct($registry, ProductInput::class);
    }

    public function findInputById(string $inputId): mixed
    {
        return $this->createQueryBuilder('i')
            ->where('i.id = :id')
            ->setParameter('id', $inputId, UuidType::NAME)
            ->getQuery()->getOneOrNullResult();
    }
}

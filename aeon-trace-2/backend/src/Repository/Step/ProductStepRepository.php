<?php

declare(strict_types=1);

namespace App\Repository\Step;

use App\Entity\Step\ProductStep;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

/**
 * @method ProductStep|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductStep|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductStep[] findAll()
 * @method ProductStep[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @template T of ProductStep
 * @template-extends ServiceEntityRepository<T>
 */
class ProductStepRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent:: __construct($registry, ProductStep::class);
    }

    /**
     * @return array<string, float>
     */
    public function getProductStepWithGeoData(Uuid $productStepId): array
    {
        $qb = $this->createQueryBuilder('ps');

        $qb->select(
            'COALESCE(cs.latitude, c.latitude) AS latitude',
            'COALESCE(cs.longitude, c.longitude) AS longitude',
            'COALESCE(cs.name, c.name) AS name'
        )
            ->leftJoin('ps.company', 'c')
            ->leftJoin('ps.companySite', 'cs')
            ->where('ps.id = :productStepId')
            ->setParameter('productStepId', $productStepId, UuidType::NAME)
            ->setMaxResults(1);

        /** @var array<string, float> $data */
        $data = $qb->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_SCALAR);

        return [
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'name' => $data['name'],
        ];
    }
}

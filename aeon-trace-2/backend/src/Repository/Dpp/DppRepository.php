<?php

declare(strict_types=1);

namespace App\Repository\Dpp;

use App\Entity\Dpp\Dpp;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

/**
 * @method Dpp|null find($id, $lockMode = null, $lockVersion = null)
 * @method Dpp|null findOneBy(array $criteria, array $orderBy = null)
 * @method Dpp[] findAll()
 * @method Dpp[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @template T of Dpp
 * @template-extends ServiceEntityRepository<T>
 */
class DppRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent:: __construct($registry, Dpp::class);
    }

    /**
     * @return array<string, float>
     */
    public function getDppWithGeoData(Uuid $dppId): array
    {
        $qb = $this->createQueryBuilder('d');

        $qb->select(
            'COALESCE(cs.latitude, c.latitude) AS latitude',
            'COALESCE(cs.longitude, c.longitude) AS longitude',
            'COALESCE(cs.name, c.name) AS name'
        )
            ->leftJoin('d.company', 'c')
            ->leftJoin('d.companySite', 'cs')
            ->where('d.id = :dppId')
            ->setParameter('dppId', $dppId, UuidType::NAME)
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

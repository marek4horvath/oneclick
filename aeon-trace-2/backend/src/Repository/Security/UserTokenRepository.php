<?php

declare(strict_types=1);

namespace App\Repository\Security;

use App\Entity\Security\UserToken;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UuidType;

/**
 * @method UserToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserToken[] findAll()
 * @method UserToken[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @template T of UserToken
 * @template-extends ServiceEntityRepository<T>
 */
class UserTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent:: __construct($registry, UserToken::class);
    }

    public function findValid(DateTimeImmutable $date, User $user): mixed
    {
        $qb = $this->createQueryBuilder('ut');
        $qb->where('ut.tokenOwner = :owner')
            ->andWhere('ut.createdAt >= :date')
            ->andWhere($qb->expr()->isNull('ut.usedAt'))
            ->setParameter('date', $date->modify('-2 days'))
            ->setParameter('owner', $user->getId(), UuidType::NAME);

        return $qb->getQuery()->getResult();
    }
}

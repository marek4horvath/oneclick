<?php declare(strict_types=1);

namespace App\DataProviders;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Collection\Process;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @template T of Process
 * @implements ProviderInterface<Process>
 */
readonly class UnassignedProcessDataProvider implements ProviderInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('p')
            ->from(Process::class, 'p')
            ->leftJoin('p.nodes', 'n')
            ->leftJoin('p.steps', 's')
            ->where('n.id IS NULL')
            ->andWhere('s.id IS NULL');

        /** @var Process[] $results */
        $results = $qb->getQuery()->getResult();

        return $results;
    }
}

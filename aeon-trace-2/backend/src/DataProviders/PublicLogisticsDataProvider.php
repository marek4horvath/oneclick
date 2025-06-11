<?php

declare(strict_types=1);

namespace App\DataProviders;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Company\Company;
use App\Entity\Logistics\Logistics;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @template T of Logistics
 * @implements ProviderInterface<Logistics>
 */
class PublicLogisticsDataProvider implements ProviderInterface
{
    private ?Company $company = null;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (!$operation instanceof Get) {
            throw new BadRequestException();
        }

        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            throw new BadRequestException();
        }

        /** @var string|null $companySlug */
        $companySlug = $request->get('companySlug');

        if ($companySlug !== null) {
            $this->company = $this->entityManager->getRepository(Company::class)->findOneBy(['slug' => $companySlug]);
        }

        $queryBuilder = $this->entityManager->getRepository(Logistics::class)->createQueryBuilder('l')
            ->andWhere('l.qrId = :qrId')
            ->setParameter('qrId', $request->get('qrId'));

        if ($this->company !== null) {
            $queryBuilder
                ->andWhere('l.company = :companyId')
                ->setParameter('companyId', $this->company->getId(), UuidType::NAME);
        } else {
            $queryBuilder->andWhere('l.company IS NULL');
        }

        /** @var ?Logistics $entity */
        $entity = $queryBuilder->getQuery()->getOneOrNullResult();

        if ($entity instanceof Logistics) {
            return $entity;
        }
            throw new NotFoundHttpException(Logistics::class . ' not found.', code: Response::HTTP_NOT_FOUND);

    }
}

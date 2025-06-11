<?php

declare(strict_types=1);

namespace App\DataProviders;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Company\Company;
use App\Entity\Dpp\Dpp;
use App\Entity\Step\ProductStep;
use App\Entity\Step\Step;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @template T1 of Step
 * @template T2 of Dpp
 * @template T3 of ProductStep
 * @implements ProviderInterface<Step|Dpp|ProductStep>
 */
readonly class QrEntityDataProvider implements ProviderInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RequestStack $requestStack,
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

        $className = $operation->getClass();
        $classObject = new $className();
        $class = get_class($classObject);
        $company = null;

        if (!$class) {
            throw new BadRequestException();
        }

        if (method_exists($classObject, 'getCompany')) {
            $companySlug = $request->get('companySlug');

            /** @var Company|null $company */
            $company = $this->entityManager->getRepository(Company::class)->createQueryBuilder('c')
                ->andWhere('c.slug = :slug')
                ->setParameter('slug', $companySlug)
                ->getQuery()
                ->getOneOrNullResult();
        }

        $queryBuilder = $this->entityManager->getRepository($class)->createQueryBuilder('e')
            ->andWhere('e.qrId = :qrId')
            ->setParameter('qrId', $request->get('qrId'));

        if ($company !== null) {
            $queryBuilder
                ->andWhere('e.company = :companyId')
                ->setParameter('companyId', $company->getId(), UuidType::NAME);
        }

        /** @var null|Dpp|Step|ProductStep|iterable<Dpp|Step|ProductStep> $entity */
        $entity = $queryBuilder->getQuery()->getOneOrNullResult();

        if ($entity === null) {
            throw new NotFoundHttpException($class . ' not found.');
        }

        if ($entity instanceof Dpp || $entity instanceof Step || $entity instanceof ProductStep) {
            return $entity;
        }

        throw new BadRequestException();
    }
}

<?php

declare(strict_types=1);

namespace App\DataProviders;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Dpp\Dpp;
use App\Services\Security\TimestampService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\UuidV6;

/**
 * @template T1 of JsonResponse
 * @implements ProviderInterface<JsonResponse>
 */
readonly class TimestampDataProvider implements ProviderInterface
{
    public function __construct(
        private TimestampService $timestampService,
        private EntityManagerInterface $entityManager,
    ) {
    }
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {

        if (!$operation instanceof Get) {
            throw new BadRequestException();
        }

        /** @var UuidV6 $uuid */
        $uuid = $uriVariables['id'];

        $className = $operation->getClass();
        $classObject = new $className();
        $class = get_class($classObject);

        if ($class) {
            /** @var Dpp $entity */
            $entity = $this->entityManager->getRepository($class)->findOneBy([
                'id' => $uuid->toRfc4122(),
            ]);

            return new JsonResponse([
                'verified' => $this->timestampService->verify($entity),
                'info' => $this->timestampService->getTimestampInfo($entity),
            ], Response::HTTP_OK);
        }

        return new JsonResponse([
            'verified' => false,
            'info' => [],
        ], Response::HTTP_NOT_FOUND);
    }

}

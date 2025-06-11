<?php

declare(strict_types=1);

namespace App\DataProviders;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\TransportType\TransportType;
use App\Enums\TransportTypeEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * @template T of TransportType
 * @implements ProviderInterface<TransportType>
 */
readonly class TransportTypeDataProvider implements ProviderInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $transportTypes = [];

        foreach (TransportTypeEnum::cases() as $enumCase) {
            $existingTransportType = $this->entityManager->getRepository(TransportType::class)
                ->findOneBy(['name' => $enumCase->value]);

            if (!$existingTransportType) {
                $transportType = new TransportType();
                $transportType->setName($enumCase->value);
                $transportType->setId(Uuid::v4());

                $transportTypes[] = $transportType;

                $this->entityManager->persist($transportType);
            } else {
                $transportTypes[] = $existingTransportType;
            }
        }

        $this->entityManager->flush();

        return $transportTypes;
    }
}

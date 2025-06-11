<?php

declare(strict_types=1);

namespace App\DataProviders;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\DataTransferObjects\Dpp\DppGeoData;
use App\Entity\Dpp\Dpp;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\Uid\Uuid;

/**
 * @template T1 of DppGeoData
 * @implements ProviderInterface<DppGeoData>
 */
readonly class DppGeoCollectionProvider implements ProviderInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (!$operation instanceof Get) {
            throw new BadRequestException('Wrong operation provided.');
        }

        /** @var Uuid|null $dppId */
        $dppId = $uriVariables['id'] ?? null;

        if (!$dppId) {
            throw new BadRequestException('DPP Id is required.');
        }

        $geoData = $this->entityManager->getRepository(Dpp::class)->getDppWithGeoData($dppId);

        return new DppGeoData(
            $geoData['latitude'] ?? null,
            $geoData['longitude'] ?? null,
            isset($geoData['name']) ? (string) $geoData['name'] : null,
        );
    }
}

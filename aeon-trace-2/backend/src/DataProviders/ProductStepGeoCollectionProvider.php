<?php declare(strict_types=1);

namespace App\DataProviders;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\DataTransferObjects\Dpp\DppGeoData;
use App\Entity\Step\ProductStep;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\Uid\Uuid;

/**
 * @template T1 of DppGeoData
 * @implements ProviderInterface<DppGeoData>
 */
readonly class ProductStepGeoCollectionProvider implements ProviderInterface
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

        /** @var Uuid|null $productStepId */
        $productStepId = $uriVariables['id'] ?? null;

        if (!$productStepId) {
            throw new BadRequestException('Product Step Id is required.');
        }

        $geoData = $this->entityManager->getRepository(ProductStep::class)->getProductStepWithGeoData($productStepId);

        return new DppGeoData(
            $geoData['latitude'] ?? null,
            $geoData['longitude'] ?? null,
            isset($geoData['name']) ? (string) $geoData['name'] : null,
        );
    }
}

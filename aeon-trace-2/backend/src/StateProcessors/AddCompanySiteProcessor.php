<?php declare(strict_types=1);

namespace App\StateProcessors;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Company\CompanySite;
use App\Services\Geo\GeoService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * @template T1 of CompanySite
 * @template T2 of CompanySite
 * @implements ProcessorInterface<CompanySite, CompanySite>
 */
readonly class AddCompanySiteProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private GeoService $geoService,
        private LoggerInterface $logger,
    ) {
    }

    /** @param CompanySite $data
     * @throws TransportExceptionInterface
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        try {
            $coordinates = $this->geoService->getGeoLocation($data);
            $data->setLatitude($coordinates['lat']);
            $data->setLongitude($coordinates['lng']);
        } catch (NotFoundHttpException|BadRequestException $e) {
            $e instanceof NotFoundHttpException ? $this->logger->info($e) : $this->logger->error($e);
        }

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        return $data;
    }
}

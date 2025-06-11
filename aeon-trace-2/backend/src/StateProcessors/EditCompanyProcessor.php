<?php

declare(strict_types=1);

namespace App\StateProcessors;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Company\Company;
use App\Entity\Company\CompanySite;
use App\Services\Geo\GeoService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * @implements ProcessorInterface<Company, Company>
 **/
readonly class EditCompanyProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private GeoService $geoService,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @param Company $data
     * @throws TransportExceptionInterface
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Company
    {
        try {
            $coordinates = $this->geoService->getGeoLocation($data);
            $data->setLatitude($coordinates['lat']);
            $data->setLongitude($coordinates['lng']);
        } catch (NotFoundHttpException|BadRequestException $e) {
            $e instanceof NotFoundHttpException ? $this->logger->info($e) : $this->logger->error($e);
        }

        /** @var CompanySite $site */
        foreach ($data->getSites() as $site) {
            try {
                $coordinates = $this->geoService->getGeoLocation($site);
                $site->setLatitude($coordinates['lat']);
                $site->setLongitude($coordinates['lng']);
            } catch (NotFoundHttpException|BadRequestException $e) {
                $e instanceof NotFoundHttpException ? $this->logger->info($e) : $this->logger->error($e);
            }
        }

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        return $data;
    }
}

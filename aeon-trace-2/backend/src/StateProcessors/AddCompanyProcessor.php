<?php

declare(strict_types=1);

namespace App\StateProcessors;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Company\Company;
use App\Entity\Company\CompanySite;
use App\Enums\Security\UserRole;
use App\Services\Geo\GeoService;
use App\Services\Security\RegistrationService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\String\ByteString;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * @implements ProcessorInterface<Company, Company>
 **/
readonly class AddCompanyProcessor implements ProcessorInterface
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private RegistrationService $registrationService,
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
        $slugger = new AsciiSlugger();
        $data->setSlug($slugger->slug($data->getName())->lower()->toString());
        $slugCheck = $this->entityManager->getRepository(get_class($data))->findOneBy([
            'slug' => $data->getSlug(),
        ]);

        if ($slugCheck !== null) {
            throw new ConflictHttpException(get_class($data) . ' with given name already exists.');
        }

        $data->setInvitationToken(ByteString::fromRandom(12)->toString());
        $data->setRoles([UserRole::ROLE_COMPANY_MANAGER->value]);

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

        foreach ($data->getTypeOfTransport() as $transportType) {
            if (!$this->entityManager->contains($transportType)) {
                $this->entityManager->persist($transportType);
            }
            $data->addTypeOfTransport($transportType);
        }

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        $this->registrationService->sendCompanyInvitationEmail($data);

        return $data;
    }
}

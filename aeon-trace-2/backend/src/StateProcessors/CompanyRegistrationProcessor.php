<?php

declare(strict_types=1);

namespace App\StateProcessors;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\DataTransferObjects\Registration\CompanyRegistrationInput;
use App\Entity\Company\Company;
use App\Entity\Embeddable\Address;
use App\Enums\Security\UserRole;
use App\Services\Geo\GeoService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * @template T of Company
 * @implements ProcessorInterface<CompanyRegistrationInput, Company>
 */
readonly class CompanyRegistrationProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DenormalizerInterface $denormalizer,
        private GeoService $geoService,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @param CompanyRegistrationInput $data
     * @throws Exception
     * @throws ExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $company = $this->entityManager->getRepository(Company::class)->findOneBy(['invitationToken' => $data->token]);

        if (!$company instanceof Company) {
            throw new NotFoundHttpException();
        }

        try {
            $address = $this->denormalizer->denormalize([
                'street' => $data->street,
                'houseNo' => $data->houseNo,
                'city' => $data->city,
                'postcode' => $data->postcode,
                'country' => $data->country,
            ], Address::class);

            $company->setAddress($address);

            $this->denormalizer->denormalize([
                'name' => $data->name,
                'email' => $data->email,
                'phone' => $data->phone,
                'password' => $data->password,
                'roles' => [UserRole::ROLE_COMPANY_MANAGER->value],
                'active' => true,
            ], Company::class, 'array', ['object_to_populate' => $company]);

            try {
                $coordinates = $this->geoService->getGeoLocation($company);
                $company->setLatitude($coordinates['lat']);
                $company->setLongitude($coordinates['lng']);
            } catch (NotFoundHttpException|BadRequestException $e) {
                $e instanceof NotFoundHttpException ? $this->logger->info($e) : $this->logger->error($e);
            }

            $this->entityManager->persist($company);
            $this->entityManager->flush();

            return $company;
        } catch (Exception $e) {
            if ($e->getCode() === 1062) {
                throw new ConflictHttpException('Integrity constraint violation: ' . $e->getMessage());
            }

            throw new Exception($e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }
}

<?php

declare(strict_types=1);

namespace App\StateProcessors;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Company\Company;
use App\Enums\Security\UserRole;
use App\Services\Security\RegistrationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\ByteString;

/**
 * @template T of Company
 * @implements ProcessorInterface<Company, Company>
 */
readonly class InviteCompanyProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RegistrationService $registrationService,
    ) {
    }

    /**
     * @param Company $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Company
    {
        $data->setInvitationToken(ByteString::fromRandom(12)->toString());
        $data->setRoles([UserRole::ROLE_COMPANY_MANAGER->value]);

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        $this->registrationService->sendCompanyRegistrationEmail($data);

        return $data;
    }
}

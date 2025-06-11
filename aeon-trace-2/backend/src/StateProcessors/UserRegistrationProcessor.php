<?php

declare(strict_types=1);

namespace App\StateProcessors;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use App\Services\Security\RegistrationService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\String\ByteString;

/**
 * @implements ProcessorInterface<User, User>
 **/
readonly class UserRegistrationProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RegistrationService $registrationService,
    ) {
    }

    /**
     * @param User $data
     * @throws Exception
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): User
    {
        try {
            $data->setInvitationToken(ByteString::fromRandom(12)->toString());
            $this->entityManager->persist($data);
            $this->entityManager->flush();

            $this->registrationService->sendUserInvitationEmail($data);
            return $data;
        } catch (Exception $e) {
            if ($e->getCode() === 1062) {
                throw new ConflictHttpException('Integrity constraint violation: ' . $e->getMessage());
            }

            throw new Exception($e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }
}

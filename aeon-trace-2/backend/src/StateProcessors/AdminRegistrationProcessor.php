<?php declare(strict_types=1);

namespace App\StateProcessors;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use App\Enums\Security\UserRole;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * @template T of User
 * @implements ProcessorInterface<User, User>
 */
readonly class AdminRegistrationProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param User $data
     * @throws Exception
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        try {
        $data->setRoles([UserRole::ROLE_SUPER_ADMIN->value]);
        $data->setActive(true);

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        return $data;
        } catch (Exception $e) {
            if ($e->getCode() === 1062) {
                throw new ConflictHttpException('Integrity constraint violation: ' . $e->getMessage());
            }

            throw new Exception($e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }
}

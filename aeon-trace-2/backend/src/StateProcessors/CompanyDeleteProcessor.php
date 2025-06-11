<?php

declare(strict_types=1);

namespace App\StateProcessors;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Company\Company;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * @template T1 of object
 * @template T2 of object
 * @implements ProcessorInterface<T1, T2>
 */
readonly class CompanyDeleteProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param Company $data
     * @throws Exception
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?Company
    {
        if (!$operation instanceof Delete) {
            throw new BadRequestException();
        }

        try {
            foreach ($data->getProductTemplates() as $productTemplate) {
                if ($productTemplate->getCompanies()->count() === 1) {
                    $this->entityManager->remove($productTemplate);
                }
            }

            foreach ($data->getSites() as $site) {
                $this->entityManager->remove($site);
            }

            foreach ($data->getUsers() as $user) {
                $this->entityManager->remove($user);
            }

            $this->entityManager->remove($data);
            $this->entityManager->flush();
        } catch (Exception $e) {
            if ($e->getCode() === 1451) {
                throw new ConflictHttpException('Unable to delete company: ' . $e->getMessage());
            }
            throw new Exception($e->getMessage(), $e->getCode(), $e->getPrevious());
        }

        return null;
    }
}

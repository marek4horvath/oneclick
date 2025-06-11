<?php

declare(strict_types=1);

namespace App\StateProcessors;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Product\ProductTemplate;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * @template T1 of object
 * @template T2 of object
 * @implements ProcessorInterface<T1, T2>
 */
readonly class ProductTemplateDeleteProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param ProductTemplate $data
     * @throws Exception
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?ProductTemplate
    {
        if (!$operation instanceof Delete) {
            throw new BadRequestException();
        }

        if (!$data instanceof ProductTemplate) {
            throw new ConflictHttpException('No product template provided', null, Response::HTTP_NOT_FOUND);
        }

        if($data->getStepsTemplate() && count($data->getStepsTemplate()->getSteps()) > 0) {
            throw new ConflictHttpException('The product template cannot be deleted because it contains steps.', null, Response::HTTP_CONFLICT);
        }

        try {
            $this->entityManager->remove($data);
            $this->entityManager->flush();
        } catch (Exception $e) {
            if ($e->getCode() === 1451) {
                throw new ConflictHttpException('Unable to delete product template: ' . $e->getMessage());
            }
            throw new Exception($e->getMessage(), $e->getCode(), $e->getPrevious());
        }

        return null;
    }
}

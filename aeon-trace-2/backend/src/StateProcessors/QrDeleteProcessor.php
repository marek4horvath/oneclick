<?php

declare(strict_types=1);

namespace App\StateProcessors;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Dpp\Dpp;
use App\Entity\Step\ProductStep;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Vich\UploaderBundle\Handler\UploadHandler;

/**
 * @template T1 of ProductStep
 * @template T2 of Dpp
 * @implements ProcessorInterface<T1, T2>
 */
readonly class QrDeleteProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UploadHandler $uploadHandler,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ProductStep|Dpp
    {
        if (!$operation instanceof Delete) {
            throw new BadRequestException();
        }

        $this->uploadHandler->remove($data, 'qrFile');

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        return $data;
    }
}

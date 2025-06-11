<?php

declare(strict_types=1);

namespace App\StateProcessors;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Product\ProductInput;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Vich\UploaderBundle\Handler\UploadHandler;

/**
 * @template T1 of object
 * @template T2 of object
 * @implements ProcessorInterface<T1, T2>
 */
readonly class DppDocumentUploadProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UploadHandler $uploadHandler,
    ) {
    }

    /** @param ProductInput $data */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): JsonResponse
    {
        if (!$operation instanceof Post) {
            throw new BadRequestException();
        }

        if ($data->file !== null) {
            // Verify if uploaded file is image
            if (getimagesize($data->file->getPathname()) === false) {
                throw new BadRequestException('Invalid document format');
            }
        }

        $this->uploadHandler->upload($data, 'dppDocument');

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        return new JsonResponse([
            'id' => $data->getId(),
            'fileName' => $data->dppDocument?->getBasename(),
        ], Response::HTTP_CREATED);
    }
}

<?php

declare(strict_types=1);

namespace App\StateProcessors;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Company\Company;
use App\Entity\ImageCollection\CompanySiteImage;
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
readonly class VideoUploadProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UploadHandler $uploadHandler,
    ) {
    }

    /** @param Company|CompanySiteImage $data */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): JsonResponse
    {
        if (!$operation instanceof Post) {
            throw new BadRequestException();
        }

        if ($data->file !== null) {
            // Verify if uploaded file is video
            if ($data->file->getMimeType() === null || preg_match('/video\/*/', $data->file->getMimeType()) === false) {
                throw new BadRequestException('Invalid video format');
            }
            $this->uploadHandler->remove($data, 'file');
        }

        $this->uploadHandler->upload($data, 'file');

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        return new JsonResponse([
            'id' => $data->getId(),
            'fileName' => $data->file?->getBasename(),
        ], Response::HTTP_CREATED);
    }
}

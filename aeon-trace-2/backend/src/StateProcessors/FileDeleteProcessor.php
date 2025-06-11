<?php

declare(strict_types=1);

namespace App\StateProcessors;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Product\ProductInput;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Vich\UploaderBundle\Handler\UploadHandler;

/**
 * @template T1 of object
 * @template T2 of object
 * @implements ProcessorInterface<T1, T2>
 */
readonly class FileDeleteProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UploadHandler $uploadHandler,
        private TranslatorInterface $translator,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): JsonResponse
    {
        if (!$operation instanceof Delete) {
            throw new BadRequestException();
        }

        // Soft delete for Product Input - Image or Document
        if ($data instanceof ProductInput) {

            // Return early if Input is not updatable.
            if (!$data->isUpdatable()) {
                return new JsonResponse(['Input could not be updated. Did you mark it as updatable?'], Response::HTTP_FORBIDDEN);
            }

            $uri = $operation->getUriTemplate();

            if ($uri === null) {
                throw new BadRequestException('Invalid uri for the request.');
            }

            if (str_contains($uri, 'input_image')) {
                $data->setImage(null);
            }

            if (str_contains($uri, 'input_document')) {
                $data->setDocument(null);
            }

        } else {
            $this->uploadHandler->remove($data, 'file');
        }

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        return new JsonResponse($this->translator->trans('Image deleted successfully', domain: 'messages'), Response::HTTP_NO_CONTENT);
    }
}

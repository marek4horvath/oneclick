<?php

declare(strict_types=1);

namespace App\StateProcessors;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Company\Company;
use App\Entity\ImageCollection\CompanySiteImage;
use App\Entity\Product\ProductInput;
use App\Entity\Product\ProductInputImage;
use App\Entity\User;
use App\Services\Converter\HeicToJpeg;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Maestroerror\HeicToJpg;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Vich\UploaderBundle\Handler\UploadHandler;

/**
 * @template T1 of object
 * @template T2 of object
 * @implements ProcessorInterface<T1, T2>
 */
readonly class ImageUploadProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UploadHandler $uploadHandler,
        private HeicToJpeg $heicToJpeg,
    ) {
    }

    /** @param Company|CompanySiteImage|User|ProductInput|ProductInputImage $data */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): JsonResponse
    {
        if (!$operation instanceof Post) {
            throw new BadRequestException();
        }

        $file = $data->getFile();
        if ($file !== null) {

            $mimeType = mime_content_type($file->getPathname());
            if ($mimeType === 'image/heic' || HeicToJpg::isHeic($file->getPathname())) {
                $file = $this->heicToJpeg->convert($file);
                $data->setFile($file);
            }

            if (getimagesize($file->getPathname()) === false) {
                throw new UnsupportedMediaTypeHttpException('Invalid image format');
            }

            // Do not remove image if the entity is Product Input - It later gets saved to history.
            if (!$data instanceof ProductInput) {
                $this->uploadHandler->remove($data, 'file');
            }
        }

        // Set updatedAt to ProductInput to trigger history listener (ProductInputHistoryListener).
        if (method_exists($data, 'getInput') && $data->getInput() instanceof ProductInput) {
            $productInput = $data->getInput();

            $productInput->setUpdatedAt(new DateTimeImmutable());
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

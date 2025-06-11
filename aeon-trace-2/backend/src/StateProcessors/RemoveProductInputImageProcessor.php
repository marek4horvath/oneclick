<?php

declare(strict_types=1);

namespace App\StateProcessors;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Product\ProductInput;
use App\Entity\Product\ProductInputImage;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

/**
 * @template T1 of object
 * @template T2 of object
 * @implements ProcessorInterface<T1, T2>
 */
readonly class RemoveProductInputImageProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param ProductInputImage $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ProductInputImage
    {
        if (!$data instanceof ProductInputImage) {
            throw new RuntimeException('Expected ProductInputImage');
        }

        if (!$data->getInput() instanceof ProductInput) {
            throw new RuntimeException('Entity already removed from collection.');
        }

        $productInput = $data->getInput();

        // Return early if Input is not updatable.
        if (!$productInput->isUpdatable()) {
            throw new RuntimeException('Input could not be updated. Did you mark it as updatable?', Response::HTTP_FORBIDDEN);
        }

        $productInput->removeImage($data);
        $productInput->setUpdatedAt(new DateTimeImmutable());

        $this->entityManager->persist($productInput);
        $this->entityManager->persist($data);

        $this->entityManager->flush();

        return $data;
    }
}

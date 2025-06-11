<?php

declare(strict_types=1);

namespace App\StateProcessors;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\SupplyChain\SupplyChainTemplate;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @template T1 of SupplyChainTemplate
 * @template T2 of SupplyChainTemplate
 * @implements ProcessorInterface<SupplyChainTemplate, SupplyChainTemplate>
 */
readonly class DeleteSupplyChainTemplateProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): SupplyChainTemplate
    {
        $data->setDeletedAt(new DateTimeImmutable());

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        return $data;
    }
}

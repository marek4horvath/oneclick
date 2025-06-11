<?php

declare(strict_types=1);

namespace App\StateProcessors;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\SupplyChain\Node;
use App\Services\Node\NodeService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @implements ProcessorInterface<Node, Node>
 **/
readonly class UpdateNodeProcessor implements ProcessorInterface
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private RequestStack $requestStack,
        private NodeService $nodeService,
    ) {
    }

    /**
     * @param Node $data
     * @throws ORMException
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Node
    {
        $this->nodeService->setParentsByGivenChildren($data, $this->requestStack);
        $this->entityManager->persist($data);
        $this->entityManager->flush();

        return $data;
    }
}

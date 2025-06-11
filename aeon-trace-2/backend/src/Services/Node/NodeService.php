<?php

declare(strict_types=1);

namespace App\Services\Node;

use App\Entity\SupplyChain\Node;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\HttpFoundation\RequestStack;

readonly class NodeService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @throws ORMException
     */
    public function setParentsByGivenChildren(Node $data, RequestStack $requestStack): void
    {
        $content = json_decode($requestStack->getCurrentRequest()?->getContent() ?: '', true);
        $children = [];
        if (is_array($content) && isset($content['children']) && is_array($content['children'])) {
            foreach ($content['children'] as $childIdPath) {
                $childId = substr($childIdPath, strrpos($childIdPath, '/') + 1);
                $queryBuilder = $this->entityManager->getRepository(Node::class)->createQueryBuilder('e')
                    ->andWhere('e.id = :id')
                    ->setParameter('id', $childId, UuidType::NAME);

                /** @var null|Node $child */
                $child = $queryBuilder->getQuery()->getOneOrNullResult();
                if ($child) {
                    $child->addParent($data);
                    $children[] = $child;
                }
            }
        }

        foreach ($children as $child) {
            $this->entityManager->persist($child);
        }
    }
}

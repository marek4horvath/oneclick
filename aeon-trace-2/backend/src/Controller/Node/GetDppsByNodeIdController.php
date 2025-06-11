<?php

declare(strict_types=1);

namespace App\Controller\Node;

use App\Entity\Dpp\Dpp;
use App\Entity\SupplyChain\Node;
use App\Repository\SupplyChain\NodeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class GetDppsByNodeIdController extends AbstractController
{
    /**
     * @param NodeRepository<Node> $nodeRepository
     */
    public function __construct(
        private readonly NodeRepository $nodeRepository,
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $idsJson = (string) $request->query->get('ids', '');
        $parentStepsJson = (string) $request->query->get('parentStepIds', '');

        if ($idsJson === '') {
            return new Response(
                json_encode(['error' => 'Invalid request. Missing or empty IDs parameter.']) ?: '{}',
                Response::HTTP_BAD_REQUEST,
                ['Content-Type' => 'application/json']
            );
        }

        /** @var string[]|null $ids */
        $ids = json_decode($idsJson, true);

        $parentSteps = json_decode($parentStepsJson, true);
        $parentSteps = is_array($parentSteps) ? $parentSteps : [];

        if (!is_array($ids)) {
            return new Response(
                json_encode(['error' => 'Invalid request. Expected an array of IDs.']) ?: '{}',
                Response::HTTP_BAD_REQUEST,
                ['Content-Type' => 'application/json']
            );
        }

        $dpps = [];

        foreach ($ids as $id) {
            $node = $this->nodeRepository->find($id);
            if (!$node instanceof Node) {
                continue;
            }

            if ($node->getDpps()->isEmpty() && $node->getProductSteps()->isEmpty()) {
                continue;
            }

            foreach ($node->getDpps() as $dpp) {
                if ($dpp->getState() !== Dpp::STATE_LOGISTICS && $dpp->getState() !== Dpp::STATE_DPP_IN_USE) {
                    continue;
                }

                $count = 0;

                foreach ($dpp->getProductSteps() as $step) {
                    if ($step->getState() === Dpp::STATE_EXPORT_DPP) {
                        continue;
                    }

                    $templateStep = $step->getStepTemplateReference();
                    if (!in_array($templateStep->getId()->jsonSerialize(), $parentSteps, true)) {
                        continue;
                    }

                    $count++;
                }

                if ($count !== 0) {
                    $sentWith = $dpp->getMaterialsSentWith();

                    $dpps[] = [
                        'id' => $dpp->getId(),
                        'name' => $dpp->getName(),
                        'type' => 'Dpp',
                        'logistics' => [
                            'id' => $sentWith?->getId(),
                            'name' => $sentWith?->getName(),
                        ],
                    ];
                }
            }

            foreach ($node->getProductSteps() as $productStep) {
                if ($productStep->getState() !== Dpp::STATE_LOGISTICS) {
                    continue;
                }

                if ($productStep->getDpp() !== null) {
                    continue;
                }

                $templateStep = $productStep->getStepTemplateReference();
                if (!in_array($templateStep->getId()->jsonSerialize(), $parentSteps, true)) {
                    continue;
                }

                $sentWith = $productStep->getMaterialsSentWith();

                $dpps[] = [
                    'id' => $productStep->getId(),
                    'name' => $productStep->getName(),
                    'type' => 'ProductStep',
                    'logistics' => [
                        'id' => $sentWith?->getId(),
                        'name' => $sentWith?->getName(),
                    ],
                ];
            }
        }

        if (empty($dpps)) {
            return new Response(
                $this->serializer->serialize(
                    ['dpps' => []],
                    'json'
                ),
                Response::HTTP_OK,
                ['Content-Type' => 'application/json']
            );
        }

        $jsonResponse = $this->serializer->serialize(
            ['dpps' => $dpps],
            'json'
        );

        return new Response(
            $jsonResponse ?: '{}',
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }
}

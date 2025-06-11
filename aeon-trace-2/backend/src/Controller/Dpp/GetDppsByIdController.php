<?php

declare(strict_types=1);

namespace App\Controller\Dpp;

use App\Entity\Dpp\Dpp;
use App\Repository\Dpp\DppRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class GetDppsByIdController extends AbstractController
{
    /**
     * @param DppRepository<Dpp> $dppRepository
     */
    public function __construct(
        private readonly DppRepository $dppRepository,
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
            $dpp = $this->dppRepository->find($id);
            if ($dpp instanceof Dpp) {
                $steps = [];

                foreach ($dpp->getProductSteps() as $step) {
                    if ($step->getState() === Dpp::STATE_EXPORT_DPP) {
                        continue;
                    }

                    $templateStep = $step->getStepTemplateReference();
                    if (!in_array($templateStep->getId()->jsonSerialize(), $parentSteps, true)) {
                        continue;
                    }

                    $steps[] = [
                        'id' => $step->getId(),
                        'name' => $step->getName(),
                        'state' => $step->getState(),
                    ];
                }

                $dpps[] = [
                    'id' => $dpp->getId(),
                    'name' => $dpp->getName(),
                    'state' => $dpp->getState(),
                    'productSteps' => $steps,
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

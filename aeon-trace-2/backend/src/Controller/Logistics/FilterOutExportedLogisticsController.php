<?php

declare(strict_types=1);

namespace App\Controller\Logistics;

use App\Entity\Dpp\Dpp;
use App\Entity\Logistics\Logistics;
use App\Repository\Logistics\LogisticsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class FilterOutExportedLogisticsController extends AbstractController
{
    /**
     * @param LogisticsRepository<Logistics> $logisticsRepository
     */
    public function __construct(
        private readonly LogisticsRepository $logisticsRepository,
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $ids = $this->parseIds($request);
        if ($ids === null) {
            return $this->jsonError();
        }

        $logisticsList = array_map([$this, 'filterLogistics'], $ids);
        $filteredLogistics = array_filter($logisticsList);

        return $this->jsonResponse([
            'logistics' => array_values($filteredLogistics),
        ]);
    }

    /**
     * @return string[]|null
     */
    private function parseIds(Request $request): ?array
    {
        $idsJson = $request->query->get('ids', '');
        $ids = json_decode((string) $idsJson, true);

        return is_array($ids) ? $ids : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function filterLogistics(string $id): ?array
    {
        $logistics = $this->logisticsRepository->find($id);
        if (!$logistics instanceof Logistics) {
            return null;
        }

        if ($logistics->getState() === Logistics::STATE_EXPORT_LOGISTICS) {
            return null;
        }

        $dppData = [];

        foreach ($logistics->getFromDpps() as $dpp) {
            if ($dpp->getState() === Dpp::STATE_EXPORT_DPP) {
                continue;
            }

            $dppData[] = ['id' => $dpp->getId()];
        }

        if (empty($dppData)) {
            return null;
        }

        return [
            'id' => $logistics->getId(),
            'name' => $logistics->getName(),
            'state' => $logistics->getState(),
            'fromDpps' => $dppData,
        ];
    }

    private function jsonError(): Response
    {
        return new Response(
            $this->serializer->serialize(['error' => 'Invalid request. Missing or malformed IDs parameter.'], 'json') ?: '{}',
            Response::HTTP_BAD_REQUEST,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function jsonResponse(array $data): Response
    {
        return new Response(
            $this->serializer->serialize($data, 'json') ?: '{}',
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }
}

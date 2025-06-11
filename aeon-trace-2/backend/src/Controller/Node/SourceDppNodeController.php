<?php

declare(strict_types=1);

namespace App\Controller\Node;

use App\Entity\Logistics\Logistics;
use App\Entity\SupplyChain\Node;
use App\Repository\Logistics\LogisticsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SourceDppNodeController extends AbstractController
{
    /**
     * @param LogisticsRepository<Logistics> $logisticsRepository
     */
    public function __construct(
        private readonly LogisticsRepository $logisticsRepository
    ) {
    }

    public function __invoke(Request $request, Node $node): Response
    {
        /** @var Logistics[] $logistics */
        $logistics = $this->logisticsRepository->findByToNode($node);

        $jsonLogistics = [];
        foreach ($logistics as $logistic) {
            $jsonLogistics[] = $logistic;
        }

        $jsonResponse = json_encode(['logistics' => $jsonLogistics]);
        $responseCode = Response::HTTP_OK;

        if ($jsonResponse === false) {
            throw new \RuntimeException('Failed to encode JSON: ' . json_last_error_msg());
        }

        return new Response(
            $jsonResponse,
            $responseCode,
            ['Content-Type' => 'application/json']
        );
    }
}

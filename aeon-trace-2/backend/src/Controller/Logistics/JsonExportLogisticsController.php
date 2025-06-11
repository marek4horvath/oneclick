<?php

declare(strict_types=1);

namespace App\Controller\Logistics;

use App\Entity\Logistics\Logistics;
use App\Entity\Logistics\StartingPoint;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class JsonExportLogisticsController extends AbstractController
{
    public function __invoke(Request $request, Logistics $logistics): Response
    {
        $jsonLogistics = new stdClass();
        $jsonLogistics->id = $logistics->getId();
        $jsonLogistics->description = $logistics->getDescription();
        $jsonLogistics->arrivalTime = $logistics->getArrivalTime()?->format('c');
        $jsonLogistics->company = $logistics->getCompany()?->getId();
        $jsonLogistics->departureTime = $logistics->getDepartureTime()?->format('c');
        $jsonLogistics->destinationPointLat = $logistics->getDestinationPointLat();
        $jsonLogistics->destinationPointLng = $logistics->getDestinationPointLng();

        foreach ($logistics->getFromDpps() as $fromDpp) {
            $jsonLogistics->fromDpps[] = $fromDpp->getId();
        }

        $jsonLogistics->fromNode = $logistics->getFromNode()->getId();

        $parentLogistics = $logistics->getLogisticsParent();
        if ($parentLogistics) {
            $jsonLogisticsParent = new stdClass();
            $jsonLogisticsParent->id = $parentLogistics->getId();
            $jsonLogisticsParent->description = $parentLogistics->getDescription();
            $jsonLogisticsParent->fromNode = $parentLogistics->getFromNode()->getId();
            $jsonLogisticsParent->name = $parentLogistics->getName();
            $jsonLogisticsParent->numberOfSteps = $parentLogistics->getNumberOfSteps();
            $jsonLogisticsParent->supplyChainTemplate = $parentLogistics->getSupplyChainTemplate()?->getId();
            $jsonLogisticsParent->toNode = $parentLogistics->getToNode()->getId();
            $jsonLogisticsParent->user = $parentLogistics->getUser()->getId();

            $jsonLogistics->logisticsParent = $jsonLogisticsParent;
        }

        $jsonLogistics->logisticsTemplate = $logistics->getLogisticsTemplate()?->getId();
        $jsonLogistics->name = $logistics->getName();
        $jsonLogistics->numberOfSteps = $logistics->getNumberOfSteps();
        /** @var StartingPoint $startingPointCoordinate */
        foreach ($logistics->getStartingPointCoordinates() as $startingPointCoordinate) {
            $startingPointCoordinateLogistics = [];
            /** @var Logistics $startingPointCoordinateLogistic */
            foreach ($startingPointCoordinate->getLogistics() as $startingPointCoordinateLogistic) {
                $startingPointCoordinateLogistics[] = $startingPointCoordinateLogistic->getId();
            }

            $jsonLogistics->startingPointCoordinates[] = [
                'latitude' => $startingPointCoordinate->getLatitude(),
                'longtitude' => $startingPointCoordinate->getLongitude(),
                'logistics' => $startingPointCoordinateLogistics,
            ];
        }
        $jsonLogistics->supplyChainTemplate = $logistics->getSupplyChainTemplate()?->getId();
        $jsonLogistics->toNode = $logistics->getToNode()->getId();
        $jsonLogistics->totalDistance = $logistics->getTotalDistance();
        $jsonLogistics->typeOfTransport = $logistics->getTypeOfTransport();
        $jsonLogistics->user = $logistics->getUser()->getId();

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

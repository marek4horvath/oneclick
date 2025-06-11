<?php

declare(strict_types=1);

namespace App\Controller\Logistics;

use ApiPlatform\Api\IriConverterInterface;
use App\Entity\Company\Company;
use App\Entity\Dpp\Dpp;
use App\Entity\Logistics\Logistics;
use App\Entity\Logistics\LogisticsTemplate;
use App\Entity\Logistics\StartingPoint;
use App\Entity\SupplyChain\Node;
use App\Entity\SupplyChain\SupplyChainTemplate;
use App\Entity\User;
use App\Services\Qr\QrService;
use App\Services\Security\TimestampService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Vich\UploaderBundle\Handler\UploadHandler;

class JsonToLogisticsController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly QrService $qrService,
        private readonly TimestampService $timestampService,
        private readonly UploadHandler $uploadHandler,
        private readonly IriConverterInterface $iriConverter,
    ) {
    }

    public function __invoke(Request $request): Logistics
    {
        /** @var stdClass|null $logisticsBody */
        $logisticsBody = json_decode($request->getContent());

        if (!is_object($logisticsBody)) {
            throw new InvalidArgumentException('Invalid JSON: Expected a creation body object.');
        }

        $requiredProps = [
            'company', 'arrivalTime', 'departureTime', 'typeOfTransport',
            'destinationPointLat', 'destinationPointLng', 'totalDistance',
            'fromDpps', 'fromNode', 'toNode', 'supplyChainTemplate',
            'numberOfSteps', 'logisticsTemplate', 'user', 'startingPointCoordinates',
        ];
        foreach ($requiredProps as $prop) {
            if (!property_exists($logisticsBody, $prop)) {
                throw new InvalidArgumentException("Missing required property: {$prop}");
            }
        }

        if (
            !is_string($logisticsBody->company) ||
            !is_string($logisticsBody->arrivalTime) ||
            !is_string($logisticsBody->departureTime) ||
            !is_string($logisticsBody->typeOfTransport) ||
            !is_numeric($logisticsBody->destinationPointLat) ||
            !is_numeric($logisticsBody->destinationPointLng) ||
            !is_numeric($logisticsBody->totalDistance) ||
            !is_array($logisticsBody->fromDpps) ||
            count($logisticsBody->fromDpps) == 0 ||
            !is_string($logisticsBody->fromNode) ||
            !is_string($logisticsBody->toNode) ||
            !is_string($logisticsBody->supplyChainTemplate) ||
            !is_numeric($logisticsBody->numberOfSteps) ||
            !is_string($logisticsBody->logisticsTemplate) ||
            !is_string($logisticsBody->user) ||
            !is_array($logisticsBody->startingPointCoordinates) ||
            count($logisticsBody->startingPointCoordinates) == 0
        ) {
            throw new InvalidArgumentException('Invalid data in JSON structure.');
        }

        // Resolve IRIs into entities
        $company = $this->resolveResource($logisticsBody->company, Company::class, 'Company IRI is invalid.');
        $logisticsFromNode = $this->resolveResource($logisticsBody->fromNode, Node::class, 'From Node IRI is invalid.');
        $logisticsToNode = $this->resolveResource($logisticsBody->toNode, Node::class, 'To Node IRI is invalid.');
        $logisticsSupplyChainTemplate = $this->resolveResource($logisticsBody->supplyChainTemplate, SupplyChainTemplate::class, 'SupplyChainTemplate IRI is invalid.');
        $logisticsLogisticsTemplate = $this->resolveResource($logisticsBody->logisticsTemplate, LogisticsTemplate::class, 'LogisticsTemplate IRI is invalid.');
        $logisticsUser = $this->resolveResource($logisticsBody->user, User::class, 'User IRI is invalid.');

        try {
            $arrivalTime = new DateTime($logisticsBody->arrivalTime);
        } catch (\DateMalformedStringException $e) {
            throw new InvalidArgumentException('Bad format of arrivalTime');
        }

        try {
            $departureTime = new DateTime($logisticsBody->departureTime);
        } catch (\DateMalformedStringException $e) {
            throw new InvalidArgumentException('Bad format of departureTime');
        }

        $logisticsParent = new Logistics();
        $logisticsParent->setName("{$logisticsFromNode->getName()} -> {$logisticsToNode->getName()}");
        $logisticsParent->setFromNode($logisticsFromNode);
        $logisticsParent->setToNode($logisticsToNode);
        $logisticsParent->setSupplyChainTemplate($logisticsSupplyChainTemplate);
        $logisticsParent->setNumberOfSteps((int) $logisticsBody->numberOfSteps);
        $logisticsParent->setUser($logisticsUser);

        $this->entityManager->persist($logisticsParent);
        $this->entityManager->flush();

        $logisticsParent->setName("{$logisticsParent->getId()} - {$logisticsParent->getName()}");

        $qrId = $this->qrService->generateQrId($logisticsParent, $this->entityManager);
        if ($qrId === null) {
            throw new ConflictHttpException('Logistics with given qrId already exists.');
        }

        $logisticsParent->setQrId($qrId);
        $timeStamp = $this->timestampService->createTimestamp($logisticsParent);
        if ($timeStamp instanceof DateTime) {
            $logisticsParent->setTsaVerifiedAt($timeStamp);
        }

        $qr = $this->qrService->generateQrCode($logisticsParent);
        $logisticsParent->setQrFile($qr);
        $this->uploadHandler->inject($logisticsParent, 'qrFile');

        $logistics = new Logistics();

        $logistics->setName("{$logisticsParent->getId()} - {$logisticsParent->getName()} - {$logisticsBody->typeOfTransport}");
        $logistics->setCompany($company);
        $logistics->setArrivalTime($arrivalTime);
        $logistics->setDepartureTime($departureTime);
        $logistics->setTypeOfTransport($logisticsBody->typeOfTransport);
        $logistics->setDestinationPointLat((float) $logisticsBody->destinationPointLat);
        $logistics->setDestinationPointLng((float) $logisticsBody->destinationPointLng);
        $logistics->setTotalDistance((float) $logisticsBody->totalDistance);
        $logistics->setFromNode($logisticsFromNode);
        $logistics->setToNode($logisticsToNode);
        $logistics->setSupplyChainTemplate($logisticsSupplyChainTemplate);
        $logistics->setNumberOfSteps((int) $logisticsBody->numberOfSteps);
        $logistics->setLogisticsTemplate($logisticsLogisticsTemplate);
        $logistics->setLogisticsParent($logisticsParent);

        foreach ($logisticsBody->startingPointCoordinates as $startingPointCoordinate) {
            $startingPoint = new StartingPoint();

            $startingPoint->setLatitude($startingPointCoordinate->latitude);
            $startingPoint->setLongitude($startingPointCoordinate->longitude);

            $logistics->addStartingPointCoordinate($startingPoint);
        }

        $logistics->setUser($logisticsUser);

        $this->entityManager->persist($logistics);
        $this->entityManager->flush();

        $logistics->setName("{$logistics->getId()} - {$logistics->getName()}");

        foreach ($logisticsBody->fromDpps as $fromDppIri) {
            $dpp = $this->resolveResource($fromDppIri, Dpp::class, 'Dpp IRI is invalid.');

            $logisticsParent->addFromDpp($dpp);
            $logistics->addFromDpp($dpp);
        }

        $qrId = $this->qrService->generateQrId($logistics, $this->entityManager);
        if ($qrId === null) {
            throw new ConflictHttpException('Logistics with given qrId already exists.');
        }

        $logistics->setQrId($qrId);
        $timeStamp = $this->timestampService->createTimestamp($logistics);
        if ($timeStamp instanceof DateTime) {
            $logistics->setTsaVerifiedAt($timeStamp);
        }

        $qr = $this->qrService->generateQrCode($logistics);
        $logistics->setQrFile($qr);
        $this->uploadHandler->inject($logistics, 'qrFile');

        $this->entityManager->flush();

        return $logistics;
    }

    /**
     * Helper to resolve an IRI into an entity of a given class.
     *
     * @template T of object
     * @param class-string<T> $expectedClass
     * @return T
     * @throws InvalidArgumentException
     */
    private function resolveResource(string $iri, string $expectedClass, string $errorMessage): object
    {
        try {
            $resource = $this->iriConverter->getResourceFromIri($iri);
        } catch (\Exception $e) {
            throw new InvalidArgumentException($errorMessage);
        }
        if (!$resource instanceof $expectedClass) {
            throw new InvalidArgumentException($errorMessage);
        }
        /** @var T $resource */
        return $resource;
    }
}

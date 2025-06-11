<?php

declare(strict_types=1);

namespace App\StateProcessors;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Logistics\Logistics;
use App\Services\Qr\QrService;
use App\Services\Security\TimestampService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Vich\UploaderBundle\Handler\UploadHandler;

/**
 * @template T1 of Logistics
 * @template T2 of Logistics
 * @implements ProcessorInterface<Logistics, Logistics>
 */
readonly class UpdateLogisticsProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private QrService $qrService,
        private TimestampService $timestampService,
        private UploadHandler $uploadHandler,
    ) {
    }

    /**
     * @param Logistics $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if (!$operation instanceof Patch) {
            throw new BadRequestException();
        }

        $this->entityManager->persist($data);

        $dppName = $data->getId() . ' - ' . $data->getFromNode()->getName() . ' -> ' . $data->getToNode()->getName() . ' - ' . $data->getName();
        $data->setName($dppName);

        $qrId = $this->qrService->generateQrId($data, $this->entityManager);

        if ($qrId === null) {
            throw new ConflictHttpException(get_class($data) . ' with given qrId already exists.');
        }

        $timeStamp = $this->timestampService->createTimestamp($data);
        if ($timeStamp instanceof DateTime) {
            $data->setTsaVerifiedAt($timeStamp);
        }

        $data->setQrImage(null);
        $data->setQrId($qrId);
        $qr = $this->qrService->generateQrCode($data);
        $data->setQrFile($qr);
        $this->uploadHandler->inject($data, 'qrFile');

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        return $data;
    }
}

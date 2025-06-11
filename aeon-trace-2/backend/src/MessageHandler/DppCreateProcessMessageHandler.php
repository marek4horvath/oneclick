<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Dpp\Dpp;
use App\Entity\Step\ProductStep;
use App\Message\DppCreateProcessMessage;
use App\Services\Qr\QrService;
use App\Services\Security\TimestampService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Vich\UploaderBundle\Handler\UploadHandler;

#[AsMessageHandler]
final class DppCreateProcessMessageHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private QrService $qrService,
        private TimestampService $timestampService,
        private UploadHandler $uploadHandler,
    ) {
    }

    public function __invoke(DppCreateProcessMessage $dppCreateProcessMessage): void
    {
        $queryBuilder = $this->entityManager->getRepository($dppCreateProcessMessage->getEntityClass())->createQueryBuilder('e')
            ->andWhere('e.id = :id')
            ->setParameter('id', $dppCreateProcessMessage->getUuid(), UuidType::NAME);

        /** @var null|Dpp|ProductStep $data */
        $data = $queryBuilder->getQuery()->getOneOrNullResult();

        if ($data instanceof Dpp || $data instanceof ProductStep) {
            if ($data->isCreateQr() || $data->isCreateEmptyDpp()) {

                if ($data->isCreateQr()) {
                    $timeStamp = $this->timestampService->createTimestamp($data);
                    if ($timeStamp instanceof DateTime) {
                        $data->setTsaVerifiedAt($timeStamp);
                    }
                }

                $data->setQrImage(null);
                $qr = $this->qrService->generateQrCode($data);

                $data->setQrFile($qr);
                $this->uploadHandler->inject($data, 'qrFile');
                $this->uploadHandler->upload($data, 'qrFile');

                $this->entityManager->flush();
            }
        }
    }
}

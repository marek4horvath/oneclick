<?php

declare(strict_types=1);

namespace App\StateProcessors;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Step\ProductStep;
use App\Message\DppCreateProcessMessage;
use App\Services\Qr\QrService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @template T1 of object
 * @template T2 of object
 * @implements ProcessorInterface<T1, T2>
 */
readonly class CreateProductStepProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private QrService $qrService,
        private MessageBusInterface $bus,
    ) {
    }

    /**
     * @param ProductStep $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ProductStep
    {
        if (!$operation instanceof Post) {
            throw new BadRequestException();
        }

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        $data->setDppName($data->getName() . ':' . $data->getId()->toRfc4122());
        $data->setCreatedAt(new DateTimeImmutable());

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        if ($data->isCreateQr() || $data->isCreateEmptyDpp()) {
            $qrId = $this->qrService->generateQrId($data, $this->entityManager);

            if ($qrId === null && ($data->getQrImage() === null || $data->getQrImage() === '')) {
                throw new ConflictHttpException(get_class($data) . ' with given qrId already exists.');
            }

            $data->setQrId($qrId);
        }

        $this->entityManager->flush();

        if ($data->isCreateQr() || $data->isCreateEmptyDpp()) {
            $this->bus->dispatch(new DppCreateProcessMessage($data->getId(), ProductStep::class));
        }

        return $data;
    }
}

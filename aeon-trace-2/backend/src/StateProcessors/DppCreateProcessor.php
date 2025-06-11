<?php

declare(strict_types=1);

namespace App\StateProcessors;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Dpp\Dpp;
use App\Message\DppCreateProcessMessage;
use App\Services\Qr\QrService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

/**
 * @template T1 of object
 * @template T2 of object
 * @implements ProcessorInterface<T1, T2>
 */
readonly class DppCreateProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private QrService $qrService,
        private MessageBusInterface $bus,
    ) {
    }

    /**
     * @param Dpp $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Dpp
    {
        if (!$operation instanceof Post && !$operation instanceof Patch) {
            throw new BadRequestException();
        }

        $data->setName($data->getNode()?->getName());

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        if ($data->isCreateQr() || $data->isCreateEmptyDpp()) {
            $qrId = $this->qrService->generateQrId($data, $this->entityManager);

            if ($qrId === null && ($data->getQrImage() === null || $data->getQrImage() === '')) {
                throw new ConflictHttpException(get_class($data) . ' with given qrId already exists.');
            }

            $data->setQrId($qrId);
            $this->entityManager->flush();
        }

        if ($operation instanceof Post) {
            $data->setCreatedAt(new DateTime());
            $products = $data->getProducts();

            foreach ($products as $product) {
                $productTemplate = $product->getProductTemplate();
                if ($productTemplate !== null) {
                    $productTemplate->setHaveDpp(true);
                    $this->entityManager->persist($productTemplate);
                }
            }
        }

        $this->entityManager->flush();

        if ($data->isCreateQr() || $data->isCreateEmptyDpp()) {
            $this->bus->dispatch(
                new DppCreateProcessMessage($data->getId(), Dpp::class),
                [new DelayStamp(500)]
            );
        }

        return $data;
    }

}

<?php

declare(strict_types=1);

namespace App\StateProcessors;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Dpp\Dpp;
use App\Entity\Step\ProductStep;
use App\Entity\Step\Step;
use App\Entity\SupplyChain\Node;
use App\Services\Node\NodeService;
use App\Services\Qr\QrService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Vich\UploaderBundle\Handler\UploadHandler;

/**
 * @template T1 of object
 * @template T2 of object
 * @implements ProcessorInterface<T1, T2>
 */
readonly class QrCreateProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private QrService $qrService,
        private UploadHandler $uploadHandler,
        private RequestStack $requestStack,
        private NodeService $nodeService,
    ) {
    }

    /**
     * @param Dpp|ProductStep|Step|Node $data
     * @throws ORMException
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Dpp|ProductStep|Step|Node
    {
        if (!$operation instanceof Post && !$operation instanceof Patch) {
            throw new BadRequestException();
        }

        $this->entityManager->persist($data);

        if ($data->isCreateQr()) {
            $qrId = $this->qrService->generateQrId($data, $this->entityManager);
            if ($qrId === null) {
                throw new ConflictHttpException(get_class($data) . ' with given qrId already exists.');
            }
            $data->setQrImage(null);
            $data->setQrId($qrId);
            $qr = $this->qrService->generateQrCode($data);
            $data->setQrFile($qr);
            $this->uploadHandler->inject($data, 'qrFile');

            if ($operation instanceof Post) {
                $this->entityManager->detach($data);
            }
            $this->entityManager->persist($data);
        }

        if ($data instanceof Node) {
            $this->nodeService->setParentsByGivenChildren($data, $this->requestStack);
        }

        $this->entityManager->flush();

        return $data;
    }
}

<?php

declare(strict_types=1);

namespace App\Controller\Downloads;

use App\Entity\Step\ProductStep;
use App\Services\Tsa\TsaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class DownloadProductStepTsaController extends AbstractController
{
    public function __construct(
        private readonly TsaService $tsaService,
    ) {
    }

    public function downloadProductStepTsaFile(ProductStep $item): BinaryFileResponse
    {
        $data = $this->tsaService->downloadTsaFile($item->getTimestampPath(), $item->getName());

        return (new BinaryFileResponse($data['file']))
            ->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $data['name']);
    }
}

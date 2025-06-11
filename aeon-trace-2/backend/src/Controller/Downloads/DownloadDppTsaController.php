<?php

declare(strict_types=1);

namespace App\Controller\Downloads;

use App\Entity\Dpp\Dpp;
use App\Services\Tsa\TsaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class DownloadDppTsaController extends AbstractController
{
    public function __construct(
        private readonly TsaService $tsaService,
    ) {
    }

    public function downloadDppTsaFile(Dpp $item): BinaryFileResponse
    {
        $data = $this->tsaService->downloadTsaFile($item->getTimestampPath(), $item->getName() ?: 'Dpp');

        return (new BinaryFileResponse($data['file']))
            ->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $data['name']);
    }
}

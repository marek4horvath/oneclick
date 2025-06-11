<?php

declare(strict_types=1);

namespace App\Services\Tsa;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use UnexpectedValueException;

readonly class TsaService
{
    public function __construct(
        private ParameterBagInterface $parameterBag
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function downloadTsaFile(string $path, string $name): array
    {
        $projectDir = $this->parameterBag->get('kernel.project_dir');

        if (!is_string($projectDir)) {
            throw new UnexpectedValueException('Expected kernel.project_dir to be a string.');
        }

        $publicDir = $projectDir . '/public';

        $filePath = $publicDir . '/' . $path;

        if (!file_exists($filePath)) {
            throw new NotFoundHttpException('TSA file not found: ' . $filePath);
        }

        $downloadName = $name . '.txt';

        return [
            'file' => $filePath,
            'name' => $downloadName,
        ];
    }
}

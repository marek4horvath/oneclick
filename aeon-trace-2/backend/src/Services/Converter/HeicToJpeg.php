<?php

declare(strict_types=1);

namespace App\Services\Converter;

use App\Kernel;
use Maestroerror\HeicToJpg;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Vich\UploaderBundle\FileAbstraction\ReplacingFile;

readonly class HeicToJpeg
{
    public function __construct(
        private Kernel $kernel,
    ) {
    }

    public function convert(File $image): File
    {
        try {
            $convertedImagePath = $this->getTmpDir() . '/' . uniqid() . '.jpg';
            HeicToJpg::convert($image->getRealPath())->saveAs($convertedImagePath);
            return new ReplacingFile($convertedImagePath);
        } catch (RuntimeException) {
            throw new UnsupportedMediaTypeHttpException('Invalid HEIC format.');
        }

    }

    protected function getTmpDir(): string
    {
        $tmpDir = $this->kernel->getProjectDir() . '/public/media/tmp';
        if (!is_dir($tmpDir)) {
            if (!mkdir($tmpDir)) {
                return sys_get_temp_dir();
            }
        }

        return $tmpDir;
    }
}

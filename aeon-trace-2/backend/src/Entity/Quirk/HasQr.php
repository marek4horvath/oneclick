<?php declare(strict_types=1);

namespace App\Entity\Quirk;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * Define GET and SET methods for entities that needs to have qr code
 */
trait HasQr
{
    #[Vich\UploadableField(mapping: '', fileNameProperty: 'qrImage')]
    public ?File $qrFile = null;

    #[ORM\Column(type: Types::STRING, nullable: true, options: [
        'default' => '',
    ])]
    private ?string $qrImage = '';

    #[ORM\Column(type: Types::BOOLEAN, nullable: false, options: [
        'default' => false,
    ])]
    private bool $createQr = false;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $qrId = null;

    public function getQrFile(): ?File
    {
        return $this->qrFile;
    }

    public function setQrFile(?File $qrFile): void
    {
        $this->qrFile = $qrFile;
    }

    public function getQrImage(): ?string
    {
        return $this->qrImage;
    }

    public function setQrImage(?string $qrImage): void
    {
        $this->qrImage = $qrImage;
    }

    public function isCreateQr(): bool
    {
        return $this->createQr;
    }

    public function setCreateQr(bool $createQr): void
    {
        $this->createQr = $createQr;
    }

    public function getQrId(): ?int
    {
        return $this->qrId;
    }

    public function setQrId(?int $qrId): void
    {
        $this->qrId = $qrId;
    }
}

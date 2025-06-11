<?php

declare(strict_types=1);

namespace App\Entity\ImageCollection;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Dpp\Dpp;
use App\Repository\ImageCollection\DppImageRepository;
use App\StateProcessors\ImageUploadProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(
            inputFormats: [
                'multipart' => ['multipart/form-data'],
            ],
            normalizationContext: [],
            denormalizationContext: [
                'groups' => [self::IMAGE_WRITE],
            ],
            processor: ImageUploadProcessor::class
        ),
        new Patch(),
        new Delete(),
    ],
    normalizationContext: [
        'groups' => [
            self::IMAGE_READ,
            self::DPP_IMAGE_READ,
        ],
    ],
    denormalizationContext: [
        'groups' => [self::IMAGE_WRITE],
    ]
)]
#[ORM\Entity(repositoryClass: DppImageRepository::class)]
#[ORM\Index(columns: ['image'])]
#[Vich\Uploadable]
class DppImage
{
    public const IMAGE_READ = 'image_read';

    public const DPP_IMAGE_READ = 'dpp_image_read';

    public const IMAGE_WRITE = 'image_write';

    #[Vich\UploadableField(mapping: 'dpp_images', fileNameProperty: 'image')]
    #[Groups([self::IMAGE_WRITE])]
    public ?File $file = null;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups([
        self::IMAGE_READ,
        self::DPP_IMAGE_READ,
    ])]
    private Uuid $id;

    #[ORM\Column(type: Types::STRING, nullable: true, options: [
        'default' => '',
    ])]
    #[Groups([
        self::IMAGE_READ,
        self::DPP_IMAGE_READ,
    ])]
    private ?string $image = '';

    #[ORM\ManyToOne(targetEntity: Dpp::class, inversedBy: 'dppImages')]
    #[Groups([
        self::IMAGE_READ,
        self::IMAGE_WRITE,
    ])]
    private Dpp $dpp;

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(File $file): void
    {
        $this->file = $file;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): void
    {
        $this->id = $id;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): void
    {
        $this->image = $image;
    }

    public function getDpp(): Dpp
    {
        return $this->dpp;
    }

    public function setDpp(Dpp $dpp): void
    {
        $this->dpp = $dpp;
    }
}

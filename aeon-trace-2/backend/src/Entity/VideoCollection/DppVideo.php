<?php

declare(strict_types=1);

namespace App\Entity\VideoCollection;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Dpp\Dpp;
use App\Repository\VideoCollection\DppVideoRepository;
use App\StateProcessors\VideoUploadProcessor;
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
                'groups' => [self::VIDEO_WRITE],
            ],
            processor: VideoUploadProcessor::class
        ),
        new Patch(),
        new Delete(),
    ],
    normalizationContext: [
        'groups' => [
            self::VIDEO_READ,
            self::DPP_VIDEO_READ,
        ],
    ],
    denormalizationContext: [
        'groups' => [self::VIDEO_WRITE],
    ]
)]
#[ORM\Entity(repositoryClass: DppVideoRepository::class)]
#[ORM\Index(columns: ['video'])]
#[Vich\Uploadable]
class DppVideo
{
    public const VIDEO_READ = 'video_read';

    public const DPP_VIDEO_READ = 'dpp_video_read';

    public const VIDEO_WRITE = 'video_write';

    #[Vich\UploadableField(mapping: 'dpp_videos', fileNameProperty: 'video')]
    #[Groups([self::VIDEO_WRITE])]
    public ?File $file = null;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups([
        self::VIDEO_READ,
        self::DPP_VIDEO_READ,
    ])]
    private Uuid $id;

    #[ORM\Column(type: Types::STRING, nullable: true, options: [
        'default' => '',
    ])]
    #[Groups([
        self::VIDEO_READ,
        self::DPP_VIDEO_READ,
    ])]
    private ?string $video = '';

    #[ORM\ManyToOne(targetEntity: Dpp::class, inversedBy: 'dppVideos')]
    #[Groups([
        self::VIDEO_READ,
        self::VIDEO_WRITE,
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

    public function getVideo(): ?string
    {
        return $this->video;
    }

    public function setVideo(?string $video): void
    {
        $this->video = $video;
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

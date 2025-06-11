<?php

declare(strict_types=1);

namespace App\Entity\Dpp;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ApiResource(
    operations: [
        new Get(normalizationContext: [
            'groups' => [self::GROUP_READ],
        ]),
        new GetCollection(normalizationContext: [
            'groups' => [self::GROUP_READ],
        ]),
        new Post(
            denormalizationContext: [
                'groups' => [self::GROUP_WRITE],
            ],
        ),
        new Patch(
            denormalizationContext: [
                'groups' => [self::GROUP_WRITE],
            ],
        ),
        new Delete(),
    ],
)]
#[ORM\Entity]
#[ORM\Table(name: 'dpp_connector')]
class DppConnector
{
    public const GROUP_READ = 'dpp_connector:read';
    public const GROUP_WRITE = 'dpp_connector:write';

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups([
        self::GROUP_READ,
    ])]
    private ?Uuid $id = null;

    #[ORM\OneToOne(targetEntity: Dpp::class, inversedBy: 'targetDppConnector')]
    #[ORM\JoinColumn(unique: true, nullable: false)]
    #[Groups([
        self::GROUP_READ,
        self::GROUP_WRITE,
    ])]
    private Dpp $sourceDpp;

    #[ORM\ManyToOne(targetEntity: Dpp::class, inversedBy: 'sourceDppConnectors')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([
        self::GROUP_READ,
        self::GROUP_WRITE,
    ])]
    private Dpp $targetDpp;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getSourceDpp(): Dpp
    {
        return $this->sourceDpp;
    }

    public function setSourceDpp(Dpp $sourceDpp): self
    {
        $this->sourceDpp = $sourceDpp;

        return $this;
    }

    public function getTargetDpp(): Dpp
    {
        return $this->targetDpp;
    }

    public function setTargetDpp(Dpp $targetDpp): self
    {
        $this->targetDpp = $targetDpp;

        return $this;
    }
}

<?php

declare(strict_types=1);

namespace App\Entity\Dpp;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Api\Filter\DppByNodeIdFilter;
use App\Api\Filter\DppByOngoingDppFilter;
use App\Api\Filter\DppByStateFilter;
use App\Api\Filter\ListingViewUserOrderFilter;
use App\Entity\Quirk\HasUid;
use App\Entity\SupplyChain\Node;
use App\Repository\Dpp\DppListingViewRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/dppListingView',
        ),

        new Get(
            uriTemplate: '/dppListingView/{id}',
            requirements: ['id' => '[0-9a-fA-F\-]{36}'],
        ),
    ],
    order: ['createdAt' => 'DESC'],
)]
#[ORM\Entity(repositoryClass: DppListingViewRepository::class)]
#[ApiFilter(filterClass: DppByNodeIdFilter::class, properties: ['node.id' => 'exact'])]
#[ApiFilter(filterClass: DppByStateFilter::class, properties: ['dpp.state' => 'exact'])]
#[ApiFilter(filterClass: DppByOngoingDppFilter::class, properties: ['dpp.ongoingDpp' => 'exact'])]
#[ApiFilter(ListingViewUserOrderFilter::class, properties: ['order[userData]'])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt', 'name', 'tsaVerifiedAt', 'numberOfInputs'])]
#[ApiFilter(SearchFilter::class, properties: ['name' => 'partial'])]
class DppListingView
{
    use HasUid;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: false)]
    private Uuid $id;

    #[ORM\Column(type: Types::STRING, options: ['default' => null])]
    private ?string $type = null;

    #[ORM\Column(type: Types::STRING, options: ['default' => null])]
    private ?string $name = null;

    #[ORM\Column(type: Types::STRING, nullable: true, options: [
        'default' => '',
    ])]
    private ?string $qrImage = '';

    #[ORM\Column(type: Types::BOOLEAN, nullable: false, options: [
        'default' => false,
    ])]
    private bool $ongoingDpp = false;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $state;

    #[ORM\ManyToOne(targetEntity: Node::class)]
    private ?Node $node = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTime $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTime $tsaVerifiedAt = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $numberOfInputs = null;

    #[ORM\Column(type: UuidType::NAME, nullable: true)]
    private ?Uuid $userId = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $userFirstName = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $userLastName = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $timestampPath = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $updatable = false;

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getQrImage(): ?string
    {
        return $this->qrImage;
    }

    public function setQrImage(?string $qrImage): void
    {
        $this->qrImage = $qrImage;
    }

    public function isOngoingDpp(): bool
    {
        return $this->ongoingDpp;
    }

    public function setOngoingDpp(bool $ongoingDpp): void
    {
        $this->ongoingDpp = $ongoingDpp;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): void
    {
        $this->state = $state;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getTsaVerifiedAt(): ?DateTime
    {
        return $this->tsaVerifiedAt;
    }

    public function setTsaVerifiedAt(?DateTime $tsaVerifiedAt): void
    {
        $this->tsaVerifiedAt = $tsaVerifiedAt;
    }

    public function getNumberOfInputs(): ?int
    {
        return $this->numberOfInputs;
    }

    public function setNumberOfInputs(?int $numberOfInputs): void
    {
        $this->numberOfInputs = $numberOfInputs;
    }

    public function getNode(): ?Node
    {
        return $this->node;
    }

    public function setNode(?Node $node): void
    {
        $this->node = $node;
    }

    public function setUserId(?Uuid $userId): void
    {
        $this->userId = $userId;
    }

    public function setUserFirstName(?string $userFirstName): void
    {
        $this->userFirstName = $userFirstName;
    }


    public function setUserLastName(?string $userLastName): void
    {
        $this->userLastName = $userLastName;
    }

    public function getTimestampPath(): ?string
    {
        return $this->timestampPath;
    }

    public function setTimestampPath(?string $timestampPath): void
    {
        $this->timestampPath = $timestampPath;
    }

    public function getUpdatable(): bool
    {
        return $this->updatable;
    }

    public function setUpdatable(bool $updatable): void
    {
        $this->updatable = $updatable;
    }

    /**
     * @return array{id: string, firstName: string|null, lastName: string|null}
     */
    public function getUserData(): array
    {
        return [
            'id' => (string) $this->userId,
            'firstName' => $this->userFirstName,
            'lastName' => $this->userLastName,
        ];
    }
}

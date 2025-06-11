<?php

declare(strict_types=1);

namespace App\Entity\Collection;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\DataProviders\UnassignedProcessDataProvider;
use App\Entity\Quirk\HasUid;
use App\Entity\Step\Step;
use App\Entity\SupplyChain\Node;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/processes/unassigned',
            provider: UnassignedProcessDataProvider::class,
        ),
        new GetCollection(),
        new Get(),
        new Post(),
        new Patch(),
        new Delete(),
    ],
    normalizationContext: ['groups' => [self::PROCESS_READ]],
    denormalizationContext: ['groups' => [self::PROCESS_WRITE]],
    order: ['createdAt' => 'DESC'],
)]

#[ApiFilter(SearchFilter::class, properties: ['processType' => 'exact', 'name' => 'partial'])]
#[ORM\Entity]
#[ORM\Table(name: 'process')]
class Process
{
    use HasUid;

    public const PROCESS_READ = 'process:read';
    public const PROCESS_WRITE = 'process:write';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups([
        self::PROCESS_READ,
        self::PROCESS_WRITE,
        Node::NODE_READ,
        Node::NODE_WRITE,
        Node::SUPPLY_CHAIN_TEMPLATE_NODE_READ,
        Node::SUPPLY_CHAIN_TEMPLATE_NODE_WRITE,
    ])]
    private Uuid $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups([
        self::PROCESS_READ,
        self::PROCESS_WRITE,
        Node::NODE_READ,
        Node::NODE_WRITE,
        Node::SUPPLY_CHAIN_TEMPLATE_NODE_READ,
        Node::SUPPLY_CHAIN_TEMPLATE_NODE_WRITE,
    ])]
    private string $name;

    #[ORM\Column(type: 'string', length: 9, nullable: true, options: ['default' => '#ffffff'])]
    #[Groups([
        self::PROCESS_READ,
        self::PROCESS_WRITE,
        Node::NODE_READ,
        Node::NODE_WRITE,
        Node::SUPPLY_CHAIN_TEMPLATE_NODE_READ,
        Node::SUPPLY_CHAIN_TEMPLATE_NODE_WRITE,
    ])]
    private ?string $color = '#ffffff';

    #[ORM\Column(type: 'string', length: 50)]
    #[Groups([
        self::PROCESS_READ,
        self::PROCESS_WRITE,
        Node::NODE_READ,
        Node::NODE_WRITE,
        Node::SUPPLY_CHAIN_TEMPLATE_NODE_READ,
        Node::SUPPLY_CHAIN_TEMPLATE_NODE_WRITE,
    ])]
    private string $processType;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['default' => null])]
    #[Groups([
        self::PROCESS_READ,
    ])]
    private ?DateTimeImmutable $createdAt;

    /**
     * @var Collection<int, Node>
     */
    #[ORM\OneToMany(targetEntity: Node::class, mappedBy: 'typeOfProcess', cascade: ['persist'])]
    #[Groups([
        self::PROCESS_READ,
    ])]
    private Collection $nodes;

    /**
     * @var Collection<int, Step>
     */
    #[ORM\OneToMany(targetEntity: Step::class, mappedBy: 'process', cascade: ['persist'])]
    #[Groups([
        self::PROCESS_READ,
    ])]
    private Collection $steps;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->nodes = new ArrayCollection();
        $this->steps = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): void
    {
        $this->color = $color;
    }

    public function getProcessType(): string
    {
        return $this->processType;
    }

    public function setProcessType(string $processType): void
    {
        $this->processType = $processType;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return Collection<int, Node>
     */
    public function getNodes(): Collection
    {
        return $this->nodes;
    }

    /**
     * @return Collection<int, Step>
     */
    public function getSteps(): Collection
    {
        return $this->steps;
    }
}

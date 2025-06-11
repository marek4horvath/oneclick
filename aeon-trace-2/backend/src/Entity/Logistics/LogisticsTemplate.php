<?php

declare(strict_types=1);

namespace App\Entity\Logistics;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Api\Filter\LogisticsTemplateInputsLengthOrderFilter;
use App\Api\Filter\LogisticsTemplateTransportTypeNameOrderFilter;
use App\Entity\Product\Input;
use App\Entity\Quirk\HasLatLng;
use App\Entity\TransportType\TransportType;
use App\Repository\Logistics\LogisticsTemplateRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Patch(),
        new Delete(),
    ],
    normalizationContext: [
        'groups' => [
            self::LOGISTICS_TEMPLATE_READ,
        ],
    ],
    denormalizationContext: [
        'groups' => [
            self::LOGISTICS_TEMPLATE_WRITE,
        ],
    ],
    openapiContext: [
        'parameters' => [
            [
                'name' => 'itemsPerPage',
                'in' => 'query',
                'required' => false,
                'schema' => [
                    'type' => 'integer',
                    'default' => 20,
                    'minimum' => 1,
                ],
                'description' => 'Number of items per page.',
            ],
        ],
    ],
    order: ['createdAt' => 'DESC'],
    paginationItemsPerPage: 20,
)]
#[ApiFilter(OrderFilter::class, properties: ['name'])]
#[ApiFilter(LogisticsTemplateInputsLengthOrderFilter::class, properties: ['order[numberOfInputs]'])]
#[ApiFilter(LogisticsTemplateTransportTypeNameOrderFilter::class, properties: ['order[typeOfTransport]'])]
#[ORM\Entity(repositoryClass: LogisticsTemplateRepository::class)]
#[ApiFilter(SearchFilter::class, properties: ['name' => 'partial'])]
class LogisticsTemplate
{
    use HasLatLng;

    public const LOGISTICS_TEMPLATE_READ = 'logistics-template-read';
    public const LOGISTICS_TEMPLATE_WRITE = 'logistics-template-write';

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups([
        self::LOGISTICS_TEMPLATE_READ,
    ])]
    private Uuid $id;

    #[ORM\Column(type: Types::STRING, options: ['default' => ''])]
    #[Assert\NotBlank]
    #[Groups([
        self::LOGISTICS_TEMPLATE_READ,
        self::LOGISTICS_TEMPLATE_WRITE,
    ])]
    private string $name = '';

    #[ORM\Column(type: Types::TEXT, options: ['default' => ''])]
    #[Groups([
        self::LOGISTICS_TEMPLATE_READ,
        self::LOGISTICS_TEMPLATE_WRITE,
    ])]
    private string $description = '';

    /**
     * @var Collection<int, Input>
     */
    #[ORM\OneToMany(targetEntity: Input::class, mappedBy: 'logisticsTemplate', cascade: ['persist'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    #[Groups([
        self::LOGISTICS_TEMPLATE_WRITE,
    ])]
    private Collection $inputs;

    #[ORM\ManyToOne(targetEntity: TransportType::class, cascade: ['persist'], inversedBy: 'logisticsTemplates')]
    #[Groups([
        self::LOGISTICS_TEMPLATE_READ,
        self::LOGISTICS_TEMPLATE_WRITE,
    ])]
    private ?TransportType $typeOfTransport = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['default' => null])]
    #[Groups([
        self::LOGISTICS_TEMPLATE_READ,
    ])]
    private ?DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->inputs = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return Collection<int, Input>
     */
    public function getInputs(): Collection
    {
        return $this->inputs;
    }

    /**
     * @return array<int|string, array{id: string, type: string, name: string, sort: int}>
     */
    #[Groups([self::LOGISTICS_TEMPLATE_READ])]
    public function getInputDetails(): array
    {
        return $this->inputs->map(function (Input $input) {
            return [
                'id' => '/api/inputs/' . $input->getId(),
                'type' => $input->getType(),
                'name' => $input->getName(),
                'sort' => $input->getSort(),
                'createdAt' => $input->getCreatedAt(),
            ];
        })->toArray();
    }

    public function addInput(Input $input): self
    {
        if (!$this->inputs->contains($input)) {
            $this->inputs[] = $input;
            $input->setLogisticsTemplate($this);
        }

        return $this;
    }

    public function removeInput(Input $input): self
    {
        if ($this->inputs->removeElement($input)) {
            if ($input->getLogisticsTemplate() === $this) {
                $input->setLogisticsTemplate(null);
            }
        }

        return $this;
    }

    public function getTypeOfTransport(): ?TransportType
    {
        return $this->typeOfTransport;
    }

    public function setTypeOfTransport(?TransportType $typeOfTransport): self
    {
        $this->typeOfTransport = $typeOfTransport;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}

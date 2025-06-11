<?php

declare(strict_types=1);

namespace App\Entity\Product;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\DataProviders\InputsByStepProvider;
use App\Entity\Logistics\LogisticsTemplate;
use App\Entity\Step\Step;
use App\Entity\SupplyChain\Node;
use App\Repository\Product\InputRepository;
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
        new GetCollection(
            uriTemplate: '/inputs/{id}/steps',
            provider: InputsByStepProvider::class,
            normalizationContext: ['groups' => [self::INPUT_READ]],
            name: 'inputs_by_step'
        ),
        new Post(),
        new Patch(),
        new Delete(),
    ],
    normalizationContext: [
        'groups' => [
            self::INPUT_READ,
            InputCategory::INPUT_INPUT_CATEGORY_READ,
            Step::STEP_INPUT_CATEGORY_READ,
        ],
    ],
    denormalizationContext: ['groups' => [self::INPUT_WRITE]],
    order: ['createdAt' => 'DESC'],
)]
#[ORM\Entity(repositoryClass: InputRepository::class)]
class Input
{
    public const INPUT_READ = 'input-read';
    public const INPUT_WRITE = 'input-write';
    public const STEP_INPUT_READ = 'step-input-read';
    public const SUPPLY_CHAIN_TEMPLATE_INPUT_READ = 'supply-chain-template-input-read';

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups([
        self::INPUT_READ,
        self::STEP_INPUT_READ,
        Node::NODE_STEPS_READ,
        self::SUPPLY_CHAIN_TEMPLATE_INPUT_READ,
    ])]
    private Uuid $id;

    #[ORM\Column(type: Types::STRING, options: ['default' => ''])]
    #[Groups([
        self::INPUT_READ,
        self::STEP_INPUT_READ,
        self::INPUT_WRITE,
        Node::NODE_STEPS_READ,
        self::SUPPLY_CHAIN_TEMPLATE_INPUT_READ,
        LogisticsTemplate::LOGISTICS_TEMPLATE_READ,
        LogisticsTemplate::LOGISTICS_TEMPLATE_WRITE,
        Step::STEP_READ,
    ])]
    #[Assert\NotBlank]
    private string $type = '';

    #[ORM\Column(type: Types::STRING, options: ['default' => ''])]
    #[Groups([
        self::INPUT_READ,
        self::STEP_INPUT_READ,
        self::INPUT_WRITE,
        Node::NODE_STEPS_READ,
        self::SUPPLY_CHAIN_TEMPLATE_INPUT_READ,
        LogisticsTemplate::LOGISTICS_TEMPLATE_READ,
        LogisticsTemplate::LOGISTICS_TEMPLATE_WRITE,
        Step::STEP_READ,
    ])]
    #[Assert\NotBlank]
    private string $name = '';

    /**
     * @var array<string> $options
     */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['default' => null])]
    #[Groups([
        self::INPUT_READ,
        self::STEP_INPUT_READ,
        self::INPUT_WRITE,
        Node::NODE_STEPS_READ,
        self::SUPPLY_CHAIN_TEMPLATE_INPUT_READ,
        LogisticsTemplate::LOGISTICS_TEMPLATE_READ,
        LogisticsTemplate::LOGISTICS_TEMPLATE_WRITE,
        Step::STEP_READ,
    ])]
    private ?array $options = null;

    #[ORM\ManyToOne(targetEntity: Step::class, inversedBy: 'inputs')]
    #[ORM\JoinColumn(nullable: true, options: [
        'default' => null,
    ])]
    #[Groups([
        self::INPUT_READ,
        self::INPUT_WRITE,
    ])]
    private ?Step $step = null;

    #[ORM\ManyToOne(targetEntity: LogisticsTemplate::class, inversedBy: 'inputs')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE', options: [
        'default' => null,
    ])]
    #[Groups([
        self::INPUT_READ,
        self::INPUT_WRITE,
        LogisticsTemplate::LOGISTICS_TEMPLATE_READ,
        LogisticsTemplate::LOGISTICS_TEMPLATE_WRITE,
    ])]
    private ?LogisticsTemplate $logisticsTemplate = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['default' => null])]
    #[Groups([
        self::INPUT_READ,
    ])]
    private ?DateTimeImmutable $createdAt;

    /**
     * @var Collection<int, InputCategory>
     */
    #[ORM\ManyToMany(targetEntity: InputCategory::class, inversedBy: 'inputs')]
    #[ORM\JoinTable(name: 'input_category_input')]
    #[Groups([
        self::INPUT_READ,
        self::INPUT_WRITE,
        self::SUPPLY_CHAIN_TEMPLATE_INPUT_READ,
        Step::STEP_READ,
    ])]
    private Collection $inputCategories;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    #[Groups([
        self::INPUT_READ,
        self::STEP_INPUT_READ,
        self::INPUT_WRITE,
        LogisticsTemplate::LOGISTICS_TEMPLATE_READ,
        LogisticsTemplate::LOGISTICS_TEMPLATE_WRITE,
    ])]
    private int $sort = 0;

    #[ORM\Column(type: Types::STRING, length: 255, options: ['default' => ''])]
    #[Groups([
        self::INPUT_READ,
        self::STEP_INPUT_READ,
        self::INPUT_WRITE,
        self::SUPPLY_CHAIN_TEMPLATE_INPUT_READ,
        Step::STEP_READ,
        LogisticsTemplate::LOGISTICS_TEMPLATE_READ,
        LogisticsTemplate::LOGISTICS_TEMPLATE_WRITE,
        Node::NODE_STEPS_READ,
    ])]
    private string $measurementType = '';

    #[ORM\Column(type: Types::STRING, length: 255, options: ['default' => ''])]
    #[Groups([
        self::INPUT_READ,
        self::STEP_INPUT_READ,
        self::INPUT_WRITE,
        self::SUPPLY_CHAIN_TEMPLATE_INPUT_READ,
        Step::STEP_READ,
        LogisticsTemplate::LOGISTICS_TEMPLATE_READ,
        LogisticsTemplate::LOGISTICS_TEMPLATE_WRITE,
        Node::NODE_STEPS_READ,
    ])]
    private string $unitMeasurement = '';

    #[ORM\Column(type: Types::STRING, length: 255, options: ['default' => ''])]
    #[Groups([
        self::INPUT_READ,
        self::STEP_INPUT_READ,
        self::INPUT_WRITE,
        self::SUPPLY_CHAIN_TEMPLATE_INPUT_READ,
        Step::STEP_READ,
        LogisticsTemplate::LOGISTICS_TEMPLATE_READ,
        LogisticsTemplate::LOGISTICS_TEMPLATE_WRITE,
        Node::NODE_STEPS_READ,
    ])]
    private string $unitSymbol = '';

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    #[Groups([
        self::INPUT_READ,
        self::INPUT_WRITE,
    ])]
    private ?bool $automaticCalculation = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    #[Groups([
        self::INPUT_READ,
        self::STEP_INPUT_READ,
        self::INPUT_WRITE,
        self::SUPPLY_CHAIN_TEMPLATE_INPUT_READ,
        Step::STEP_READ,
        LogisticsTemplate::LOGISTICS_TEMPLATE_READ,
        LogisticsTemplate::LOGISTICS_TEMPLATE_WRITE,
        Node::NODE_STEPS_READ,
    ])]
    private bool $updatable = false;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->inputCategories = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): void
    {
        $this->id = $id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return array<string>|null
     */
    public function getOptions(): ?array
    {
        return $this->options;
    }

    /**
     * @param array<string>|null $options
     */
    public function setOptions(?array $options): void
    {
        $this->options = $options;
    }

    public function getStep(): ?Step
    {
        return $this->step;
    }

    public function setStep(?Step $step): void
    {
        $this->step = $step;
    }

    public function getSort(): int
    {
        return $this->sort;
    }

    public function setSort(int $sort): self
    {
        $this->sort = $sort;
        return $this;
    }

    /**
     * @return Collection<int, InputCategory>
     */
    public function getInputCategories(): Collection
    {
        return $this->inputCategories;
    }

    /**
     * @return $this
     */
    public function addInputCategory(InputCategory $inputCategory): self
    {
        if (!$this->inputCategories->contains($inputCategory)) {
            $this->inputCategories->add($inputCategory);
        }

        return $this;
    }


    public function getLogisticsTemplate(): ?LogisticsTemplate
    {
        return $this->logisticsTemplate;
    }

    public function setLogisticsTemplate(?LogisticsTemplate $logisticsTemplate): self
    {
        $this->logisticsTemplate = $logisticsTemplate;

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

    /**
     * @return $this
     */
    public function removeInputCategory(InputCategory $inputCategory): self
    {
        if ($this->inputCategories->contains($inputCategory)) {
            $this->inputCategories->removeElement($inputCategory);
        }

        return $this;
    }

    public function getMeasurementType(): string
    {
        return $this->measurementType;
    }

    public function setMeasurementType(string $measurementType): self
    {
        $this->measurementType = $measurementType;

        return $this;
    }

    public function getUnitSymbol(): string
    {
        return $this->unitSymbol;
    }

    public function setUnitSymbol(string $unitSymbol): self
    {
        $this->unitSymbol = $unitSymbol;

        return $this;
    }

    public function getUnitMeasurement(): string
    {
        return $this->unitMeasurement;
    }

    public function setUnitMeasurement(string $unitMeasurement): self
    {
        $this->unitMeasurement = $unitMeasurement;

        return $this;
    }

    public function getAutomaticCalculation(): ?bool
    {
        return $this->automaticCalculation;
    }

    public function setAutomaticCalculation(?bool $automaticCalculation): self
    {
        $this->automaticCalculation = $automaticCalculation;

        return $this;
    }

    public function getUpdatable(): bool
    {
        return $this->updatable;
    }

    public function setUpdatable(bool $updatable): self
    {
        $this->updatable = $updatable;

        return $this;
    }
}

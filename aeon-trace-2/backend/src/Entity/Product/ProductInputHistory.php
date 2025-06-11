<?php

declare(strict_types=1);

namespace App\Entity\Product;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Api\Filter\UidFilter;
use App\Entity\User;
use App\Repository\Product\ProductInputHistoryRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
    ],
    normalizationContext: [
        'groups' => [
            self::PRODUCT_INPUT_HISTORY_READ,
        ],
    ],
)]
#[ApiFilter(filterClass: UidFilter::class, properties: ['productInput.id' => 'exact'])]
#[ORM\Entity(repositoryClass: ProductInputHistoryRepository::class)]
class ProductInputHistory
{
    public const PRODUCT_INPUT_HISTORY_READ = 'product-input-history-read';

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups([self::PRODUCT_INPUT_HISTORY_READ])]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: ProductInput::class, inversedBy: 'history')]
    #[Groups([self::PRODUCT_INPUT_HISTORY_READ])]
    private ProductInput $productInput;

    #[ORM\Column(type: Types::STRING, nullable: true, options: ['default' => null])]
    #[Groups([self::PRODUCT_INPUT_HISTORY_READ])]
    private ?string $name = null;

    #[ORM\Column(type: Types::STRING, nullable: true, options: ['default' => null])]
    #[Groups([self::PRODUCT_INPUT_HISTORY_READ])]
    private ?string $type = null;

    #[ORM\Column(type: Types::STRING, nullable: true, options: ['default' => null])]
    #[Groups([self::PRODUCT_INPUT_HISTORY_READ])]
    private ?string $textValue = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true, options: ['default' => null])]
    #[Groups([self::PRODUCT_INPUT_HISTORY_READ])]
    private ?float $numericalValue = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups([self::PRODUCT_INPUT_HISTORY_READ])]
    private ?DateTime $dateTimeFrom = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups([self::PRODUCT_INPUT_HISTORY_READ])]
    private ?DateTime $dateTimeTo = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true, options: ['default' => null])]
    #[Groups([self::PRODUCT_INPUT_HISTORY_READ])]
    private ?float $latitudeValue = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true, options: ['default' => null])]
    #[Groups([self::PRODUCT_INPUT_HISTORY_READ])]
    private ?float $longitudeValue = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['default' => null])]
    #[Groups([self::PRODUCT_INPUT_HISTORY_READ])]
    private ?string $textAreaValue = null;

    #[ORM\Column(type: Types::STRING, nullable: true, options: ['default' => null])]
    #[Groups([self::PRODUCT_INPUT_HISTORY_READ])]
    private ?string $document = null;

    #[ORM\Column(type: Types::STRING, nullable: true, options: ['default' => null])]
    #[Groups([self::PRODUCT_INPUT_HISTORY_READ])]
    private ?string $image = null;

    #[ORM\Column(type: Types::STRING, nullable: true, options: ['default' => null])]
    #[Groups([self::PRODUCT_INPUT_HISTORY_READ])]
    private ?string $radioValue = null;

    /**
     * @var array<string> $checkboxValue
     */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['default' => null])]
    #[Groups([self::PRODUCT_INPUT_HISTORY_READ])]
    private ?array $checkboxValue = null;

    /**
     * @var Collection<int, ProductInputImage>
     */
    #[ORM\ManyToMany(targetEntity: ProductInputImage::class, inversedBy: 'inputHistory', cascade: ['persist'], orphanRemoval: true)]
    #[Groups([self::PRODUCT_INPUT_HISTORY_READ])]
    private Collection $images;

    #[ORM\Column(type: Types::STRING, nullable: true, options: ['default' => null])]
    #[Groups([self::PRODUCT_INPUT_HISTORY_READ])]
    private ?string $unitMeasurement = null;

    #[ORM\Column(type: Types::STRING, nullable: true, options: ['default' => null])]
    #[Groups([self::PRODUCT_INPUT_HISTORY_READ])]
    private ?string $measurementType = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true, options: ['default' => null])]
    #[Groups([self::PRODUCT_INPUT_HISTORY_READ])]
    private ?float $measurementValue = null;

    #[ORM\Column(type: Types::STRING, length: 255, options: ['default' => ''])]
    #[Groups([self::PRODUCT_INPUT_HISTORY_READ])]
    private string $unitSymbol = '';

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    #[Groups([self::PRODUCT_INPUT_HISTORY_READ])]
    private ?bool $automaticCalculation = false;

    #[ORM\Column(type: Types::BOOLEAN, options: [
        'default' => false,
    ])]
    #[Groups([self::PRODUCT_INPUT_HISTORY_READ])]
    private bool $locked = false;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['default' => null])]
    #[Groups([self::PRODUCT_INPUT_HISTORY_READ])]
    private ?DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[Groups([self::PRODUCT_INPUT_HISTORY_READ])]
    private User $updatedBy;

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['default' => null])]
    #[Groups([self::PRODUCT_INPUT_HISTORY_READ])]
    private ?int $version = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['default' => null])]
    #[Groups([self::PRODUCT_INPUT_HISTORY_READ])]
    private ?DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->images = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): void
    {
        $this->id = $id;
    }

    public function getProductInput(): ProductInput
    {
        return $this->productInput;
    }

    public function setProductInput(ProductInput $productInput): void
    {
        $this->productInput = $productInput;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getTextValue(): ?string
    {
        return $this->textValue;
    }

    public function setTextValue(?string $textValue): void
    {
        $this->textValue = $textValue;
    }

    public function getNumericalValue(): ?float
    {
        return $this->numericalValue;
    }

    public function setNumericalValue(?float $numericalValue): void
    {
        $this->numericalValue = $numericalValue;
    }

    public function getDateTimeFrom(): ?DateTime
    {
        return $this->dateTimeFrom;
    }

    public function setDateTimeFrom(?DateTime $dateTimeFrom): void
    {
        $this->dateTimeFrom = $dateTimeFrom;
    }

    public function getDateTimeTo(): ?DateTime
    {
        return $this->dateTimeTo;
    }

    public function setDateTimeTo(?DateTime $dateTimeTo): void
    {
        $this->dateTimeTo = $dateTimeTo;
    }

    public function getLatitudeValue(): ?float
    {
        return $this->latitudeValue;
    }

    public function setLatitudeValue(?float $latitudeValue): void
    {
        $this->latitudeValue = $latitudeValue;
    }

    public function getLongitudeValue(): ?float
    {
        return $this->longitudeValue;
    }

    public function setLongitudeValue(?float $longitudeValue): void
    {
        $this->longitudeValue = $longitudeValue;
    }

    public function getTextAreaValue(): ?string
    {
        return $this->textAreaValue;
    }

    public function setTextAreaValue(?string $textAreaValue): void
    {
        $this->textAreaValue = $textAreaValue;
    }

    public function getDocument(): ?string
    {
        return $this->document;
    }

    public function setDocument(?string $document): void
    {
        $this->document = $document;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): void
    {
        $this->image = $image;
    }

    public function getRadioValue(): ?string
    {
        return $this->radioValue;
    }

    public function setRadioValue(?string $radioValue): void
    {
        $this->radioValue = $radioValue;
    }

    /**
     * @return array<string>
     */
    public function getCheckboxValue(): ?array
    {
        return $this->checkboxValue;
    }

    /**
     * @param array<string> $checkboxValue
     */
    public function setCheckboxValue(?array $checkboxValue): void
    {
        $this->checkboxValue = $checkboxValue;
    }

    /**
     * @return Collection<int, ProductInputImage>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(ProductInputImage $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);

            $image->addInputHistory($this);
        }

        return $this;
    }

    public function removeImage(ProductInputImage $image): self
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
        }

        return $this;
    }

    public function getUnitMeasurement(): ?string
    {
        return $this->unitMeasurement;
    }

    public function setUnitMeasurement(?string $unitMeasurement): void
    {
        $this->unitMeasurement = $unitMeasurement;
    }

    public function getMeasurementType(): ?string
    {
        return $this->measurementType;
    }

    public function setMeasurementType(?string $measurementType): void
    {
        $this->measurementType = $measurementType;
    }

    public function getMeasurementValue(): ?float
    {
        return $this->measurementValue;
    }

    public function setMeasurementValue(?float $measurementValue): void
    {
        $this->measurementValue = $measurementValue;
    }

    public function getUnitSymbol(): string
    {
        return $this->unitSymbol;
    }

    public function setUnitSymbol(string $unitSymbol): void
    {
        $this->unitSymbol = $unitSymbol;
    }

    public function getAutomaticCalculation(): ?bool
    {
        return $this->automaticCalculation;
    }

    public function setAutomaticCalculation(?bool $automaticCalculation): void
    {
        $this->automaticCalculation = $automaticCalculation;
    }

    public function isLocked(): bool
    {
        return $this->locked;
    }

    public function setLocked(bool $locked): void
    {
        $this->locked = $locked;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getUpdatedBy(): User
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(User $updatedBy): void
    {
        $this->updatedBy = $updatedBy;
    }

    public function getVersion(): ?int
    {
        return $this->version;
    }

    public function setVersion(?int $version): void
    {
        $this->version = $version;
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

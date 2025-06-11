<?php

declare(strict_types=1);

namespace App\Entity\Product;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Api\Filter\UidFilter;
use App\Entity\Dpp\Dpp;
use App\Entity\Logistics\Logistics;
use App\Entity\Step\ProductStep;
use App\Entity\User;
use App\Repository\Product\ProductInputRepository;
use App\StateProcessors\DppDocumentUploadProcessor;
use App\StateProcessors\FileDeleteProcessor;
use App\StateProcessors\ImageUploadProcessor;
use DateTime;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Post(
            uriTemplate: '/product_inputs/{id}/input_image',
            inputFormats: ['multipart' => ['multipart/form-data']],
            denormalizationContext: ['groups' => [self::PRODUCT_INPUT_IMAGE_UPLOAD]],
            processor: ImageUploadProcessor::class
        ),
        new Post(
            uriTemplate: '/product_inputs/{id}/input_document',
            inputFormats: ['multipart' => ['multipart/form-data']],
            denormalizationContext: ['groups' => [self::PRODUCT_INPUT_FILE_UPLOAD]],
            processor: DppDocumentUploadProcessor::class
        ),
        new Patch(),
        new Delete(
            uriTemplate: '/product_inputs/{id}/input_image',
            processor: FileDeleteProcessor::class,
        ),
        new Delete(
            uriTemplate: '/product_inputs/{id}/input_document',
            processor: FileDeleteProcessor::class,
        ),
    ],
    normalizationContext: ['groups' => [
        self::PRODUCT_INPUT_READ,
        ProductInputImage::DPP_PRODUCT_INPUT_IMAGE_READ,
    ]],
    denormalizationContext: ['groups' => [self::PRODUCT_INPUT_WRITE]]
)]
#[ApiFilter(filterClass: UidFilter::class, properties: ['productStep.id' => 'exact'])]
#[ApiFilter(filterClass: BooleanFilter::class, properties: ['updatable'])]
#[ORM\Entity(repositoryClass: ProductInputRepository::class)]
#[Vich\Uploadable]
class ProductInput
{
    public const PRODUCT_INPUT_READ = 'product-input-read';
    public const PRODUCT_INPUT_WRITE = 'product-input-write';
    public const DPP_PRODUCT_INPUT_READ = 'dpp-product-input-read';
    public const LOGISTICS_INPUT_READ = 'logistics-input-read';
    public const PRODUCT_INPUT_IMAGE_UPLOAD = 'product-input-image-upload';
    public const PRODUCT_INPUT_FILE_UPLOAD = 'product-input-file-upload';


    #[Vich\UploadableField(mapping: 'product_input_images', fileNameProperty: 'image')]
    #[Groups([self::PRODUCT_INPUT_IMAGE_UPLOAD])]
    public ?File $file = null;

    #[Vich\UploadableField(mapping: 'product_input_documents', fileNameProperty: 'document')]
    #[Groups([self::PRODUCT_INPUT_FILE_UPLOAD])]
    public ?File $dppDocument = null;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups([
        self::PRODUCT_INPUT_READ,
        self::DPP_PRODUCT_INPUT_READ,
        self::LOGISTICS_INPUT_READ,
    ])]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: ProductStep::class, inversedBy: 'productInputs')]
    #[Groups([
        self::PRODUCT_INPUT_READ,
        self::PRODUCT_INPUT_WRITE,
    ])]
    private ProductStep $productStep;

    #[ORM\Column(type: Types::STRING, nullable: true, options: ['default' => null])]
    #[Groups([
        self::PRODUCT_INPUT_READ,
        self::PRODUCT_INPUT_WRITE,
        self::DPP_PRODUCT_INPUT_READ,
        self::LOGISTICS_INPUT_READ,
    ])]
    private ?string $textValue = null;

    #[ORM\Column(type: Types::STRING, nullable: true, options: ['default' => null])]
    #[Groups([
        self::PRODUCT_INPUT_READ,
        self::PRODUCT_INPUT_WRITE,
        self::DPP_PRODUCT_INPUT_READ,
        self::LOGISTICS_INPUT_READ,
    ])]
    private ?string $name = null;

    #[ORM\Column(type: Types::STRING, nullable: true, options: ['default' => null])]
    #[Groups([
        self::PRODUCT_INPUT_READ,
        self::PRODUCT_INPUT_WRITE,
        self::DPP_PRODUCT_INPUT_READ,
        self::LOGISTICS_INPUT_READ,
    ])]
    private ?string $type = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true, options: ['default' => 0])]
    #[Groups([
        self::PRODUCT_INPUT_READ,
        self::PRODUCT_INPUT_WRITE,
        self::DPP_PRODUCT_INPUT_READ,
        self::LOGISTICS_INPUT_READ,
    ])]
    private float $numericalValue = 0;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups([
        self::PRODUCT_INPUT_READ,
        self::PRODUCT_INPUT_WRITE,
        self::DPP_PRODUCT_INPUT_READ,
        self::LOGISTICS_INPUT_READ,
    ])]
    private ?DateTime $dateTimeTo = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups([
        self::PRODUCT_INPUT_READ,
        self::PRODUCT_INPUT_WRITE,
        self::DPP_PRODUCT_INPUT_READ,
        self::LOGISTICS_INPUT_READ,
    ])]
    private ?DateTime $dateTimeFrom = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true, options: ['default' => 0])]
    #[Groups([
        self::PRODUCT_INPUT_READ,
        self::PRODUCT_INPUT_WRITE,
        self::DPP_PRODUCT_INPUT_READ,
        self::LOGISTICS_INPUT_READ,
    ])]
    private float $latitudeValue = 0;

    #[ORM\Column(type: Types::FLOAT, nullable: true, options: ['default' => 0])]
    #[Groups([
        self::PRODUCT_INPUT_READ,
        self::PRODUCT_INPUT_WRITE,
        self::DPP_PRODUCT_INPUT_READ,
        self::LOGISTICS_INPUT_READ,
    ])]
    private float $longitudeValue = 0;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['default' => null])]
    #[Groups([
        self::PRODUCT_INPUT_READ,
        self::PRODUCT_INPUT_WRITE,
        self::DPP_PRODUCT_INPUT_READ,
        self::LOGISTICS_INPUT_READ,
    ])]
    private ?string $textAreaValue = null;

    #[ORM\Column(type: Types::STRING, nullable: true, options: ['default' => null])]
    #[Groups([
        self::PRODUCT_INPUT_READ,
        self::DPP_PRODUCT_INPUT_READ,
        self::LOGISTICS_INPUT_READ,
    ])]
    private ?string $document = null;

    #[ORM\Column(type: Types::STRING, nullable: true, options: ['default' => null])]
    #[Groups([
        self::PRODUCT_INPUT_READ,
        self::DPP_PRODUCT_INPUT_READ,
        self::LOGISTICS_INPUT_READ,
    ])]
    private ?string $image = null;

    #[ORM\Column(type: Types::STRING, nullable: true, options: ['default' => null])]
    #[Groups([
        self::PRODUCT_INPUT_READ,
        self::PRODUCT_INPUT_WRITE,
        self::DPP_PRODUCT_INPUT_READ,
        self::LOGISTICS_INPUT_READ,
    ])]
    private ?string $radioValue = null;

    /**
     * @var array<string> $checkboxValue
     */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['default' => null])]
    #[Groups([
        self::PRODUCT_INPUT_READ,
        self::PRODUCT_INPUT_WRITE,
        self::DPP_PRODUCT_INPUT_READ,
        self::LOGISTICS_INPUT_READ,
    ])]
    private ?array $checkboxValue = null;

    /**
     * @var Collection<int, ProductInputImage>
     */
    #[ORM\OneToMany(targetEntity: ProductInputImage::class, mappedBy: 'input', cascade: ['persist'])]
    #[Groups([
        self::PRODUCT_INPUT_READ,
        self::PRODUCT_INPUT_WRITE,
        self::DPP_PRODUCT_INPUT_READ,
        self::LOGISTICS_INPUT_READ,
    ])]
    private Collection $images;

    #[ORM\ManyToOne(targetEntity: Dpp::class, inversedBy: 'productInputs')]
    #[Groups([
        self::PRODUCT_INPUT_READ,
        self::PRODUCT_INPUT_WRITE,
    ])]
    private ?Dpp $dpp = null;

    #[ORM\ManyToOne(targetEntity: Logistics::class, inversedBy: 'productInputs')]
    #[Groups([
        self::PRODUCT_INPUT_READ,
        self::PRODUCT_INPUT_WRITE,
    ])]
    private ?Logistics $logistics = null;

    /**
     * @var Collection<int, InputCategory>
     */
    #[ORM\ManyToMany(targetEntity: InputCategory::class, inversedBy: 'productInputs', cascade: ['persist'])]
    #[ORM\JoinTable(name: 'product_input_category')]
    #[Groups([
        self::PRODUCT_INPUT_READ,
        self::PRODUCT_INPUT_WRITE,
    ])]
    private Collection $inputCategories;

    #[ORM\Column(type: Types::STRING, nullable: true, options: ['default' => ''])]
    #[Groups([
        self::PRODUCT_INPUT_READ,
        self::PRODUCT_INPUT_WRITE,
        self::DPP_PRODUCT_INPUT_READ,
        self::LOGISTICS_INPUT_READ,
    ])]
    private ?string $unitMeasurement = '';

    #[ORM\Column(type: Types::STRING, nullable: true, options: ['default' => ''])]
    #[Groups([
        self::PRODUCT_INPUT_READ,
        self::PRODUCT_INPUT_WRITE,
        self::DPP_PRODUCT_INPUT_READ,
        self::LOGISTICS_INPUT_READ,
    ])]
    private ?string $measurementType = '';

    #[ORM\Column(type: Types::FLOAT, nullable: true, options: ['default' => 0])]
    #[Groups([
        self::PRODUCT_INPUT_READ,
        self::PRODUCT_INPUT_WRITE,
        self::DPP_PRODUCT_INPUT_READ,
        self::LOGISTICS_INPUT_READ,
    ])]
    private ?float $measurementValue = 0;

    #[ORM\Column(type: Types::STRING, length: 255, options: ['default' => ''])]
    #[Groups([
        self::PRODUCT_INPUT_READ,
        self::PRODUCT_INPUT_WRITE,
        self::DPP_PRODUCT_INPUT_READ,
        self::LOGISTICS_INPUT_READ,
    ])]
    private string $unitSymbol = '';

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    #[Groups([
        self::PRODUCT_INPUT_READ,
        self::PRODUCT_INPUT_WRITE,
    ])]
    private ?bool $automaticCalculation = false;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['default' => null])]
    #[Groups([
        self::PRODUCT_INPUT_READ,
    ])]
    private ?DateTimeImmutable $createdAt;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups([
        self::PRODUCT_INPUT_READ,
        self::DPP_PRODUCT_INPUT_READ,
    ])]
    private ?User $createdBy = null;

    #[ORM\Column(type: Types::BOOLEAN, options: [
        'default' => false,
    ])]
    #[Groups([
        self::PRODUCT_INPUT_READ,
        self::PRODUCT_INPUT_WRITE,
        self::DPP_PRODUCT_INPUT_READ,
    ])]
    private bool $locked = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    #[Groups([
        self::PRODUCT_INPUT_READ,
        self::PRODUCT_INPUT_WRITE,
        self::DPP_PRODUCT_INPUT_READ,
    ])]
    private bool $updatable = false;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['default' => null])]
    #[Groups([
        self::PRODUCT_INPUT_READ,
        self::DPP_PRODUCT_INPUT_READ,
        self::PRODUCT_INPUT_WRITE,
    ])]
    private ?DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups([
        self::PRODUCT_INPUT_READ,
        self::DPP_PRODUCT_INPUT_READ,
    ])]
    private ?User $updatedBy = null;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 1])]
    #[Groups([
        self::PRODUCT_INPUT_READ,
        self::DPP_PRODUCT_INPUT_READ,
    ])]
    private int $version = 1;

    /**
     * @var Collection<int, ProductInputHistory>
     */
    #[ORM\OneToMany(targetEntity: ProductInputHistory::class, mappedBy: 'productInput', cascade: ['persist'], orphanRemoval: true)]
    #[Groups([
        self::PRODUCT_INPUT_READ,
        self::DPP_PRODUCT_INPUT_READ,
    ])]
    private Collection $history;

    #[ORM\Column(type: Types::BOOLEAN, options: [
        'default' => false,
    ])]
    #[Groups([
        self::PRODUCT_INPUT_READ,
        self::PRODUCT_INPUT_WRITE,
        self::DPP_PRODUCT_INPUT_READ,
    ])]

    private bool $additional = false;

    #[ORM\Column(type: Types::STRING, nullable: true, options: ['default' => null])]
    #[Groups([
        self::PRODUCT_INPUT_READ,
        self::PRODUCT_INPUT_WRITE,
        self::DPP_PRODUCT_INPUT_READ,
    ])]
    private ?string $inputReference = null;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->images = new ArrayCollection();
        $this->inputCategories = new ArrayCollection();
        $this->history = new ArrayCollection();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(File $file): void
    {
        $this->file = $file;
    }

    public function getDppDocument(): ?File
    {
        return $this->dppDocument;
    }

    public function setDppDocument(File $dppDocument): void
    {
        $this->dppDocument = $dppDocument;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): void
    {
        $this->id = $id;
    }

    public function getProductStep(): ProductStep
    {
        return $this->productStep;
    }

    public function setProductStep(ProductStep $productStep): void
    {
        $this->productStep = $productStep;
    }

    public function getTextValue(): ?string
    {
        return $this->textValue;
    }

    public function setTextValue(?string $textValue): void
    {
        $this->textValue = $textValue;
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

    public function getTextAreaValue(): ?string
    {
        return $this->textAreaValue;
    }

    public function setTextAreaValue(?string $textAreaValue): void
    {
        $this->textAreaValue = $textAreaValue;
    }

    public function getNumericalValue(): float
    {
        return $this->numericalValue;
    }

    public function setNumericalValue(float $numericalValue): void
    {
        $this->numericalValue = $numericalValue;
    }

    public function getDateTimeTo(): ?DateTime
    {
        return $this->dateTimeTo;
    }

    public function setDateTimeTo(?DateTime $dateTimeTo): void
    {
        $this->dateTimeTo = $dateTimeTo;
    }

    public function getDateTimeFrom(): ?DateTime
    {
        return $this->dateTimeFrom;
    }

    public function setDateTimeFrom(?DateTime $dateTimeFrom): void
    {
        $this->dateTimeFrom = $dateTimeFrom;
    }

    public function getLongitudeValue(): float
    {
        return $this->longitudeValue;
    }

    public function setLongitudeValue(float $longitudeValue): void
    {
        $this->longitudeValue = $longitudeValue;
    }

    public function getLatitudeValue(): float
    {
        return $this->latitudeValue;
    }

    public function setLatitudeValue(float $latitudeValue): void
    {
        $this->latitudeValue = $latitudeValue;
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
     * @return array<string>|null
     */
    public function getCheckboxValue(): ?array
    {
        $checkboxValue = [];

        return $this->checkboxValue;
    }

    /**
     * @param array<string>|null $checkboxValue
     */
    public function setCheckboxValue(?array $checkboxValue): void
    {
        $this->checkboxValue = $checkboxValue;
    }

    /** @return Collection<int, ProductInputImage> */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(ProductInputImage $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setInput($this);
        }

        return $this;
    }

    public function removeImage(ProductInputImage $image): self
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);

            if ($image->getInput() === $this) {
                $image->setInput(null);
            }
        }

        return $this;
    }

    public function getDpp(): ?Dpp
    {
        return $this->dpp;
    }

    public function setDpp(?Dpp $dpp): void
    {
        $this->dpp = $dpp;
    }

    public function getLogistics(): ?Logistics
    {
        return $this->logistics;
    }

    public function setLogistics(?Logistics $logistics): void
    {
        $this->logistics = $logistics;
    }

    /** @return Collection<int, InputCategory> */
    public function getInputCategories(): Collection
    {
        return $this->inputCategories;
    }

    public function addInputCategory(InputCategory $inputCategory): self
    {
        if (!$this->inputCategories->contains($inputCategory)) {
            $this->inputCategories->add($inputCategory);
        }

        return $this;
    }

    public function removeInputCategory(InputCategory $inputCategory): self
    {
        if ($this->inputCategories->contains($inputCategory)) {
            $this->inputCategories->removeElement($inputCategory);
        }

        return $this;
    }

    public function getUnitMeasurement(): ?string
    {
        return $this->unitMeasurement;
    }

    public function setUnitMeasurement(?string $unitMeasurement): self
    {
        $this->unitMeasurement = $unitMeasurement;

        return $this;
    }

    public function getMeasurementType(): ?string
    {
        return $this->measurementType;
    }

    public function setMeasurementType(?string $measurementType): self
    {
        $this->measurementType = $measurementType;

        return $this;
    }

    public function getMeasurementValue(): ?float
    {
        return $this->measurementValue;
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

    public function setMeasurementValue(?float $measurementValue): self
    {
        $this->measurementValue = $measurementValue;

        return $this;
    }

    public function isAutomaticCalculation(): ?bool
    {
        return $this->automaticCalculation;
    }

    public function setAutomaticCalculation(?bool $automaticCalculation): self
    {
        $this->automaticCalculation = $automaticCalculation;

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

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): void
    {
        $this->createdBy = $createdBy;
    }

    public function isLocked(): bool
    {
        return $this->locked;
    }

    public function setLocked(bool $locked): void
    {
        $this->locked = $locked;
    }

    public function isUpdatable(): bool
    {
        return $this->updatable;
    }

    public function setUpdatable(bool $updatable): void
    {
        $this->updatable = $updatable;

        if ($updatable) {
            $productStep = $this->getProductStep();
            $dpp = $productStep->getDpp();

            $productStep->setUpdatable(true);
            $dpp?->setUpdatable(true);
        }
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getUpdatedBy(): ?User
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?User $updatedBy): void
    {
        $this->updatedBy = $updatedBy;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function setVersion(int $version): void
    {
        $this->version = $version;
    }

    /**
     * @return Collection<int, ProductInputHistory>
     */
    public function getHistory(): Collection
    {
        return $this->history;
    }

    public function addHistory(ProductInputHistory $inputHistory): self
    {
        if (!$this->history->contains($inputHistory)) {
            $this->history->add($inputHistory);

            $inputHistory->setProductInput($this);
        }

        return $this;
    }

    public function removeHistory(ProductInputHistory $inputHistory): self
    {
        if ($this->history->contains($inputHistory)) {
            $this->history->removeElement($inputHistory);
        }

        return $this;
    }

    public function isAdditional(): bool
    {
        return $this->additional;
    }

    public function setAdditional(bool $additional): void
    {
        $this->additional = $additional;
    }

    public function getInputReference(): ?string
    {
        return $this->inputReference;
    }

    public function setInputReference(?string $inputReference): void
    {
        $this->inputReference = $inputReference;
    }

}

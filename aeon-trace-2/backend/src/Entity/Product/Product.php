<?php

declare(strict_types=1);

namespace App\Entity\Product;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\DataTransferObjects\Product\CreateProductInput;
use App\Entity\Company\CompanySite;
use App\Entity\Dpp\Dpp;
use App\Entity\Embeddable\Address;
use App\Entity\Quirk\HasLatLng;
use App\Entity\Step\ProductStep;
use App\Entity\Step\StepsTemplate;
use App\Repository\Product\ProductRepository;
use App\StateProcessors\CreateProductProcessor;
use App\StateProcessors\EditProductProcessor;
use App\StateProcessors\FileDeleteProcessor;
use App\StateProcessors\ImageUploadProcessor;
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
        new Post(
            uriTemplate: '/products/{id}/product_image',
            inputFormats: ['multipart' => ['multipart/form-data']],
            denormalizationContext: ['groups' => [self::PRODUCT_IMAGE_UPLOAD]],
            processor: ImageUploadProcessor::class,
        ),
        new Post(
            denormalizationContext: [],
            input: CreateProductInput::class,
            processor: CreateProductProcessor::class,
        ),
        new Patch(
            processor: EditProductProcessor::class,
        ),
        new Delete(
            uriTemplate: '/products/{id}/product_image',
            processor: FileDeleteProcessor::class,
        ),
        new Delete(),
    ],
    normalizationContext: [
        'groups' => [
            self::PRODUCT_READ,
            Address::ADDRESS_READ,
            ProductStep::DPP_STEP_READ,
            ProductInput::DPP_PRODUCT_INPUT_READ,
            ProductInputImage::DPP_PRODUCT_INPUT_IMAGE_READ,
            StepsTemplate::STEP_TEMPLATE_READ,
            CompanySite::DPP_COMPANY_SITE_READ,
            Input::STEP_INPUT_READ,
        ],
    ],
    denormalizationContext: [
        'groups' => [
            self::PRODUCT_WRITE,
            Address::ADDRESS_WRITE,
        ],
    ]
)]
#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[Vich\Uploadable]
class Product
{
    use HasLatLng;

    public const PRODUCT_READ = 'product_read';
    public const PRODUCT_WRITE = 'product-write';
    public const DPP_PRODUCT_READ = 'dpp-product-read';
    public const PRODUCT_IMAGE_UPLOAD = 'product-image-upload';

    #[Vich\UploadableField(mapping: 'product_images', fileNameProperty: 'productImage')]
    #[Groups([self::PRODUCT_IMAGE_UPLOAD])]
    public ?File $file = null;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups([self::PRODUCT_READ, self::DPP_PRODUCT_READ])]
    private Uuid $id;

    #[ORM\Column(type: Types::STRING, options: ['default' => ''])]
    #[Groups([
        self::PRODUCT_READ,
        self::DPP_PRODUCT_READ,
        self::PRODUCT_WRITE,
    ])]
    private string $modelId = '';

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups([
        self::PRODUCT_READ,
        self::DPP_PRODUCT_READ,
        self::PRODUCT_WRITE,
    ])]
    private string $name = '';

    #[ORM\Embedded(class: Address::class)]
    #[Groups([
        self::PRODUCT_READ,
        self::PRODUCT_WRITE,
        self::DPP_PRODUCT_READ,
    ])]
    private Address $address;

    #[ORM\ManyToOne(targetEntity: CompanySite::class, inversedBy: 'products')]
    #[Groups([
        self::PRODUCT_READ,
        self::PRODUCT_WRITE,
        self::DPP_PRODUCT_READ,
    ])]
    private ?CompanySite $companySite = null;

    #[ORM\ManyToOne(targetEntity: ProductTemplate::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups([
        self::PRODUCT_READ,
        self::PRODUCT_WRITE,
        self::DPP_PRODUCT_READ,
    ])]
    private ?ProductTemplate $productTemplate = null;

    #[ORM\Column(type: Types::STRING, nullable: true, options: [
        'default' => '',
    ])]
    #[Groups([
        self::PRODUCT_READ,
        self::DPP_PRODUCT_READ,
    ])]
    private ?string $productImage = '';

    #[ORM\Column(type: Types::TEXT, options: ['default' => ''])]
    #[Groups([
        self::PRODUCT_READ,
        self::DPP_PRODUCT_READ,
        self::PRODUCT_WRITE,
    ])]
    private string $description = '';

    /**
     * @var Collection<int, ProductStep>
     */
    #[ORM\OneToMany(targetEntity: ProductStep::class, mappedBy: 'product', cascade: ['persist'], orphanRemoval: true)]
    #[Groups([
        self::PRODUCT_READ,
        self::DPP_PRODUCT_READ,
    ])]
    private Collection $productSteps;

    #[ORM\Column(type: Types::FLOAT, options: [
        'default' => 0,
    ])]
    #[Groups([
        self::PRODUCT_READ,
        self::DPP_PRODUCT_READ,
        self::PRODUCT_WRITE,
    ])]
    private float $latitude = 0;

    #[ORM\Column(type: Types::FLOAT, options: [
        'default' => 0,
    ])]
    #[Groups([
        self::PRODUCT_READ,
        self::DPP_PRODUCT_READ,
        self::PRODUCT_WRITE,
    ])]
    private float $longitude = 0;

    #[ORM\ManyToOne(targetEntity: Dpp::class, inversedBy: 'products')]
    #[Groups([
        self::PRODUCT_READ,
        self::PRODUCT_WRITE,
    ])]
    private Dpp $dpp;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['default' => null])]
    #[Groups([
        self::PRODUCT_READ,
    ])]
    private ?DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->productSteps = new ArrayCollection();
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file): void
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

    public function getModelId(): string
    {
        return $this->modelId;
    }

    public function setModelId(string $modelId): void
    {
        $this->modelId = $modelId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getAddress(): Address
    {
        return $this->address;
    }

    public function setAddress(Address $address): void
    {
        $this->address = $address;
    }

    public function getCompanySite(): ?CompanySite
    {
        return $this->companySite;
    }

    public function setCompanySite(?CompanySite $companySite): void
    {
        $this->companySite = $companySite;
    }

    public function getProductImage(): ?string
    {
        return $this->productImage;
    }

    public function setProductImage(?string $productImage): void
    {
        $this->productImage = $productImage;
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
     * @return Collection<int, ProductStep>
     */
    public function getProductSteps(): Collection
    {
        return $this->productSteps;
    }

    public function addProductStep(ProductStep $input): self
    {
        if (!$this->productSteps->contains($input)) {
            $this->productSteps->add($input);
            $input->setProduct($this);
        }

        return $this;
    }

    public function removeProductStep(ProductStep $input): self
    {
        if ($this->productSteps->contains($input)) {
            $this->productSteps->removeElement($input);
        }

        return $this;
    }

    public function getProductTemplate(): ?ProductTemplate
    {
        return $this->productTemplate;
    }

    public function setProductTemplate(?ProductTemplate $productTemplate): void
    {
        $this->productTemplate = $productTemplate;
    }

    public function getDpp(): Dpp
    {
        return $this->dpp;
    }

    public function setDpp(Dpp $dpp): void
    {
        $this->dpp = $dpp;
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

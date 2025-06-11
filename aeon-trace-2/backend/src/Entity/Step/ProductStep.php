<?php

declare(strict_types=1);

namespace App\Entity\Step;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Api\Filter\UidFilter;
use App\Controller\Step\GetProcessedMaterialsTraceController;
use App\DataProviders\ProductStepGeoCollectionProvider;
use App\DataProviders\QrEntityDataProvider;
use App\DataProviders\TimestampDataProvider;
use App\Entity\Company\Company;
use App\Entity\Company\CompanySite;
use App\Entity\Dpp\Dpp;
use App\Entity\Logistics\Logistics;
use App\Entity\Product\Product;
use App\Entity\Product\ProductInput;
use App\Entity\Product\ProductInputImage;
use App\Entity\Quirk\HasName;
use App\Entity\Quirk\HasQr;
use App\Entity\Quirk\HasUid;
use App\Entity\SupplyChain\Node;
use App\Entity\User;
use App\Repository\Step\ProductStepRepository;
use App\StateProcessors\CreateProductStepProcessor;
use App\StateProcessors\FileDeleteProcessor;
use App\StateProcessors\ImageUploadProcessor;
use App\StateProcessors\UpdateProductStepProcessor;
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
use Symfony\Component\Serializer\Attribute\MaxDepth;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Uid\Uuid;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ApiResource(
    operations: [
        new Get(),
        new Get(
            uriTemplate: 'product_steps/get_processed_materials_trace/{id}',
            controller: GetProcessedMaterialsTraceController::class,
        ),
        new Get(
            uriTemplate: '/product_steps/{id}/confirm_tsa',
            provider: TimestampDataProvider::class,
        ),
        new Get(
            uriTemplate: '/product_steps/{id}/get_geo',
            provider: ProductStepGeoCollectionProvider::class,
        ),
        new Get(
            uriTemplate: '/static/product_steps/getByQrId',
            openapiContext: [
                'parameters' => [
                    [
                        'name' => 'companySlug',
                        'in' => 'query',
                        'description' => 'Company slug',
                        'required' => false,
                        'type' => 'string',
                    ],
                    [
                        'name' => 'qrId',
                        'in' => 'query',
                        'description' => 'QR identificator',
                        'required' => true,
                        'type' => 'integer',
                    ],
                ],
            ],
            shortName: 'Static',
            provider: QrEntityDataProvider::class,
        ),
        new GetCollection(),
        new Post(
            uriTemplate: '/product_steps/{id}/step_image',
            inputFormats: ['multipart' => ['multipart/form-data']],
            denormalizationContext: ['groups' => [self::STEP_IMAGE_UPLOAD]],
            processor: ImageUploadProcessor::class,
        ),
        new Post(
            processor: CreateProductStepProcessor::class,
        ),
        new Patch(
            processor: UpdateProductStepProcessor::class,
        ),
        new Delete(
            uriTemplate: '/product_steps/{id}/step_image',
            processor: FileDeleteProcessor::class,
        ),
        new Delete(),
    ],
    normalizationContext: [
        'groups' => [
            self::STEP_READ,
            Step::STEP_READ,
            ProductInput::DPP_PRODUCT_INPUT_READ,
            ProductInputImage::DPP_PRODUCT_INPUT_IMAGE_READ,
        ],
        AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true,
    ],
    denormalizationContext: [
        'groups' => [
            self::STEP_WRITE,
        ],
    ]
)]
#[ApiFilter(filterClass: UidFilter::class, properties: ['node.id' => 'exact'])]
#[ORM\Entity(repositoryClass: ProductStepRepository::class)]
#[Vich\Uploadable]
class ProductStep
{
    use HasUid;
    use HasName;
    use HasQr;

    public const URL_PATH = 'product_step';
    public const STEP_READ = 'step-read';
    public const STEP_WRITE = 'step-write';
    public const DPP_STEP_READ = 'dpp-step-read';
    public const LOGISTICS_STEP_READ = 'logistics-step-read';
    public const STEP_IMAGE_UPLOAD = 'company-image-upload';

    #[Vich\UploadableField(mapping: 'step_images', fileNameProperty: 'stepImage')]
    #[Groups([self::STEP_IMAGE_UPLOAD])]
    public ?File $file = null;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups([
        self::STEP_READ,
        self::DPP_STEP_READ,
        self::LOGISTICS_STEP_READ,
    ])]
    private Uuid $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups([
        self::STEP_READ,
        self::STEP_WRITE,
        self::DPP_STEP_READ,
        self::LOGISTICS_STEP_READ,
    ])]
    private string $name = '';

    #[ORM\ManyToOne(targetEntity: Step::class, inversedBy: 'inheritors')]
    #[Groups([
        self::STEP_READ,
        self::DPP_STEP_READ,
        self::LOGISTICS_STEP_READ,
    ])]
    #[MaxDepth(1)]
    private Step $stepTemplateReference;

    #[ORM\Column(type: Types::STRING, nullable: true, options: ['default' => ''])]
    #[Groups([
        self::STEP_READ,
        self::DPP_STEP_READ,
        self::LOGISTICS_STEP_READ,
    ])]
    private ?string $stepImage = '';

    #[ORM\ManyToOne(targetEntity: ProductStep::class, inversedBy: 'steps')]
    #[ORM\JoinColumn(nullable: true, options: [
        'default' => null,
    ])]
    #[Groups([
        self::STEP_READ,
        self::STEP_WRITE,
        self::DPP_STEP_READ,
        self::LOGISTICS_STEP_READ,
    ])]
    #[MaxDepth(1)]
    private ?ProductStep $parentStep = null;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'productSteps')]
    #[Groups([
        self::STEP_READ,
        self::STEP_WRITE,
    ])]
    private ?Product $product = null;

    #[ORM\ManyToOne(targetEntity: Logistics::class)]
    #[Groups([
        self::STEP_READ,
        self::STEP_WRITE,
    ])]
    private ?Logistics $logistics = null;

    #[ORM\ManyToOne(targetEntity: Node::class, inversedBy: 'productSteps')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Node $node = null;

    #[ORM\ManyToOne(targetEntity: Company::class, inversedBy: 'productSteps')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups([
        self::STEP_READ,
        self::STEP_WRITE,
    ])]
    private ?Company $company = null;

    #[ORM\ManyToOne(targetEntity: CompanySite::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups([
        self::STEP_READ,
        self::STEP_WRITE,
    ])]
    private ?CompanySite $companySite = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups([
        self::STEP_READ,
        self::STEP_WRITE,
    ])]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Logistics::class, cascade: ['persist'], inversedBy: 'fromProductSteps')]
    #[Groups([
        self::STEP_READ,
        self::STEP_WRITE,
    ])]
    private ?Logistics $materialsSentWith = null;

    /**
     * @var Collection<int, Logistics>
     */
    #[ORM\ManyToMany(targetEntity: Logistics::class, inversedBy: 'toProductSteps', cascade: ['persist'])]
    #[ORM\JoinTable(name: 'product_step_material_received_from')]
    #[ORM\JoinColumn(name: 'product_step_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'logistics_id', referencedColumnName: 'id')]
    #[Groups([
        self::STEP_READ,
        self::STEP_WRITE,
    ])]
    private Collection $materialsReceivedFrom;

    #[ORM\Column(type: Types::INTEGER, length: 2)]
    #[Groups([
        self::STEP_READ,
        self::STEP_WRITE,
        self::DPP_STEP_READ,
        self::LOGISTICS_STEP_READ,
    ])]
    private int $sort = 1;

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['default' => 1])]
    #[Groups([
        self::STEP_READ,
        self::STEP_WRITE,
        self::DPP_STEP_READ,
        self::LOGISTICS_STEP_READ,
    ])]
    private int $quantityIndex = 1;

    #[Vich\UploadableField(mapping: 'step_qrs', fileNameProperty: 'qrImage')]
    public ?File $qrFile = null;

    #[ORM\Column(type: Types::STRING, nullable: true, options: [
        'default' => '',
    ])]
    #[Groups([self::STEP_READ])]
    private ?string $qrImage = '';

    #[ORM\Column(type: Types::BOOLEAN, nullable: false, options: [
        'default' => false,
    ])]
    #[Groups([self::STEP_WRITE])]
    private bool $createQr = false;

    #[ORM\Column(type: Types::BOOLEAN, options: [
        'default' => false,
    ])]
    #[Groups([
        self::STEP_READ,
        self::STEP_WRITE,
        self::DPP_STEP_READ,
    ])]
    private bool $locked = false;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Groups([self::STEP_READ])]
    private ?int $qrId = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups([self::STEP_READ])]
    private ?DateTime $tsaVerifiedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups([self::STEP_READ])]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    #[Groups([
        self::STEP_READ,
        self::STEP_WRITE,
    ])]
    private bool $closed = false;

    #[ORM\Column(type: Types::STRING, nullable: true, options: ['default' => null])]
    #[Groups([
        self::STEP_READ,
        self::STEP_WRITE,
    ])]
    private ?string $typeOfTransport = null;

    /**
     * @var Collection<int, ProductStep>
     */
    #[ORM\OneToMany(targetEntity: ProductStep::class, mappedBy: 'parentStep', cascade: ['persist'], orphanRemoval: true)]
    #[Groups([
        self::STEP_READ,
        self::DPP_STEP_READ,
        self::LOGISTICS_STEP_READ,
    ])]
    #[MaxDepth(1)]
    private Collection $steps;

    /**
     * @var Collection<int, ProductInput>
     */
    #[ORM\OneToMany(targetEntity: ProductInput::class, mappedBy: 'productStep', cascade: ['persist'], orphanRemoval: true)]
    #[Groups([
        self::STEP_READ,
        self::DPP_STEP_READ,
        self::LOGISTICS_STEP_READ,
    ])]
    private Collection $productInputs;

    #[ORM\ManyToOne(targetEntity: ProductStep::class, inversedBy: 'processedMaterials')]
    #[Groups([
        self::STEP_READ,
        self::STEP_WRITE,
    ])]
    private ?ProductStep $usedIn = null;

    /**
     * @var Collection<int, ProductStep>
     */
    #[ORM\OneToMany(targetEntity: ProductStep::class, mappedBy: 'usedIn', cascade: ['persist'])]
    #[Groups([
        self::STEP_READ,
        self::STEP_WRITE,
    ])]
    #[MaxDepth(1)]
    private Collection $processedMaterials;

    #[ORM\ManyToOne(targetEntity: Dpp::class, inversedBy: 'productSteps')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups([
        self::STEP_READ,
        self::STEP_WRITE,
    ])]
    private ?Dpp $dpp = null;

    #[ORM\Column(type: Types::STRING, nullable: true, options: ['default' => ''])]
    #[Groups([
        self::STEP_READ,
        self::STEP_WRITE,
        self::DPP_STEP_READ,
        self::LOGISTICS_STEP_READ,
    ])]
    private ?string $unitMeasurement = '';

    #[ORM\Column(type: Types::STRING, nullable: true, options: ['default' => ''])]
    #[Groups([
        self::STEP_READ,
        self::STEP_WRITE,
        self::DPP_STEP_READ,
        self::LOGISTICS_STEP_READ,
    ])]
    private ?string $measurementType = '';

    #[ORM\Column(type: Types::STRING, length: 255, options: ['default' => ''])]
    #[Groups([
        self::STEP_READ,
        self::STEP_WRITE,
        self::DPP_STEP_READ,
        self::LOGISTICS_STEP_READ,
    ])]
    private string $unitSymbol = '';

    #[ORM\Column(type: Types::FLOAT, nullable: true, options: ['default' => 0])]
    #[Groups([
        self::STEP_READ,
        self::STEP_WRITE,
        self::DPP_STEP_READ,
        self::LOGISTICS_STEP_READ,
    ])]
    private ?float $measurementValue = 0;

    /**
     * @var array{
     *     initializationValues: array{value: float, inputs: list<array{uuid: string, value: float}>},
     *     consumptions: list<array{uuid: string, value: float, inputs: list<array{uuid: string, value: float}>}>,
     *     remainingValues: array{value: float, inputs: list<array{uuid: string, value: float}>}
     * }|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups([
        self::STEP_READ,
        self::STEP_WRITE,
        self::DPP_STEP_READ,
    ])]
    private ?array $transactions = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups([
        self::STEP_READ,
        self::DPP_STEP_READ,
        self::LOGISTICS_STEP_READ,
    ])]
    private string $dppName = '';

    #[ORM\Column(type: Types::BOOLEAN, nullable: false, options: [
        'default' => false,
    ])]
    #[Groups([
        self::STEP_READ,
        self::STEP_WRITE,
        self::DPP_STEP_READ,
        self::LOGISTICS_STEP_READ,
    ])]
    private bool $ongoingDpp = false;

    #[ORM\Column(type: Types::BOOLEAN, nullable: false, options: [
        'default' => false,
    ])]
    #[Groups([
        self::STEP_READ,
        self::STEP_WRITE,
        self::DPP_STEP_READ,
        self::LOGISTICS_STEP_READ,
    ])]
    private bool $createEmptyDpp = false;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    #[Groups([
        self::STEP_READ,
        self::STEP_WRITE,
        self::DPP_STEP_READ,
        self::LOGISTICS_STEP_READ,
    ])]
    private ?string $state = Dpp::STATE_NOT_ASSIGNED;

    #[ORM\Column(type: Types::STRING, options: ['default' => ''])]
    #[Groups([
        self::STEP_READ,
        self::STEP_WRITE,
        self::DPP_STEP_READ,
        self::LOGISTICS_STEP_READ,
    ])]
    private string $timestampPath = '';

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    #[Groups([
        self::STEP_READ,
        self::STEP_WRITE,
        self::DPP_STEP_READ,
        self::LOGISTICS_STEP_READ,
    ])]
    private bool $updatable = false;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['default' => null])]
    #[Groups([
        self::STEP_READ,
        self::STEP_WRITE,
        self::DPP_STEP_READ,
        self::LOGISTICS_STEP_READ,
    ])]
    private ?DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->steps = new ArrayCollection();
        $this->productInputs = new ArrayCollection();
        $this->materialsReceivedFrom = new ArrayCollection();
        $this->processedMaterials = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(File $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function getStepImage(): ?string
    {
        return $this->stepImage;
    }

    public function setStepImage(?string $stepImage): self
    {
        $this->stepImage = $stepImage;

        return $this;
    }

    public function getStepTemplateReference(): Step
    {
        return $this->stepTemplateReference;
    }

    public function setStepTemplateReference(Step $stepTemplateReference): self
    {
        $this->stepTemplateReference = $stepTemplateReference;

        return $this;
    }

    public function getParentStep(): ?ProductStep
    {
        return $this->parentStep;
    }

    public function setParentStep(?ProductStep $parentStep): self
    {
        $this->parentStep = $parentStep;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getLogistics(): ?Logistics
    {
        return $this->logistics;
    }

    public function setLogistics(?Logistics $logistics): self
    {
        $this->logistics = $logistics;

        return $this;
    }

    public function getNode(): ?Node
    {
        return $this->node;
    }

    public function setNode(?Node $node): void
    {
        $this->node = $node;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): void
    {
        $this->company = $company;
    }

    public function getCompanySite(): ?CompanySite
    {
        return $this->companySite;
    }

    public function setCompanySite(?CompanySite $companySite): void
    {
        $this->companySite = $companySite;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function getMaterialsSentWith(): ?Logistics
    {
        return $this->materialsSentWith;
    }

    public function setMaterialsSentWith(?Logistics $materialsSentWith): void
    {
        $this->materialsSentWith = $materialsSentWith;

        if ($materialsSentWith !== null) {
            $this->setState(Dpp::STATE_LOGISTICS);
        } else {
            $this->setState(Dpp::STATE_NOT_ASSIGNED);
        }
    }

    /**
     * @return Collection<int, Logistics>
     */
    public function getMaterialsReceivedFrom(): Collection
    {
        return $this->materialsReceivedFrom;
    }

    public function addMaterialsReceivedFrom(Logistics $logistics): self
    {
        if (!$this->materialsReceivedFrom->contains($logistics)) {
            $this->materialsReceivedFrom->add($logistics);
            $logistics->addToProductStep($this);
        }

        return $this;
    }

    public function removeMaterialsReceivedFrom(Logistics $logistics): self
    {
        if ($this->materialsReceivedFrom->contains($logistics)) {
            $this->materialsReceivedFrom->removeElement($logistics);
        }

        return $this;
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

    public function getQuantityIndex(): int
    {
        return $this->quantityIndex;
    }

    public function setQuantityIndex(int $quantityIndex): self
    {
        $this->quantityIndex = $quantityIndex;

        return $this;
    }

    public function getTsaVerifiedAt(): ?DateTime
    {
        return $this->tsaVerifiedAt;
    }

    public function setTsaVerifiedAt(?DateTime $tsaVerifiedAt): self
    {
        $this->tsaVerifiedAt = $tsaVerifiedAt;

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function isClosed(): bool
    {
        return $this->closed;
    }

    public function setClosed(bool $closed): self
    {
        $this->closed = $closed;

        return $this;
    }

    public function getTypeOfTransport(): ?string
    {
        return $this->typeOfTransport;
    }

    public function setTypeOfTransport(?string $typeOfTransport): self
    {
        $this->typeOfTransport = $typeOfTransport;

        return $this;
    }

    /**
     * @return Collection<int, ProductStep>
     */
    public function getSteps(): Collection
    {
        return $this->steps;
    }

    public function addStep(ProductStep $step): self
    {
        if (!$this->steps->contains($step)) {
            $this->steps->add($step);
            $step->setParentStep($this);
        }

        return $this;
    }

    public function removeStep(ProductStep $step): self
    {
        if ($this->steps->contains($step)) {
            $this->steps->removeElement($step);
        }

        return $this;
    }

    /**
     * @return Collection<int, ProductInput>
     */
    public function getProductInputs(): Collection
    {
        return $this->productInputs;
    }

    public function addInput(ProductInput $input): self
    {
        if (!$this->productInputs->contains($input)) {
            $this->productInputs->add($input);
            $input->setProductStep($this);
        }

        return $this;
    }

    public function removeInput(ProductInput $input): self
    {
        if ($this->productInputs->contains($input)) {
            $this->productInputs->removeElement($input);
        }

        return $this;
    }

    public function getUsedIn(): ?ProductStep
    {
        return $this->usedIn;
    }

    public function setUsedIn(?ProductStep $usedIn): void
    {
        $this->usedIn = $usedIn;
    }

    /**
     * @return Collection<int, ProductStep>
     */
    public function getProcessedMaterials(): Collection
    {
        return $this->processedMaterials;
    }

    public function addProcessedMaterial(ProductStep $productStep): self
    {
        if (!$this->processedMaterials->contains($productStep)) {
            $this->processedMaterials->add($productStep);

            $productStep->setUsedIn($this);
            $productStep->setState(Dpp::STATE_EXPORT_DPP);
            $dpp = $productStep->getDpp();

            if($dpp === null) {
                return $this;
            }

            $dppProductSteps = $dpp->getProductSteps();
            $count = 0;

            foreach($dppProductSteps as $dpproductStep) {
                if($dpproductStep->getUsedIn() === null) {
                    $count++;
                }
            }

            if($count === 0) {
                $dpp->setState(Dpp::STATE_EXPORT_DPP);
            } else {
                $dpp->setState(Dpp::STATE_DPP_IN_USE);
            }

        }

        return $this;
    }

    public function removeProcessedMaterial(ProductStep $productStep): self
    {
        if ($this->processedMaterials->contains($productStep)) {
            $this->processedMaterials->removeElement($productStep);
        }

        return $this;
    }

    public function getDpp(): ?Dpp
    {
        return $this->dpp;
    }

    public function setDpp(?Dpp $dpp): self
    {
        $this->dpp = $dpp;

        return $this;
    }

    public function isLocked(): bool
    {
        return $this->locked;
    }

    public function setLocked(bool $locked): self
    {
        $this->locked = $locked;

        return $this;
    }

    public function isCreateQr(): bool
    {
        return $this->createQr;
    }

    public function setCreateQr(bool $createQr): self
    {
        $this->createQr = $createQr;

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

    public function getUnitSymbol(): string
    {
        return $this->unitSymbol;
    }

    public function setUnitSymbol(string $unitSymbol): self
    {
        $this->unitSymbol = $unitSymbol;

        return $this;
    }

    public function getMeasurementValue(): ?float
    {
        return $this->measurementValue;
    }

    public function setMeasurementValue(?float $measurementValue): self
    {
        $this->measurementValue = $measurementValue;

        return $this;
    }

    /**
     * @return array{
     *     initializationValues: array{value: float, inputs: list<array{uuid: string, value: float}>},
     *     consumptions: list<array{uuid: string, value: float, inputs: list<array{uuid: string, value: float}>}>,
     *     remainingValues: array{value: float, inputs: list<array{uuid: string, value: float}>}
     * }|null
     */
    public function getTransactions(): ?array
    {
        return $this->transactions;
    }

    /**
     * @param array{
     *     initializationValues: array{value: float, inputs: list<array{uuid: string, value: float}>},
     *     consumptions: list<array{uuid: string, value: float, inputs: list<array{uuid: string, value: float}>}>,
     *     remainingValues: array{value: float, inputs: list<array{uuid: string, value: float}>}
     * }|null $transactions
     */
    public function setTransactions(?array $transactions): self
    {
        $this->transactions = $transactions;

        return $this;
    }

    public function getDppName(): string
    {
        return $this->dppName;
    }

    public function setDppName(string $dppName): void
    {
        $this->dppName = $dppName;
    }

    public function isOngoingDpp(): bool
    {
        return $this->ongoingDpp;
    }

    public function setOngoingDpp(bool $ongoingDpp): void
    {
        $this->ongoingDpp = $ongoingDpp;
    }

    public function isCreateEmptyDpp(): bool
    {
        return $this->createEmptyDpp;
    }

    public function setCreateEmptyDpp(bool $createEmptyDpp): void
    {
        $this->createEmptyDpp = $createEmptyDpp;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): void
    {
        $this->state = $state;
    }

    public function getTimestampPath(): string
    {
        return $this->timestampPath;
    }

    public function setTimestampPath(string $timestampPath): void
    {
        $this->timestampPath = $timestampPath;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function isUpdatable(): bool
    {
        return $this->updatable;
    }

    public function setUpdatable(bool $updatable): void
    {
        $this->updatable = $updatable;
    }

    #[Groups([self::STEP_READ])]
    public function getNodeId(): ?Uuid
    {
        return $this->node?->getId();
    }

    /**
     * @return array{id: string, firstName: string|null, lastName: string|null}
     */
    #[Groups([
        self::STEP_READ,
        self::STEP_WRITE,
        self::DPP_STEP_READ,
    ])]
    public function getUserData(): array
    {
        return [
            'id' => (string) $this->user?->getId(),
            'firstName' => $this->user?->getFirstName(),
            'lastName' => $this->user?->getLastName(),
        ];
    }

    /**
     * @return array<int<0, max>, array{
     *     node: Node,
     *     dpps: array<int<0, max>, array{
     *          id: string,
     *          name: string|null,
     *      }>,
     * }>
     */
    #[Groups([
        self::STEP_READ,
    ])]
    public function getParentDpps(): array
    {
        $nodesWithProductSteps = [];
        $materialsByNode = [];

        $parents = $this->getNode()?->getParents();

        if (!$parents) {
            return [];
        }

        foreach ($this->getProcessedMaterials() as $material) {
            $node = $material->getNode();
            if ($node !== null) {
                $nodesWithProductSteps[] = $node;

                $materialsByNode[spl_object_hash($node)][] = [
                    'id' => $material->getId()->jsonSerialize(),
                    'name' => $material->getName(),
                ];
            }
        }

        $nodesWithProductSteps = array_unique($nodesWithProductSteps, SORT_REGULAR);
        $parentDpps = [];

        foreach ($parents as $parent) {
            if (!in_array($parent, $nodesWithProductSteps, true)) {
                continue;
            }

            $parentDppsCollection = $parent->getDpps();

            $countDpp = [
                'notAssignedDpp' => 0,
                'dppInUse' => 0,
                'logistics' => 0,
                'exportDpp' => 0,
                'ongoingDpp' => 0,
                'emptyDpp' => 0,
            ];

            $this->addDppCounts($parentDppsCollection, $countDpp);

            foreach ($parentDppsCollection as $parentDpp) {
                $dppProductSteps = $parentDpp->getProductSteps();
                $this->addDppCounts($dppProductSteps, $countDpp);
            }

            $parent->setCountDpp($countDpp);

            $parentKey = spl_object_hash($parent);
            $parentSerializedSteps = $materialsByNode[$parentKey] ?? [];

            $parentDpps[] = [
                'node' => $parent,
                'dpps' => $parentSerializedSteps,
            ];
        }

        return $parentDpps;
    }

    /**
     * Filters DPP by status.
     *
     * @param iterable<Dpp|ProductStep> $dpps
     * @param array<string> $states
     * @return array<Dpp|ProductStep>
     */
    private function filterDppsByState(iterable $dpps, array $states): array
    {
        return array_filter(iterator_to_array($dpps), fn (Dpp|ProductStep $dpp) => in_array($dpp->getState(), $states, true));
    }

    /**
     * Filters DPP by ongoing status.
     *
     * @param iterable<Dpp|ProductStep> $dpps
     * @return array<Dpp|ProductStep>
     */
    private function filterDppsByOngoing(iterable $dpps, bool $ongoing): array
    {
        return array_filter(iterator_to_array($dpps), fn (Dpp|ProductStep $dpp) => $dpp->isOngoingDpp() === $ongoing);
    }

    /**
     * Filter DPPs by the ongoing property.
     *
     * @param iterable<Dpp|ProductStep> $dpps
     * @return array<Dpp|ProductStep>
     */
    private function filterDppsByEmpty(iterable $dpps, bool $empty): array
    {
        return array_filter(iterator_to_array($dpps), function (Dpp|ProductStep $dpp) use ($empty) {
            return $dpp->isCreateEmptyDpp() === $empty;
        });
    }

    /**
     * @param iterable<Dpp>|iterable<ProductStep> $collection
     * @param array<string,int> $counts
     */
    private function addDppCounts(iterable $collection, array &$counts): void
    {
        $dppsExport = $this->filterDppsByState($collection, [Dpp::STATE_EXPORT_DPP]);
        $dppsNotAssigned = $this->filterDppsByState($collection, [Dpp::STATE_NOT_ASSIGNED]);
        $dppsInUse = $this->filterDppsByState($collection, [Dpp::STATE_DPP_IN_USE]);
        $dppsLogistics = $this->filterDppsByState($collection, [Dpp::STATE_LOGISTICS]);
        $dppsOngoing = $this->filterDppsByOngoing($collection, true);
        $dppsEmpty = $this->filterDppsByEmpty($collection, true);

        $dppsNotAssigned = array_filter($dppsNotAssigned, fn ($dpp) => !$dpp->isOngoingDpp());
        $dppsLogistics = array_filter($dppsLogistics, fn ($dpp) => !$dpp->isOngoingDpp());
        $dppsExport = array_filter($dppsExport, fn ($dpp) => !$dpp->isOngoingDpp());

        $dppsNotAssigned = array_filter($dppsNotAssigned, fn ($dpp) => !$dpp->isCreateEmptyDpp());
        $dppsLogistics = array_filter($dppsLogistics, fn ($dpp) => !$dpp->isCreateEmptyDpp());
        $dppsExport = array_filter($dppsExport, fn ($dpp) => !$dpp->isCreateEmptyDpp());

        $counts['notAssignedDpp'] += count($dppsNotAssigned);
        $counts['dppInUse'] += count($dppsInUse);
        $counts['logistics'] += count($dppsLogistics);
        $counts['exportDpp'] += count($dppsExport);
        $counts['ongoingDpp'] += count($dppsOngoing);
        $counts['emptyDpp'] += count($dppsEmpty);
    }
}

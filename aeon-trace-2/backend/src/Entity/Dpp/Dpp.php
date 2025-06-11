<?php

declare(strict_types=1);

namespace App\Entity\Dpp;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Api\Filter\DppByCreateEmptyDppFilter;
use App\Api\Filter\DppByNodeIdFilter;
use App\Api\Filter\DppByOngoingDppFilter;
use App\Api\Filter\DppByStateFilter;
use App\Api\Filter\DppProductInputsLengthOrderFilter;
use App\Api\Filter\DppUserOrderFilter;
use App\Controller\Dpp\GetDppsByIdController;
use App\Controller\Dpp\GetProcessedMaterialsTraceController;
use App\Controller\Dpp\JsonExportDppController;
use App\Controller\Dpp\JsonStepsToDppController;
use App\Controller\Dpp\MapEnaleiaJsonToDppController;
use App\Controller\Dpp\UpdateJsonInputsController;
use App\DataProviders\DppGeoCollectionProvider;
use App\DataProviders\QrEntityDataProvider;
use App\DataProviders\TimestampDataProvider;
use App\DataTransferObjects\Dpp\CreateFromJsonInput;
use App\DataTransferObjects\Dpp\InputJsonWrapper;
use App\Entity\Company\Company;
use App\Entity\Company\CompanySite;
use App\Entity\Embeddable\Address;
use App\Entity\ImageCollection\DppImage;
use App\Entity\Logistics\Logistics;
use App\Entity\Product\Product;
use App\Entity\Product\ProductInput;
use App\Entity\Product\ProductInputImage;
use App\Entity\Product\ProductTemplate;
use App\Entity\Quirk\HasUid;
use App\Entity\Step\ProductStep;
use App\Entity\Step\Step;
use App\Entity\SupplyChain\Node;
use App\Entity\SupplyChain\SupplyChainTemplate;
use App\Entity\User;
use App\Entity\VideoCollection\DppVideo;
use App\Repository\Dpp\DppRepository;
use App\StateProcessors\DppCreateProcessor;
use App\StateProcessors\FileDeleteProcessor;
use App\StateProcessors\ImageUploadProcessor;
use App\StateProcessors\QrDeleteProcessor;
use DateTime;
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
        new GetCollection(
            normalizationContext: [
                'groups' => [self::SUPPLY_CHAIN_DPP_COLLECTION],
            ]
        ),
        new GetCollection(
            uriTemplate: '/dpps/listing',
            normalizationContext: [
                'groups' => [self::SUPPLY_CHAIN_DPP_LISTING],
            ],
        ),
        new GetCollection(
            uriTemplate: '/dpps/by_ids',
            controller: GetDppsByIdController::class,
            openapiContext: [
                'parameters' => [
                    [
                        'name' => 'ids',
                        'in' => 'query',
                        'description' => 'IDs of dpps',
                        'required' => true,
                        'type' => 'array',
                        'items' => [
                            'type' => 'string',
                        ],
                    ],
                    [
                        'name' => 'parentStepIds',
                        'in' => 'query',
                        'description' => 'IDs of parent steps.',
                        'required' => true,
                        'type' => 'array',
                        'items' => [
                            'type' => 'string',
                        ],
                    ],
                ],
            ],
        ),
        new Get(
            normalizationContext: [
                'groups' => [
                    Address::ADDRESS_READ,
                    self::DPP_READ_DETAIL,
                    Product::DPP_PRODUCT_READ,
                    ProductStep::DPP_STEP_READ,
                    ProductInput::DPP_PRODUCT_INPUT_READ,
                    ProductInputImage::DPP_PRODUCT_INPUT_IMAGE_READ,
                    CompanySite::DPP_COMPANY_SITE_READ,
                ],
            ],
        ),
        new Get(
            uriTemplate: '/dpps/{id}/verify_timestamp',
            provider: TimestampDataProvider::class,
        ),
        new Get(
            uriTemplate: '/dpps/steps/{id}',
            normalizationContext: [
                'groups' => [
                    self::DPP_STEPS,
                    ProductStep::DPP_STEP_READ,
                ],
            ],
        ),
        new Get(
            uriTemplate: '/dpps/promises/{id}',
            normalizationContext: [
                'groups' => [
                    self::DPP_PROMISES,
                    ProductStep::DPP_STEP_READ,
                    ProductInput::DPP_PRODUCT_INPUT_READ,
                    ProductTemplate::PRODUCT_TEMPLATE_READ,
                ],
            ],
        ),
        new Get(
            uriTemplate: '/static/dpp/getByQrId',
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
        new Get(
            uriTemplate: '/dpps/{id}/get_geo',
            provider: DppGeoCollectionProvider::class,
        ),
        new Post(
            processor: DppCreateProcessor::class
        ),
        new Post(
            uriTemplate: '/dpps/{id}/cover_image',
            inputFormats: [
                'multipart' => ['multipart/form-data'],
            ],
            denormalizationContext: [
                'groups' => [self::DPP_IMAGE_UPLOAD],
            ],
            processor: ImageUploadProcessor::class
        ),
        new Post(
            uriTemplate: 'dpps/upload_enaleia_json_file',
            controller: MapEnaleiaJsonToDppController::class,
            normalizationContext: [],
            denormalizationContext: [],
            input: CreateFromJsonInput::class,
        ),
        new Post(
            uriTemplate: 'dpps/update_inputs_for_steps_json_file',
            controller: UpdateJsonInputsController::class,
            normalizationContext: [],
            denormalizationContext: [],
            input: InputJsonWrapper::class,
        ),
        new Post(
            uriTemplate: 'dpps/upload_steps_json_file',
            controller: JsonStepsToDppController::class,
            normalizationContext: [],
            denormalizationContext: [],
            read: false,
            deserialize: false,
        ),
        new Get(
            uriTemplate: 'dpps/export_json_file/{id}',
            controller: JsonExportDppController::class,
            normalizationContext: [
                self::DPP_READ,
            ],
        ),
        new Get(
            uriTemplate: 'dpps/get_processed_materials_trace/{id}',
            controller: GetProcessedMaterialsTraceController::class,
            normalizationContext: [
                self::DPP_READ,
            ],
        ),
        new Patch(
            processor: DppCreateProcessor::class
        ),
        new Delete(
            processor: QrDeleteProcessor::class
        ),
        new Delete(
            uriTemplate: '/dpps/{id}/cover_image',
            processor: FileDeleteProcessor::class,
        ),
    ],
    normalizationContext: [
        'groups' => [
            Address::ADDRESS_READ,
            self::DPP_READ,
            Product::DPP_PRODUCT_READ,
            ProductStep::DPP_STEP_READ,
            ProductInput::DPP_PRODUCT_INPUT_READ,
            ProductInputImage::DPP_PRODUCT_INPUT_IMAGE_READ,
            CompanySite::DPP_COMPANY_SITE_READ,
            DppImage::DPP_IMAGE_READ,
            DppVideo::DPP_VIDEO_READ,
            ProductTemplate::PRODUCT_TEMPLATE_READ,
        ],
    ],
    denormalizationContext: [
        'groups' => [
            self::DPP_WRITE,
        ],
    ],
    order: ['createdAt' => 'DESC'],
)]
#[ORM\Entity(repositoryClass: DppRepository::class)]
#[ApiFilter(filterClass: DppByNodeIdFilter::class, properties: ['node.id' => 'exact'])]
#[ApiFilter(filterClass: DppByStateFilter::class, properties: ['dpp.state' => 'exact'])]
#[ApiFilter(filterClass: DppByOngoingDppFilter::class, properties: ['dpp.ongoingDpp' => 'exact'])]
#[ApiFilter(filterClass: DppByCreateEmptyDppFilter::class, properties: ['dpp.createEmptyDpp' => 'exact'])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt', 'name', 'tsaVerifiedAt'])]
#[ApiFilter(DppProductInputsLengthOrderFilter::class, properties: ['order[numberOfInputs]'])]
#[ApiFilter(DppUserOrderFilter::class, properties: ['order[userData]'])]
#[ApiFilter(SearchFilter::class, properties: ['name' => 'partial'])]
#[Vich\Uploadable]
class Dpp
{
    use HasUid;

    public const URL_PATH = 'product';
    public const DPP_PROMISES = 'dpp-promises';
    public const DPP_STEPS = 'dpp-steps';
    public const DPP_READ = 'dpp-read';
    public const DPP_READ_DETAIL = 'dpp-read-detail';
    public const DPP_WRITE = 'dpp-write';
    public const SUPPLY_CHAIN_DPP_COLLECTION = 'supply-chain-dpp-collection';
    public const SUPPLY_CHAIN_DPP_LISTING = 'supply-chain-dpp-listing';
    public const SUPPLY_CHAIN_DPP_READ = 'supply-chain-dpp-read';
    public const SUPPLY_CHAIN_DPP_WRITE = 'supply-chain-dpp-write';
    public const DPP_IMAGE_UPLOAD = 'dpp-image-upload';
    public const SERIALIZABLE = 'serializable';
    public const STATE_NOT_ASSIGNED = 'NOT_ASSIGNED';     // Status for a standard DPP that is not assigned to logistics
    public const STATE_DPP_IN_USE = 'IN_USE';             // Status for a DPP that belongs to logistics but is not yet assigned or exported
    public const STATE_LOGISTICS = 'LOGISTICS';          // Status for a DPP that is assigned to logistics but not yet exported
    public const STATE_EXPORT_DPP = 'EXPORT_DPP';       // Status for a DPP that has been assigned to logistics and successfully exported

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups([
        self::DPP_PROMISES,
        self::DPP_READ,
        self::DPP_READ_DETAIL,
        self::SUPPLY_CHAIN_DPP_READ,
        self::SERIALIZABLE,
        self::SUPPLY_CHAIN_DPP_COLLECTION,
        self::SUPPLY_CHAIN_DPP_LISTING,
    ])]
    private Uuid $id;

    #[ORM\Column(type: Types::STRING, options: ['default' => null])]
    #[Groups([
        self::DPP_READ,
        self::DPP_READ_DETAIL,
        self::DPP_PROMISES,
        self::SUPPLY_CHAIN_DPP_READ,
        self::SUPPLY_CHAIN_DPP_COLLECTION,
        self::SUPPLY_CHAIN_DPP_LISTING,
    ])]
    private ?string $name = null;

    #[Vich\UploadableField(mapping: 'dpp_cover_images', fileNameProperty: 'coverImage')]
    #[Groups([self::DPP_IMAGE_UPLOAD])]
    public ?File $file = null;

    #[ORM\Column(type: Types::STRING, nullable: true, options: [
        'default' => '',
    ])]
    #[Groups([
        self::DPP_READ,
        self::SUPPLY_CHAIN_DPP_READ,
    ])]
    private ?string $coverImage = '';

    #[Vich\UploadableField(mapping: 'dpp_qrs', fileNameProperty: 'qrImage')]
    public ?File $qrFile = null;

    #[ORM\Column(type: Types::STRING, nullable: true, options: [
        'default' => '',
    ])]
    #[Groups([
        self::DPP_READ,
        self::DPP_READ_DETAIL,
        self::DPP_PROMISES,
        self::SUPPLY_CHAIN_DPP_READ,
        self::SUPPLY_CHAIN_DPP_COLLECTION,
        self::SUPPLY_CHAIN_DPP_LISTING,
    ])]
    private ?string $qrImage = '';

    #[ORM\Column(type: Types::BOOLEAN, nullable: false, options: [
        'default' => false,
    ])]
    #[Groups([
        self::DPP_READ,
        self::DPP_PROMISES,
        self::DPP_WRITE,
        self::SUPPLY_CHAIN_DPP_COLLECTION,
        self::SUPPLY_CHAIN_DPP_LISTING,
    ])]
    private bool $ongoingDpp = false;

    #[ORM\Column(type: Types::BOOLEAN, nullable: false, options: [
        'default' => false,
    ])]
    #[Groups([
        self::DPP_WRITE,
        self::SUPPLY_CHAIN_DPP_WRITE,
    ])]
    private bool $createQr = false;

    #[ORM\Column(type: Types::BOOLEAN, nullable: false, options: [
        'default' => false,
    ])]
    #[Groups([
        self::DPP_READ,
        self::DPP_PROMISES,
        self::DPP_WRITE,
        self::SUPPLY_CHAIN_DPP_WRITE,
        self::SUPPLY_CHAIN_DPP_COLLECTION,
        self::SUPPLY_CHAIN_DPP_LISTING,
    ])]
    private bool $createEmptyDpp = false;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    #[Groups([
        self::DPP_READ,
        self::DPP_WRITE,
        self::DPP_PROMISES,
        self::SUPPLY_CHAIN_DPP_READ,
        self::SUPPLY_CHAIN_DPP_COLLECTION,
        self::SUPPLY_CHAIN_DPP_LISTING,
        self::DPP_READ_DETAIL,
    ])]
    private ?string $state = self::STATE_NOT_ASSIGNED;

   #[ORM\ManyToOne(targetEntity: User::class)]
   #[ORM\JoinColumn(nullable: true)]
    #[Groups([
        self::DPP_WRITE,
        self::DPP_PROMISES,
        self::SUPPLY_CHAIN_DPP_WRITE,
        self::SUPPLY_CHAIN_DPP_READ,
        self::SUPPLY_CHAIN_DPP_COLLECTION,
    ])]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Company::class, inversedBy: 'dpps')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups([
        self::DPP_READ,
        self::DPP_READ_DETAIL,
        self::DPP_WRITE,
        self::DPP_PROMISES,
        self::SUPPLY_CHAIN_DPP_READ,
        self::SUPPLY_CHAIN_DPP_WRITE,
    ])]
    private ?Company $company = null;

    #[ORM\ManyToOne(targetEntity: Dpp::class, inversedBy: 'children')]
    #[Groups([
        self::DPP_READ,
        self::DPP_WRITE,
        self::DPP_PROMISES,
        self::SUPPLY_CHAIN_DPP_READ,
    ])]
    private ?Dpp $parent = null;

    #[ORM\ManyToOne(targetEntity: Node::class, inversedBy: 'dpps')]
    #[Groups([
        self::DPP_PROMISES,
        self::DPP_READ,
        self::DPP_READ_DETAIL,
        self::DPP_WRITE,
        self::SUPPLY_CHAIN_DPP_READ,
        self::SUPPLY_CHAIN_DPP_COLLECTION,
        self::SUPPLY_CHAIN_DPP_LISTING,
    ])]
    private ?Node $node = null;

    /**
     * @var Collection<int, Dpp>
     */
    #[ORM\OneToMany(targetEntity: Dpp::class, mappedBy: 'parent', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    #[Groups([
        self::DPP_READ,
        self::DPP_PROMISES,
        self::SUPPLY_CHAIN_DPP_READ,
    ])]
    private Collection $children;

    /**
     * @var Collection<int, Product>
     */
    #[ORM\OneToMany(targetEntity: Product::class, mappedBy: 'dpp', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    #[Groups([
        self::DPP_READ,
        self::DPP_PROMISES,
        self::DPP_WRITE,
    ])]
    private Collection $products;

    #[ORM\ManyToOne(targetEntity: CompanySite::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups([
        self::DPP_READ,
        self::DPP_WRITE,
        self::DPP_READ_DETAIL,
        self::DPP_PROMISES,
        self::SUPPLY_CHAIN_DPP_READ,
        self::SUPPLY_CHAIN_DPP_WRITE,
    ])]
    private ?CompanySite $companySite = null;

    /** @var Collection<int, DppImage> */
    #[ORM\OneToMany(targetEntity: DppImage::class, mappedBy: 'dpp', cascade: ['persist'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    #[Groups([
        DppImage::DPP_IMAGE_READ,
    ])]
    private Collection $dppImages;

    /** @var Collection<int, DppVideo> */
    #[ORM\OneToMany(targetEntity: DppVideo::class, mappedBy: 'dpp', cascade: ['persist'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    #[Groups([
        DppVideo::DPP_VIDEO_READ,
    ])]
    private Collection $dppVideos;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['default' => ''])]
    #[Groups([
        self::DPP_READ,
        self::DPP_WRITE,
        self::SUPPLY_CHAIN_DPP_READ,
        self::SUPPLY_CHAIN_DPP_WRITE,
    ])]
    private string $description = '';

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    #[Groups([
        self::DPP_READ,
        self::DPP_WRITE,
        self::DPP_PROMISES,
        self::SUPPLY_CHAIN_DPP_READ,
        self::SUPPLY_CHAIN_DPP_WRITE,
        self::SUPPLY_CHAIN_DPP_COLLECTION,
    ])]
    private bool $locked = false;

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['default' => null])]
    #[Groups([
        self::DPP_READ,
        self::DPP_PROMISES,
        self::DPP_WRITE,
        self::DPP_READ_DETAIL,
    ])]
    private ?int $qrId = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups([
        self::DPP_READ,
        self::DPP_READ_DETAIL,
        self::DPP_PROMISES,
        self::SUPPLY_CHAIN_DPP_READ,
        self::SUPPLY_CHAIN_DPP_COLLECTION,
        self::SUPPLY_CHAIN_DPP_LISTING,
    ])]
    private ?DateTime $createdAt = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    #[Groups([
        self::DPP_READ,
        self::DPP_READ_DETAIL,
        self::DPP_PROMISES,
        self::SUPPLY_CHAIN_DPP_READ,
        self::SUPPLY_CHAIN_DPP_COLLECTION,
        self::SUPPLY_CHAIN_DPP_LISTING,
    ])]
    private bool $updatable = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTime $updatedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups([
        self::DPP_READ,
        self::DPP_PROMISES,
        self::SUPPLY_CHAIN_DPP_READ,
        self::SUPPLY_CHAIN_DPP_COLLECTION,
        self::SUPPLY_CHAIN_DPP_LISTING,
    ])]
    private ?DateTime $tsaVerifiedAt = null;

    #[ORM\ManyToOne(targetEntity: SupplyChainTemplate::class, inversedBy: 'dpps')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups([
        self::DPP_READ,
        self::DPP_WRITE,
        self::DPP_PROMISES,
        self::SUPPLY_CHAIN_DPP_COLLECTION,
    ])]
    private ?SupplyChainTemplate $supplyChainTemplate = null;

    /**
     * @var Collection<int, ProductInput>
     */
    #[ORM\OneToMany(targetEntity: ProductInput::class, mappedBy: 'dpp', fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    #[Groups([
        self::SERIALIZABLE,
        self::SUPPLY_CHAIN_DPP_COLLECTION,
    ])]
    private Collection $productInputs;

    /**
     * @var Collection<int, ProductStep>
     */
    #[ORM\OneToMany(targetEntity: ProductStep::class, mappedBy: 'dpp', fetch: 'EXTRA_LAZY')]
    #[Groups([
        self::DPP_READ,
        self::DPP_READ_DETAIL,
        self::DPP_WRITE,
        self::DPP_PROMISES,
        self::SUPPLY_CHAIN_DPP_READ,
        self::SUPPLY_CHAIN_DPP_WRITE,
        self::SUPPLY_CHAIN_DPP_COLLECTION,
    ])]
    private Collection $productSteps;

    /**
     * @var Collection<int, Step>
     */
    #[ORM\ManyToMany(targetEntity: Step::class, inversedBy: 'dpps')]
    #[Groups([
        self::DPP_READ,
        self::DPP_WRITE,
        self::DPP_PROMISES,
        self::DPP_STEPS,
        self::SUPPLY_CHAIN_DPP_READ,
        self::SUPPLY_CHAIN_DPP_WRITE,
    ])]
    private Collection $steps;

    #[ORM\ManyToOne(targetEntity: Logistics::class, cascade: ['persist'], inversedBy: 'fromDpps')]
    #[Groups([
        self::DPP_READ,
        self::DPP_WRITE,
        self::DPP_PROMISES,
        self::SUPPLY_CHAIN_DPP_READ,
        self::SUPPLY_CHAIN_DPP_WRITE,
        self::DPP_READ_DETAIL,
    ])]
    private ?Logistics $materialsSentWith = null;

    /**
     * @var Collection<int, Logistics>
     */
    #[ORM\ManyToMany(targetEntity: Logistics::class, inversedBy: 'toDpps', cascade: ['persist'])]
    #[Groups([
        self::DPP_READ,
        self::DPP_WRITE,
        self::DPP_PROMISES,
        self::SUPPLY_CHAIN_DPP_READ,
        self::SUPPLY_CHAIN_DPP_WRITE,
        self::DPP_READ_DETAIL,
    ])]
    private Collection $materialsReceivedFrom;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    #[Groups([
        self::DPP_READ,
        self::DPP_WRITE,
        self::DPP_PROMISES,
        self::SUPPLY_CHAIN_DPP_READ,
        self::SUPPLY_CHAIN_DPP_WRITE,
    ])]
    private bool $imported = false;

    #[ORM\OneToOne(targetEntity: DppConnector::class, mappedBy: 'sourceDpp', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    #[Groups([
        self::DPP_READ,
    ])]
    private ?DppConnector $targetDppConnector = null;

    /**
     * @var Collection<int, DppConnector>
     */
    #[ORM\OneToMany(targetEntity: DppConnector::class, mappedBy: 'targetDpp', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    #[Groups([
        self::DPP_READ,
    ])]
    private Collection $sourceDppConnectors;

    #[ORM\Column(type: Types::STRING, options: ['default' => ''])]
    #[Groups([
        self::DPP_READ,
        self::DPP_READ_DETAIL,
        self::DPP_PROMISES,
        self::SUPPLY_CHAIN_DPP_READ,
        self::SUPPLY_CHAIN_DPP_COLLECTION,
        self::SUPPLY_CHAIN_DPP_LISTING,
    ])]
    private string $timestampPath = '';

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->dppImages = new ArrayCollection();
        $this->dppVideos = new ArrayCollection();
        $this->productInputs = new ArrayCollection();
        $this->productSteps = new ArrayCollection();
        $this->steps = new ArrayCollection();
        $this->materialsReceivedFrom = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->sourceDppConnectors = new ArrayCollection();
    }

    public function getQrFile(): ?File
    {
        return $this->qrFile;
    }

    public function setQrFile(?File $file): void
    {
        $this->qrFile = $file;

        if ($file !== null) {
            $this->updatedAt = new DateTime();
        }
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

    public function isCreateQr(): bool
    {
        return $this->createQr;
    }

    public function setCreateQr(bool $createQr): void
    {
        $this->createQr = $createQr;
    }

    public function getQrId(): ?int
    {
        return $this->qrId;
    }

    public function setQrId(?int $qrId): void
    {
        $this->qrId = $qrId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
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

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * @return array{id: string, firstName: string, lastName: string}
     */
    #[Groups([
        self::DPP_READ,
        self::DPP_READ_DETAIL,
        self::DPP_PROMISES,
        self::SUPPLY_CHAIN_DPP_READ,
        self::SUPPLY_CHAIN_DPP_COLLECTION,
        self::SUPPLY_CHAIN_DPP_LISTING,
    ])]
    public function getUserData(): array
    {
        return [
            'id' => (string) $this->user->getId(),
            'firstName' => $this->user->getFirstName(),
            'lastName' => $this->user->getLastName(),
        ];
    }

    public function getCoverImage(): ?string
    {
        return $this->coverImage;
    }

    public function setCoverImage(?string $coverImage): void
    {
        $this->coverImage = $coverImage;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(File $file): void
    {
        $this->file = $file;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): void
    {
        $this->company = $company;
    }

    public function getParent(): ?Dpp
    {
        return $this->parent;
    }

    public function setParent(?Dpp $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * @return Collection<int, Dpp>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(Dpp $dpp): self
    {
        if (!$this->children->contains($dpp)) {
            $this->children->add($dpp);
            $dpp->setParent($this);
        }

        return $this;
    }

    public function removeChild(Dpp $dpp): self
    {
        if ($this->children->contains($dpp)) {
            $this->children->removeElement($dpp);
        }

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->setDpp($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if ($this->products->contains($product)) {
            $this->products->removeElement($product);
        }

        return $this;
    }

    public function getCompanySite(): ?CompanySite
    {
        return $this->companySite;
    }

    public function setCompanySite(?CompanySite $companySite): void
    {
        $this->companySite = $companySite;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function isLocked(): bool
    {
        return $this->locked;
    }

    public function setLocked(bool $locked): void
    {
        $this->locked = $locked;
    }

    /**
     * @return Collection<int, DppImage>
     */
    public function getDppImages(): Collection
    {
        return $this->dppImages;
    }

    public function addDppImage(DppImage $dppImage): self
    {
        if (!$this->dppImages->contains($dppImage)) {
            $this->dppImages->add($dppImage);
            $dppImage->setDpp($this);
        }

        return $this;
    }

    public function removeDppImage(DppImage $dppImage): self
    {
        if ($this->dppImages->contains($dppImage)) {
            $this->dppImages->removeElement($dppImage);
        }

        return $this;
    }

    /**
     * @return Collection<int, DppVideo>
     */
    public function getDppVideos(): Collection
    {
        return $this->dppVideos;
    }

    public function addDppVideo(DppVideo $dppVideo): self
    {
        if (!$this->dppVideos->contains($dppVideo)) {
            $this->dppVideos->add($dppVideo);
            $dppVideo->setDpp($this);
        }

        return $this;
    }

    public function removeDppVideo(DppVideo $dppVideo): self
    {
        if ($this->dppVideos->contains($dppVideo)) {
            $this->dppVideos->removeElement($dppVideo);
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

    public function addProductInput(ProductInput $input): self
    {
        if (!$this->productInputs->contains($input)) {
            $this->productInputs->add($input);
            $input->setDpp($this);
        }

        return $this;
    }

    public function removeProductInput(ProductInput $input): self
    {
        if ($this->productInputs->contains($input)) {
            $this->productInputs->removeElement($input);
        }

        return $this;
    }

    #[Groups([
        self::SUPPLY_CHAIN_DPP_LISTING,
    ])]
    public function getNumberOfInputs(): ?int
    {
        return $this->productInputs->count();
    }

    public function getNode(): ?Node
    {
        return $this->node;
    }

    public function setNode(?Node $node): void
    {
        $this->node = $node;
    }

    /**
     * @return Collection<int, ProductStep>
     */
    public function getProductSteps(): Collection
    {
        return $this->productSteps;
    }

    public function addProductStep(ProductStep $productStep): self
    {
        if (!$this->productSteps->contains($productStep)) {
            $this->productSteps->add($productStep);
            $productStep->setDpp($this);
        }

        return $this;
    }

    public function removeProductStep(ProductStep $productStep): self
    {
        if ($this->productSteps->contains($productStep)) {
            $this->productSteps->removeElement($productStep);
        }

        return $this;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function isUpdatable(): bool
    {
        return $this->updatable;
    }

    public function setUpdatable(bool $updatable): void
    {
        $this->updatable = $updatable;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getTsaVerifiedAt(): ?DateTime
    {
        return $this->tsaVerifiedAt;
    }

    public function setTsaVerifiedAt(?DateTime $tsaVerifiedAt): void
    {
        $this->tsaVerifiedAt = $tsaVerifiedAt;
    }

    public function getSupplyChainTemplate(): ?SupplyChainTemplate
    {
        return $this->supplyChainTemplate;
    }

    public function setSupplyChainTemplate(?SupplyChainTemplate $supplyChainTemplate): void
    {
        $this->supplyChainTemplate = $supplyChainTemplate;
    }

    /**
     * @return Collection<int, Step>
     */
    public function getSteps(): Collection
    {
        return $this->steps;
    }

    public function addStep(Step $step): self
    {
        if (!$this->steps->contains($step)) {
            $this->steps[] = $step;
        }

        return $this;
    }

    public function removeStep(Step $step): self
    {
        $this->steps->removeElement($step);

        return $this;
    }

    public function getMaterialsSentWith(): ?Logistics
    {
        return $this->materialsSentWith;
    }

    public function setMaterialsSentWith(?Logistics $materialsSentWith): void
    {
        $this->materialsSentWith = $materialsSentWith;

        if ($materialsSentWith !== null) {
            $this->setState(self::STATE_LOGISTICS);
        } else {
            $this->setState(self::STATE_NOT_ASSIGNED);
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

            if(!$logistics->getFromDpps()->isEmpty()) {
                foreach ($logistics->getFromDpps() as $dpp) {
                    if ($dpp->getState() === self::STATE_NOT_ASSIGNED) {
                        $dpp->setState(self::STATE_EXPORT_DPP);
                    }
                }
            }

            $logistics->addToDpp($this);
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

    public function isImported(): bool
    {
        return $this->imported;
    }

    public function setImported(bool $imported): void
    {
        $this->imported = $imported;
    }

    public function getTargetDppConnector(): ?DppConnector
    {
        return $this->targetDppConnector;
    }

    public function setTargetDppConnector(?DppConnector $targetDppConnector): void
    {
        $this->targetDppConnector = $targetDppConnector;
    }

    /**
     * @return Collection<int, DppConnector>
     */
    public function getSourceDppConnectors(): Collection
    {
        return $this->sourceDppConnectors;
    }

    public function addSourceDppConnectors(DppConnector $connector): void
    {
        if (!$this->sourceDppConnectors->contains($connector)) {
            $this->sourceDppConnectors[] = $connector;
            $connector->setTargetDpp($this);
        }
    }

    public function getTimestampPath(): string
    {
        return $this->timestampPath;
    }

    public function setTimestampPath(string $timestampPath): void
    {
        $this->timestampPath = $timestampPath;
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
        self::DPP_READ_DETAIL,
    ])]
    public function getParentDpps(): array
    {
        $parentDpps = [];

        if ($this->node) {
            foreach ($this->node->getParents() as $parent) {
                $parentNodeDpps = [];

                foreach ($parent->getDpps() as $dpp) {
                    foreach ($this->materialsReceivedFrom as $logisticsFrom) {
                        if ($logisticsFrom->getFromDpps()->contains($dpp)) {
                            $parentNodeDpps[] = [
                                'id' => $dpp->getId()->jsonSerialize(),
                                'name' => $dpp->getName(),
                            ];
                            break;
                        }
                    }
                }

                if (!empty($parentNodeDpps)) {
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
                    $parentDpps[] = ['node' => $parent, 'dpps' => $parentNodeDpps];
                }
            }
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

    #[Groups([
        self::DPP_READ_DETAIL,
    ])]
    public function hasUpdatableSteps(): bool
    {
        foreach ($this->productSteps as $step) {
            if ($step->isOngoingDpp()) {
                return true;
            }

            if ($step->isCreateEmptyDpp()) {
                return true;
            }
        }

        return false;
    }
}

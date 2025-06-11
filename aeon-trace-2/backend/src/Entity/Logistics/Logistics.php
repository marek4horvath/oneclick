<?php

declare(strict_types=1);

namespace App\Entity\Logistics;

use ApiPlatform\Doctrine\Orm\Filter\ExistsFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Api\Filter\LogisticsByFromNode;
use App\Api\Filter\LogisticsByStateFilter;
use App\Api\Filter\LogisticsByToNode;
use App\Api\Filter\LogisticsProductInputsLengthOrderFilter;
use App\Api\Filter\LogisticsProductInputsWithLogisticsStepsProductInputsOrderFilter;
use App\Api\Filter\LogisticsUserOrderFilter;
use App\Controller\Logistics\FilterOutExportedLogisticsController;
use App\Controller\Logistics\JsonExportLogisticsController;
use App\Controller\Logistics\JsonToLogisticsController;
use App\DataProviders\PublicLogisticsDataProvider;
use App\Entity\Company\Company;
use App\Entity\Dpp\Dpp;
use App\Entity\Product\ProductInput;
use App\Entity\Step\ProductStep;
use App\Entity\SupplyChain\Node;
use App\Entity\SupplyChain\SupplyChainTemplate;
use App\Entity\User;
use App\Repository\Logistics\LogisticsRepository;
use App\StateProcessors\CreateLogisticsProcessor;
use App\StateProcessors\UpdateLogisticsProcessor;
use DateTime;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ApiResource(
    operations: [
        new GetCollection(),
        new GetCollection(
            uriTemplate: '/logistics/listing',
            normalizationContext: [
                'groups' => [self::LOGISTICS_READ_LISTING],
            ],
        ),
        new GetCollection(
            uriTemplate: '/logistics/filter_out_exported',
            controller: FilterOutExportedLogisticsController::class,
            openapiContext: [
                'parameters' => [
                    [
                        'name' => 'ids',
                        'in' => 'query',
                        'description' => 'IDs of logistics',
                        'required' => true,
                        'type' => 'array',
                        'items' => [
                            'type' => 'string',
                        ],
                    ],
                ],
            ],
        ),
        new Get(),
        new Get(
            uriTemplate: '/static/logistics/getByQrId',
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
            provider: PublicLogisticsDataProvider::class,
        ),
        new Get(
            uriTemplate: 'logistics/export_json_file/{id}',
            controller: JsonExportLogisticsController::class,
            normalizationContext: [],
        ),
        new Post(
            denormalizationContext: [],
            processor: CreateLogisticsProcessor::class,
        ),
        new Post(
            uriTemplate: '/logistics/native',
            denormalizationContext: [],
        ),
        new Post(
            uriTemplate: 'logistics/upload_json_file',
            controller: JsonToLogisticsController::class,
            normalizationContext: [],
            denormalizationContext: ['groups' => [self::LOGISTICS_WRITE_JSON]],
        ),
        new Patch(
            processor: UpdateLogisticsProcessor::class,
        ),
        new Delete(),
    ],
    normalizationContext: ['groups' => [
        self::LOGISTICS_READ,
        ProductInput::LOGISTICS_INPUT_READ,
        StartingPoint::STARTING_POINT_READ,
    ]],
    denormalizationContext: ['groups' => [self::LOGISTICS_WRITE]],
    order: ['createdAt' => 'DESC'],
)]
#[ORM\Entity(repositoryClass: LogisticsRepository::class)]
#[ApiFilter(OrderFilter::class, properties: ['createdAt'])]
#[ApiFilter(filterClass: LogisticsByFromNode::class, properties: ['fromNode.id' => 'exact'])]
#[ApiFilter(filterClass: LogisticsByToNode::class, properties: ['toNode.id' => 'exact'])]
#[ApiFilter(filterClass: LogisticsByStateFilter::class, properties: ['state' => 'exact'])]
#[ApiFilter(OrderFilter::class, properties: ['name', 'createdAt', 'tsaVerifiedAt'])]
#[ApiFilter(LogisticsProductInputsLengthOrderFilter::class, properties: ['order[productInputs.length]'])]
#[ApiFilter(LogisticsProductInputsWithLogisticsStepsProductInputsOrderFilter::class, properties: ['order[numberOfInputs]'])]
#[ApiFilter(LogisticsUserOrderFilter::class, properties: ['order[userData]'])]
#[ApiFilter(SearchFilter::class, properties: ['name' => 'partial'])]
#[ApiFilter(ExistsFilter::class, properties: ['logisticsParent'])]
#[Vich\Uploadable]
class Logistics
{
    public const URL_PATH = 'logistics';
    public const LOGISTICS_READ = 'logistics-read';
    public const LOGISTICS_WRITE = 'logistics-write';
    public const LOGISTICS_READ_LISTING = 'logistics-read-listing';
    public const SERIALIZABLE = 'serializable';
    public const STATE_ASSIGNED_TO_DPP = 'ASSIGNED_TO_DPP';   // The status indicates that the logistics contains DPP
    public const STATE_IN_USE_LOGISTICS = 'IN_USE_LOGISTICS'; // The status indicates that the logistics is being used
    public const STATE_EXPORT_LOGISTICS = 'EXPORT_LOGISTICS'; // The status indicates that the logistics have been exported
    public const LOGISTICS_WRITE_JSON = 'logistics-write-json';

    #[Vich\UploadableField(mapping: 'logistics_qrs', fileNameProperty: 'qrImage')]
    public ?File $qrFile = null;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups([
        self::LOGISTICS_READ,
        self::LOGISTICS_READ_LISTING,
    ])]
    private Uuid $id;

    #[ORM\Column(type: Types::STRING, options: ['default' => ''])]
    #[Groups([
        self::LOGISTICS_READ,
        self::LOGISTICS_READ_LISTING,
        self::LOGISTICS_WRITE,
    ])]
    private string $name = '';

    #[ORM\Column(type: Types::TEXT, options: ['default' => ''])]
    #[Groups([
        self::LOGISTICS_READ,
        self::LOGISTICS_WRITE,
    ])]
    private string $description = '';

    #[ORM\Column(type: Types::STRING, nullable: true, options: ['default' => null])]
    #[Groups([
        self::LOGISTICS_READ,
        self::LOGISTICS_WRITE,
        self::LOGISTICS_WRITE_JSON,
    ])]
    private ?string $typeOfTransport = null;

    /**
     * @var Collection<int, StartingPoint>
     */
    #[ORM\ManyToMany(targetEntity: StartingPoint::class, inversedBy: 'logistics', cascade: ['persist'])]
    #[ORM\JoinTable(name: 'logistics_starting_points')]
    #[Groups([
        self::LOGISTICS_READ,
        self::LOGISTICS_WRITE,
        self::LOGISTICS_WRITE_JSON,
        StartingPoint::STARTING_POINT_READ,
    ])]
    private ?Collection $startingPointCoordinates = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    #[Groups([
        self::LOGISTICS_READ,
        self::LOGISTICS_WRITE,
        self::LOGISTICS_WRITE_JSON,
    ])]
    private ?string $startingCompanyName = null;

    #[ORM\Column(type: Types::FLOAT, options: [
        'default' => 0,
    ])]
    #[Groups([
        self::LOGISTICS_READ,
        self::LOGISTICS_WRITE,
        self::LOGISTICS_WRITE_JSON,
    ])]
    private float $destinationPointLat = 0;

    #[ORM\Column(type: Types::FLOAT, options: [
        'default' => 0,
    ])]
    #[Groups([
        self::LOGISTICS_READ,
        self::LOGISTICS_WRITE,
        self::LOGISTICS_WRITE_JSON,
    ])]
    private float $destinationPointLng = 0;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    #[Groups([
        self::LOGISTICS_READ,
        self::LOGISTICS_WRITE,
        self::LOGISTICS_WRITE_JSON,
    ])]
    private ?string $destinationCompanyName = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true, options: ['default' => 0])]
    #[Groups([
        self::LOGISTICS_READ,
        self::LOGISTICS_WRITE,
        self::LOGISTICS_WRITE_JSON,
    ])]
    private float $totalDistance = 0;

    #[ORM\Column(type: 'integer')]
    #[Assert\Range(
        notInRangeMessage: 'The number of steps must be between {{ min }} and {{ max }}.',
        min: 1,
        max: 10,
    )]
    #[Groups([
        self::LOGISTICS_READ,
        self::LOGISTICS_WRITE,
        self::LOGISTICS_WRITE_JSON,
    ])]
    private int $numberOfSteps = 0;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups([
        self::LOGISTICS_READ,
        self::LOGISTICS_WRITE,
        self::LOGISTICS_WRITE_JSON,
    ])]
    private ?DateTime $departureTime = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups([
        self::LOGISTICS_READ,
        self::LOGISTICS_WRITE,
        self::LOGISTICS_WRITE_JSON,
    ])]
    private ?DateTime $arrivalTime = null;

    #[ORM\ManyToOne(targetEntity: Company::class, inversedBy: 'logistics')]
    #[Groups([
        self::LOGISTICS_READ,
        self::LOGISTICS_WRITE,
        self::LOGISTICS_WRITE_JSON,
    ])]
    private ?Company $company = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups([
        self::LOGISTICS_READ,
        self::LOGISTICS_WRITE,
        self::LOGISTICS_WRITE_JSON,
    ])]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Logistics::class, inversedBy: 'logisticsSteps')]
    #[Groups([
        self::LOGISTICS_READ,
        self::LOGISTICS_WRITE,
    ])]
    private ?Logistics $logisticsParent = null;

    /**
     * @var Collection<int, Logistics>
     */
    #[ORM\OneToMany(targetEntity: Logistics::class, mappedBy: 'logisticsParent', cascade: ['persist'])]
    #[Groups([
        self::LOGISTICS_READ,
        self::LOGISTICS_WRITE,
        self::LOGISTICS_WRITE_JSON,
    ])]
    private Collection $logisticsSteps;

    #[ORM\ManyToOne(targetEntity: Node::class, inversedBy: 'fromNodeLogistics')]
    #[Groups([
        self::LOGISTICS_READ,
        self::LOGISTICS_WRITE,
        self::LOGISTICS_WRITE_JSON,
    ])]
    private Node $fromNode;

    #[ORM\ManyToOne(targetEntity: Node::class, inversedBy: 'toNodeLogistics')]
    #[Groups([
        self::LOGISTICS_READ,
        self::LOGISTICS_WRITE,
        self::LOGISTICS_WRITE_JSON,
    ])]
    private Node $toNode;

    #[ORM\ManyToOne(targetEntity: LogisticsTemplate::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups([
        self::LOGISTICS_READ,
        self::LOGISTICS_WRITE_JSON,
    ])]
    private ?LogisticsTemplate $logisticsTemplate = null;

    #[ORM\ManyToOne(targetEntity: SupplyChainTemplate::class, inversedBy: 'logistics')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups([
        self::LOGISTICS_READ,
        self::LOGISTICS_WRITE,
        self::LOGISTICS_WRITE_JSON,
    ])]
    private ?SupplyChainTemplate $supplyChainTemplate = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['default' => null])]
    #[Groups([
        self::LOGISTICS_READ,
    ])]
    private ?int $qrId = null;

    #[ORM\Column(type: Types::STRING, nullable: true, options: [
        'default' => '',
    ])]
    #[Groups([
        self::LOGISTICS_READ,
        self::LOGISTICS_READ_LISTING,
    ])]
    private ?string $qrImage = '';

    #[ORM\Column(type: Types::STRING, nullable: true)]
    #[Groups([
        self::LOGISTICS_READ,
        self::LOGISTICS_READ_LISTING,
    ])]
    private ?string $state = self::STATE_ASSIGNED_TO_DPP;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups([
        self::LOGISTICS_READ,
        self::LOGISTICS_READ_LISTING,
    ])]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups([
        self::LOGISTICS_READ,
        self::LOGISTICS_READ_LISTING,
    ])]
    private ?DateTime $tsaVerifiedAt = null;

    /**
     * @var Collection<int, Dpp>
     */
    #[ORM\OneToMany(targetEntity: Dpp::class, mappedBy: 'materialsSentWith', cascade: ['persist'])]
    #[Groups([
        self::LOGISTICS_READ,
        self::LOGISTICS_WRITE,
        self::LOGISTICS_READ_LISTING,
    ])]
    private Collection $fromDpps;

    /**
     * @var Collection<int, Dpp>
     */
    #[ORM\ManyToMany(targetEntity: Dpp::class, mappedBy: 'materialsReceivedFrom', cascade: ['persist'])]
    #[Groups([
        self::LOGISTICS_READ,
        self::LOGISTICS_WRITE,
        self::LOGISTICS_READ_LISTING,
    ])]
    private Collection $toDpps;

    /**
     * @var Collection<int, ProductStep>
     */
    #[ORM\OneToMany(targetEntity: ProductStep::class, mappedBy: 'materialsSentWith', cascade: ['persist'])]
    #[Groups([
        self::LOGISTICS_READ,
        self::LOGISTICS_WRITE,
        self::LOGISTICS_READ_LISTING,
    ])]
    private Collection $fromProductSteps;

    /**
     * @var Collection<int, ProductStep>
     */
    #[ORM\ManyToMany(targetEntity: ProductStep::class, mappedBy: 'materialsReceivedFrom', cascade: ['persist'])]
    #[Groups([
        self::LOGISTICS_READ,
        self::LOGISTICS_WRITE,
        self::LOGISTICS_READ_LISTING,
    ])]
    private Collection $toProductSteps;

    /**
     * @var Collection<int, ProductInput>
     */
    #[ORM\OneToMany(targetEntity: ProductInput::class, mappedBy: 'logistics', orphanRemoval: true)]
    #[Groups([
        self::SERIALIZABLE,
        self::LOGISTICS_READ,
    ])]
    private Collection $productInputs;

    #[ORM\Column(type: Types::STRING, options: ['default' => ''])]
    #[Groups([
        self::LOGISTICS_READ,
        self::LOGISTICS_READ_LISTING,
    ])]
    private string $timestampPath = '';

    public function __construct()
    {
        $this->fromDpps = new ArrayCollection();
        $this->toDpps = new ArrayCollection();
        $this->productInputs = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
        $this->logisticsSteps = new ArrayCollection();
        $this->startingPointCoordinates = new ArrayCollection();
        $this->fromProductSteps = new ArrayCollection();
        $this->toProductSteps = new ArrayCollection();
    }

    public function getQrFile(): ?File
    {
        return $this->qrFile;
    }

    public function setQrFile(?File $qrFile): void
    {
        $this->qrFile = $qrFile;
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
     * @return Collection<int, StartingPoint>
     */
    public function getStartingPointCoordinates(): Collection
    {

        if ($this->startingPointCoordinates === null) {
            $this->startingPointCoordinates = new ArrayCollection();
        }

        return $this->startingPointCoordinates;
    }

    public function addStartingPointCoordinate(?StartingPoint $startingPoint): self
    {
        if ($this->startingPointCoordinates === null) {
            $this->startingPointCoordinates = new ArrayCollection();
        }

        if ($startingPoint !== null && !$this->startingPointCoordinates->contains($startingPoint)) {
            $this->startingPointCoordinates->add($startingPoint);
        }

        return $this;
    }

    public function removeStartingPointCoordinate(?StartingPoint $startingPoint): self
    {
        if ($this->startingPointCoordinates === null) {
            $this->startingPointCoordinates = new ArrayCollection();
        }

        if ($startingPoint !== null) {
            $this->startingPointCoordinates->removeElement($startingPoint);
        }

        return $this;
    }

    public function getStartingCompanyName(): ?string
    {
        return $this->startingCompanyName;
    }

    public function setStartingCompanyName(?string $startingCompanyName): void
    {
        $this->startingCompanyName = $startingCompanyName;
    }

    public function getDestinationPointLat(): float
    {
        return $this->destinationPointLat;
    }

    public function setDestinationPointLat(float $destinationPointLat): void
    {
        $this->destinationPointLat = $destinationPointLat;
    }

    public function getDestinationCompanyName(): ?string
    {
        return $this->destinationCompanyName;
    }

    public function setDestinationCompanyName(?string $destinationCompanyName): void
    {
        $this->destinationCompanyName = $destinationCompanyName;
    }

    public function getDestinationPointLng(): float
    {
        return $this->destinationPointLng;
    }

    public function setDestinationPointLng(float $destinationPointLng): void
    {
        $this->destinationPointLng = $destinationPointLng;
    }

    public function getTotalDistance(): float
    {
        return $this->totalDistance;
    }

    public function setTotalDistance(float $totalDistance): void
    {
        $this->totalDistance = $totalDistance;
    }

    public function getNumberOfSteps(): int
    {
        return $this->numberOfSteps;
    }

    public function setNumberOfSteps(int $numberOfSteps): self
    {
        $this->numberOfSteps = $numberOfSteps;

        return $this;
    }

    public function getTypeOfTransport(): ?string
    {
        return $this->typeOfTransport;
    }

    public function setTypeOfTransport(?string $typeOfTransport): void
    {
        $this->typeOfTransport = $typeOfTransport;
    }

    public function getDepartureTime(): ?DateTime
    {
        return $this->departureTime;
    }

    public function setDepartureTime(?DateTime $departureTime): void
    {
        $this->departureTime = $departureTime;
    }

    public function getArrivalTime(): ?DateTime
    {
        return $this->arrivalTime;
    }

    public function setArrivalTime(?DateTime $arrivalTime): void
    {
        $this->arrivalTime = $arrivalTime;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): void
    {
        $this->company = $company;
    }

    public function getFromNode(): Node
    {
        return $this->fromNode;
    }

    public function setFromNode(Node $fromNode): void
    {
        $this->fromNode = $fromNode;
    }

    public function getToNode(): Node
    {
        return $this->toNode;
    }

    public function setToNode(Node $toNode): void
    {
        $this->toNode = $toNode;
    }

    public function getLogisticsTemplate(): ?LogisticsTemplate
    {
        return $this->logisticsTemplate;
    }

    public function setLogisticsTemplate(?LogisticsTemplate $logisticsTemplate): void
    {
        $this->logisticsTemplate = $logisticsTemplate;
    }

    public function getQrId(): ?int
    {
        return $this->qrId;
    }

    public function setQrId(?int $qrId): void
    {
        $this->qrId = $qrId;
    }

    public function getQrImage(): ?string
    {
        return $this->qrImage;
    }

    public function setQrImage(?string $qrImage): void
    {
        $this->qrImage = $qrImage;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): void
    {
        $this->state = $state;

        foreach ($this->getLogisticsSteps() as $step) {
            $step->setState($state);
        }
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): void
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

    public function getSupplyChainTemplate(): ?SupplyChainTemplate
    {
        return $this->supplyChainTemplate;
    }

    public function setSupplyChainTemplate(?SupplyChainTemplate $supplyChainTemplate): void
    {
        $this->supplyChainTemplate = $supplyChainTemplate;
    }

    /**
     * @return Collection<int, Dpp>
     */
    public function getFromDpps(): Collection
    {
        return $this->fromDpps;
    }

    public function addFromDpp(Dpp $dpp): self
    {
        if (!$this->fromDpps->contains($dpp)) {
            $this->fromDpps->add($dpp);
            $dpp->setMaterialsSentWith($this);
        }

        return $this;
    }

    public function removeFromDpp(Dpp $dpp): self
    {
        if ($this->fromDpps->contains($dpp)) {
            $this->fromDpps->removeElement($dpp);
        }

        return $this;
    }

    /**
     * @return Collection<int, Dpp>
     */
    public function getToDpps(): Collection
    {
        return $this->toDpps;
    }

    public function addToDpp(Dpp $dpp): self
    {
        if (!$this->toDpps->contains($dpp)) {
            $this->toDpps->add($dpp);

            $count = 0;

            foreach ($this->getFromDpps() as $sentDpp) {
                if ($sentDpp->getState() === Dpp::STATE_EXPORT_DPP) {
                    continue;
                }

                $count++;
            }

            foreach ($this->getFromProductSteps() as $sentProductStep) {
                if ($sentProductStep->getState() === Dpp::STATE_EXPORT_DPP) {
                    continue;
                }

                $count++;
            }

            if ($count === 0) {
                $this->setState(self::STATE_EXPORT_LOGISTICS);
            } else {
                $this->setState(self::STATE_IN_USE_LOGISTICS);
            }

            $dpp->addMaterialsReceivedFrom($this);
        }

        return $this;
    }

    public function removeToDpp(Dpp $dpp): self
    {
        if ($this->toDpps->contains($dpp)) {
            $this->toDpps->removeElement($dpp);
        }

        return $this;
    }

    /**
     * @return Collection<int, ProductStep>
     */
    public function getFromProductSteps(): Collection
    {
        return $this->fromProductSteps;
    }

    public function addFromProductStep(ProductStep $productStep): self
    {
        if (!$this->fromProductSteps->contains($productStep)) {
            $this->fromProductSteps->add($productStep);
            $productStep->setMaterialsSentWith($this);
        }

        return $this;
    }

    public function removeFromProductStep(ProductStep $productStep): self
    {
        if ($this->fromProductSteps->contains($productStep)) {
            $this->fromProductSteps->removeElement($productStep);
        }

        return $this;
    }

    /**
     * @return Collection<int, ProductStep>
     */
    public function getToProductSteps(): Collection
    {
        return $this->toProductSteps;
    }

    public function addToProductStep(ProductStep $productStep): self
    {
        if (!$this->toProductSteps->contains($productStep)) {
            $this->toProductSteps->add($productStep);

            $count = 0;

            foreach ($this->getFromDpps() as $sentDpp) {
                if ($sentDpp->getState() === Dpp::STATE_EXPORT_DPP) {
                    continue;
                }

                $count++;
            }

            foreach ($this->getFromProductSteps() as $sentProductStep) {
                if ($sentProductStep->getState() === Dpp::STATE_EXPORT_DPP) {
                    continue;
                }

                $count++;
            }

            if ($count === 0) {
                $this->setState(self::STATE_EXPORT_LOGISTICS);
            } else {
                $this->setState(self::STATE_IN_USE_LOGISTICS);
            }

            $productStep->addMaterialsReceivedFrom($this);
        }

        return $this;
    }

    public function removeToProductStep(ProductStep $productStep): self
    {
        if ($this->toProductSteps->contains($productStep)) {
            $this->toProductSteps->removeElement($productStep);
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
            $input->setLogistics($this);
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

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
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
     * @return array{id: string, firstName: string, lastName: string}
     */
    #[Groups([
        self::LOGISTICS_READ,
        self::LOGISTICS_READ_LISTING,
        self::LOGISTICS_WRITE,
    ])]
    public function getUserData(): array
    {
        return [
            'id' => (string) $this->user->getId(),
            'firstName' => $this->user->getFirstName(),
            'lastName' => $this->user->getLastName(),
        ];
    }

    public function getLogisticsParent(): ?Logistics
    {
        return $this->logisticsParent;
    }

    public function setLogisticsParent(?Logistics $logisticsParent): void
    {
        $this->logisticsParent = $logisticsParent;
    }

    /**
     * @return Collection<int, Logistics>
     */
    public function getLogisticsSteps(): Collection
    {
        return $this->logisticsSteps;
    }

    public function addLogisticsStep(Logistics $logisticsSteps): self
    {
        if (!$this->logisticsSteps->contains($logisticsSteps)) {
            $this->logisticsSteps->add($logisticsSteps);
            $logisticsSteps->setLogisticsParent($this);
        }

        return $this;
    }

    public function removeLogisticsStep(Logistics $logisticsSteps): self
    {
        if ($this->logisticsSteps->removeElement($logisticsSteps)) {
            if ($logisticsSteps->getLogisticsParent() === $this) {
                $logisticsSteps->setLogisticsParent(null);
            }
        }

        return $this;
    }

    #[Groups([
        self::LOGISTICS_READ_LISTING,
    ])]
    public function getNumberOfInputs(): ?int
    {
        $numberOfInputs = $this->productInputs->count();

        foreach ($this->logisticsSteps as $logisticsStep) {
            $numberOfInputs += $logisticsStep->getProductInputs()->count();
        }

        return $numberOfInputs + 7; // 7 is the default value that determines the number of default inputs for logistics.
    }

    #[Groups([
        Logistics::LOGISTICS_READ,
        self::LOGISTICS_READ_LISTING,
    ])]
    public function getTotalInputs(): int
    {
        $totalInputs = count($this->getProductInputs());

        foreach ($this->getLogisticsSteps() as $logisticsStep) {
            $totalInputs += count($logisticsStep->getProductInputs());
        }

        return $totalInputs += 7; // 7 is the default value that determines the number of default inputs for logistics.
    }
}

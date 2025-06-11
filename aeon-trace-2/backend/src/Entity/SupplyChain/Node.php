<?php

declare(strict_types=1);

namespace App\Entity\SupplyChain;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Api\Filter\UidFilter;
use App\Controller\Node\GetDppsByNodeIdController;
use App\Controller\Node\SourceDppNodeController;
use App\DataProviders\NodeDataProvider;
use App\DataProviders\QrEntityDataProvider;
use App\Entity\Collection\Process;
use App\Entity\Dpp\Dpp;
use App\Entity\Logistics\Logistics;
use App\Entity\Product\ProductTemplate;
use App\Entity\Quirk\HasQr;
use App\Entity\Step\ProductStep;
use App\Entity\Step\Step;
use App\Repository\SupplyChain\NodeRepository;
use App\StateProcessors\QrCreateProcessor;
use App\StateProcessors\UpdateNodeProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Attribute\MaxDepth;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Uid\Uuid;

#[ApiResource(
    operations: [
        new GetCollection(),
        new GetCollection(
            uriTemplate: '/nodes/get_dpps_by_node_ids',
            controller: GetDppsByNodeIdController::class,
            openapiContext: [
                'parameters' => [
                    [
                        'name' => 'ids',
                        'in' => 'query',
                        'description' => 'IDs of nodes',
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
        new Get(provider: NodeDataProvider::class),
        new Get(
            uriTemplate: '/nodes/{id}/steps',
            normalizationContext: ['groups' => [self::NODE_STEPS_READ]],
        ),
        new Get(
            uriTemplate: '/node/from_dpp_logistics/{id}',
            controller: SourceDppNodeController::class,
            normalizationContext: [
                self::NODE_READ,
            ],
        ),
        new Get(
            uriTemplate: '/node/getByQrId',
            openapiContext: [
                'parameters' => [
                    [
                        'name' => 'companySlug',
                        'in' => 'query',
                        'description' => 'Company slug',
                        'required' => true,
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
            provider: QrEntityDataProvider::class,
        ),
        new Get(
            uriTemplate: 'nodes/{id}/getChildNodes',
            normalizationContext: [
                'groups' => [self::CHILD_NODE_READ],
                AbstractObjectNormalizer::ENABLE_MAX_DEPTH => false,
            ],
        ),
        new Post(
            processor: QrCreateProcessor::class
        ),
        new Patch(
            processor: UpdateNodeProcessor::class,
        ),
        new Delete(),
    ],
    normalizationContext: [
        'groups' => [self::NODE_READ],
        AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true,
    ],
    denormalizationContext: ['groups' => [self::NODE_WRITE]],
)]
#[ApiFilter(filterClass: UidFilter::class, properties: ['supplyChain.id' => 'exact'])]
#[ORM\Entity(repositoryClass: NodeRepository::class)]
class Node
{
    use HasQr;

    public const URL_PATH = 'node';
    public const NODE_READ = 'node-read';
    public const NODE_WRITE = 'node-write';
    public const SUPPLY_CHAIN_NODE_READ = 'supply-chain-node-read';
    public const SUPPLY_CHAIN_TEMPLATE_NODE_READ = 'supply-chain-template-node-read';
    public const SUPPLY_CHAIN_TEMPLATE_NODE_WRITE = 'supply-chain-template-node-write';
    public const NODE_STEPS_READ = 'node-steps-read';
    public const CHILD_NODE_READ = 'child-node-read';

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups([
        self::NODE_READ,
        self::SUPPLY_CHAIN_NODE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_NODE_READ,
        Step::STEP_READ,
        self::CHILD_NODE_READ,
    ])]
    private Uuid $id;

    #[ORM\Column(type: Types::STRING, options: ['default' => ''])]
    #[Groups([
        self::NODE_READ,
        self::NODE_WRITE,
        self::SUPPLY_CHAIN_NODE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_NODE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_NODE_WRITE,
        Step::STEP_READ,
        Dpp::DPP_READ_DETAIL,
        self::CHILD_NODE_READ,
    ])]
    private string $name = '';

    #[ORM\Column(type: Types::TEXT, options: ['default' => ''])]
    #[Groups([
        self::NODE_READ,
        self::NODE_WRITE,
        self::SUPPLY_CHAIN_TEMPLATE_NODE_READ,
        Step::STEP_READ,
    ])]
    private string $description = '';

    #[ORM\ManyToOne(targetEntity: Process::class, inversedBy: 'nodes')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    #[Groups([
        self::NODE_READ,
        self::NODE_WRITE,
        self::SUPPLY_CHAIN_NODE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_NODE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_NODE_WRITE,
        Step::STEP_READ,
        Dpp::DPP_READ_DETAIL,
    ])]
    private Process $typeOfProcess;

    /**
     * @var Collection<int, Node>
     */
    #[ORM\ManyToMany(targetEntity: Node::class, inversedBy: 'children', cascade: ['persist'])]
    #[ORM\JoinTable(name: 'node_parents_node_children')]
    #[Groups([
        self::NODE_READ,
        self::NODE_WRITE,
        self::SUPPLY_CHAIN_NODE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_NODE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_NODE_WRITE,
        Step::STEP_READ,
        Dpp::DPP_READ_DETAIL,
    ])]
    #[MaxDepth(1)]
    private Collection $parents;

    /**
     * @var Collection<int, Node>
     */
    #[ORM\ManyToMany(targetEntity: Node::class, mappedBy: 'parents', cascade: ['persist'])]
    #[Groups([
        self::NODE_READ,
        self::NODE_WRITE,
        self::SUPPLY_CHAIN_NODE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_NODE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_NODE_WRITE,
        Step::STEP_READ,
        self::CHILD_NODE_READ,
    ])]
    #[MaxDepth(1)]
    private Collection $children;

    /**
     * @var Collection<int, Node>
     */
    #[ORM\ManyToMany(targetEntity: Node::class)]
    #[Groups([
        self::NODE_READ,
        self::NODE_WRITE,
        self::SUPPLY_CHAIN_TEMPLATE_NODE_WRITE,
        Step::STEP_READ,
    ])]
    private Collection $siblings;

    /**
     * @var Collection<int, Dpp>
     */
    #[ORM\OneToMany(targetEntity: Dpp::class, mappedBy: 'node', cascade: ['persist'])]
    #[Groups([
        self::SUPPLY_CHAIN_NODE_READ,
    ])]
    private Collection $dpps;

    /**
     * @var Collection<int, ProductStep>
     */
    #[ORM\OneToMany(targetEntity: ProductStep::class, mappedBy: 'node', cascade: ['persist'])]
    #[Groups([
        self::NODE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_NODE_READ,
    ])]
    private Collection $productSteps;

    #[ORM\ManyToOne(targetEntity: SupplyChainTemplate::class, inversedBy: 'nodes')]
    #[Groups([
        self::NODE_READ,
        self::SUPPLY_CHAIN_NODE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_NODE_READ,
        self::NODE_WRITE,
        Step::STEP_READ,
    ])]
    private SupplyChainTemplate $supplyChainTemplate;

    /**
     * @var Collection<int, ProductTemplate>
     */
    #[ORM\ManyToMany(targetEntity: ProductTemplate::class, inversedBy: 'nodes', cascade: ['persist'])]
    #[Groups([
        self::NODE_READ,
        self::NODE_WRITE,
        self::SUPPLY_CHAIN_NODE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_NODE_READ,
    ])]
    private Collection $productTemplates;

    #[ORM\ManyToOne(targetEntity: ProductTemplate::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups([
        self::NODE_READ,
        self::NODE_WRITE,
        self::SUPPLY_CHAIN_NODE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_NODE_READ,
    ])]
    private ?ProductTemplate $nodeTemplate = null;

    /**
     * @var Collection<int, Step>
     */
    #[ORM\ManyToMany(targetEntity: Step::class, inversedBy: 'nodes')]
    #[Groups([
        self::NODE_READ,
        self::NODE_WRITE,
        self::NODE_STEPS_READ,
        self::SUPPLY_CHAIN_NODE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_NODE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_NODE_WRITE,
    ])]
    #[MaxDepth(1)]
    private Collection $steps;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Groups([
        self::NODE_READ,
        self::NODE_WRITE,
        self::SUPPLY_CHAIN_NODE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_NODE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_NODE_WRITE,
        Step::STEP_READ,
    ])]
    private ?int $qrId = null;

    /**
     * @var Collection<int, Logistics>
     */
    #[ORM\OneToMany(targetEntity: Logistics::class, mappedBy: 'fromNode', cascade: ['persist'], orphanRemoval: true)]
    #[Groups([
        self::NODE_READ,
        self::NODE_WRITE,
        self::SUPPLY_CHAIN_NODE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_NODE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_NODE_WRITE,
    ])]
    private Collection $fromNodeLogistics;

    /**
     * @var Collection<int, Logistics>
     */
    #[ORM\OneToMany(targetEntity: Logistics::class, mappedBy: 'toNode', cascade: ['persist'], orphanRemoval: true)]
    #[Groups([
        self::NODE_READ,
        self::NODE_WRITE,
        self::SUPPLY_CHAIN_NODE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_NODE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_NODE_WRITE,
    ])]
    private Collection $toNodeLogistics;

    /**
     * @var array<string, int>
     */
    #[Groups([
        self::NODE_READ,
        self::SUPPLY_CHAIN_NODE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_NODE_READ,
        Dpp::DPP_READ_DETAIL,
        ProductStep::STEP_READ,
    ])]
    private array $countDpp = [];

    /**
     * @var array<string, array{count: int, data: array<int, Dpp>}>
     */
    #[Groups([
        self::NODE_READ,
        self::SUPPLY_CHAIN_NODE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_NODE_READ,
        Dpp::DPP_READ_DETAIL,
        ProductStep::STEP_READ,
    ])]
    private ?array $countDppData = null;

    /**
     * @var array<string, array<Logistics>|int<0, max>>
     */
    #[Groups([
        self::NODE_READ,
        self::SUPPLY_CHAIN_NODE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_NODE_READ,
    ])]
    private array $countLogistics = [];

    private bool $existAssignedDpp = false;

    private bool $existLogisticsAssignedToDpp = false;

    #[ORM\OneToOne(targetEntity: NodePosition::class, mappedBy: 'node', cascade: ['persist', 'remove'])]
    #[Groups([
        self::NODE_READ,
        self::NODE_WRITE,
        self::SUPPLY_CHAIN_NODE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_NODE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_NODE_WRITE,
        NodePosition::NODE_POSITION_READ,
        NodePosition::NODE_POSITION_WRITE,
    ])]
    private ?NodePosition $nodePosition = null;

    public function __toString(): string
    {
        return $this->getName();
    }

    public function __construct()
    {
        $this->parents = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->siblings = new ArrayCollection();
        $this->steps = new ArrayCollection();
        $this->fromNodeLogistics = new ArrayCollection();
        $this->toNodeLogistics = new ArrayCollection();
        $this->productTemplates = new ArrayCollection();
        $this->productSteps = new ArrayCollection();
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

    public function getTypeOfProcess(): Process
    {
        return $this->typeOfProcess;
    }

    public function setTypeOfProcess(Process $typeOfProcess): void
    {
        $this->typeOfProcess = $typeOfProcess;
    }

    /**
     * @return Collection<int, Node>
     */
    public function getParents(): Collection
    {
        return $this->parents;
    }

    public function addParent(Node $node): self
    {
        if (!$this->parents->contains($node)) {
            $this->parents->add($node);
            $node->addChild($this);
        }

        return $this;
    }

    public function removeParent(Node $node): self
    {
        if ($this->parents->contains($node)) {
            $this->parents->removeElement($node);
            $node->removeChild($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Node>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(Node $node): self
    {
        if (!$this->children->contains($node)) {
            $this->children->add($node);
            $node->addParent($this);
        }

        return $this;
    }

    public function removeChild(Node $node): self
    {
        if ($this->children->contains($node)) {
            $this->children->removeElement($node);
            $node->removeParent($this);
        }

        return $this;
    }

    public function getSupplyChainTemplate(): SupplyChainTemplate
    {
        return $this->supplyChainTemplate;
    }

    public function setSupplyChainTemplate(SupplyChainTemplate $supplyChainTemplate): void
    {
        $this->supplyChainTemplate = $supplyChainTemplate;
    }

    /**
     * @return Collection<int, ProductTemplate>
     */
    public function getProductTemplates(): Collection
    {
        return $this->productTemplates;
    }

    public function addProductTemplate(ProductTemplate $productTemplate): self
    {
        if (!$this->productTemplates->contains($productTemplate)) {
            $this->productTemplates->add($productTemplate);
            $productTemplate->addNode($this);
        }

        return $this;
    }

    public function removeProductTemplate(ProductTemplate $productTemplate): self
    {
        if ($this->productTemplates->contains($productTemplate)) {
            $this->productTemplates->removeElement($productTemplate);
        }

        return $this;
    }

    public function getNodeTemplate(): ?ProductTemplate
    {
        return $this->nodeTemplate;
    }

    public function setNodeTemplate(?ProductTemplate $nodeTemplate): self
    {
        $this->nodeTemplate = $nodeTemplate;

        return $this;
    }

    /**
     * @return array<int, array{
     *     id: string,
     *     name: string,
     *     longitude: float|null,
     *     latitude: float|null,
     *     isLogisticsCompany: bool,
     *     sites: array<int, array{
     *         id: string,
     *         name: string,
     *         longitude: float|null,
     *         latitude: float|null
     *     }>
     * }>
     */
    #[Groups([
        self::NODE_READ,
        self::SUPPLY_CHAIN_NODE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_NODE_READ,
    ])]
    public function getCompaniesFromProductTemplates(): array
    {
        $companies = [];

        foreach ($this->productTemplates as $productTemplate) {
            foreach ($productTemplate->getCompanies() as $company) {
                $companyId = (string) $company->getId();

                if (!array_key_exists($companyId, $companies)) {
                    $companies[$companyId] = [
                        'id' => $companyId,
                        'name' => $company->getName(),
                        'longitude' => $company->getLongitude(),
                        'latitude' => $company->getLatitude(),
                        'isLogisticsCompany' => $company->isLogisticsCompany(),
                        'sites' => [],
                    ];
                }

                foreach ($company->getSites() as $site) {
                    $siteId = (string) $site->getId();

                    if (!array_key_exists($siteId, array_column($companies[$companyId]['sites'], 'id'))) {
                        $companies[$companyId]['sites'][] = [
                            'id' => $siteId,
                            'name' => $site->getName(),
                            'longitude' => $site->getLongitude(),
                            'latitude' => $site->getLatitude(),
                        ];
                    }
                }
            }
        }

        return array_values($companies);
    }

    /**
     * @return Collection<int, Node>
     */
    public function getSiblings(): Collection
    {
        return $this->siblings;
    }

    /**
     * @return array<int, string>
     */
    #[Groups([
        self::NODE_READ,
        self::SUPPLY_CHAIN_NODE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_NODE_READ,
    ])]
    public function getSiblingIds(): array
    {
        return $this->siblings->map(fn (Node $sibling) => (string) $sibling->getId())->toArray();
    }

    public function addSibling(Node $sibling): self
    {
        if (!$this->siblings->contains($sibling)) {
            $this->siblings[] = $sibling;
            $sibling->addSibling($this);
        }

        return $this;
    }

    public function removeSibling(Node $sibling): self
    {
        if ($this->siblings->removeElement($sibling)) {
            $sibling->removeSibling($this);
        }

        return $this;
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

    /**
     * @return Collection<int, Logistics>
     */
    public function getFromNodeLogistics(): Collection
    {
        return $this->fromNodeLogistics->filter(function ($logistics) {
            return $logistics->getLogisticsParent() !== null;
        });
    }

    public function addFromNodeLogistics(Logistics $logistics): self
    {
        if (!$this->fromNodeLogistics->contains($logistics)) {
            $this->fromNodeLogistics->add($logistics);
            $logistics->setFromNode($this);
        }

        return $this;
    }

    public function removeFromNodeLogistics(Logistics $logistics): self
    {
        if ($this->fromNodeLogistics->contains($logistics)) {
            $this->fromNodeLogistics->removeElement($logistics);
        }

        return $this;
    }

    /**
     * @return Collection<int, Logistics>
     */
    public function getToNodeLogistics(): Collection
    {
        return $this->toNodeLogistics;
    }

    public function addToNodeLogistics(Logistics $logistics): self
    {
        if (!$this->fromNodeLogistics->contains($logistics)) {
            $this->fromNodeLogistics->add($logistics);
            $logistics->setToNode($this);
        }

        return $this;
    }

    public function removeToNodeLogistics(Logistics $logistics): self
    {
        if ($this->toNodeLogistics->contains($logistics)) {
            $this->toNodeLogistics->removeElement($logistics);
        }

        return $this;
    }

    /**
     * @return array<string, int>
     */
    public function getCountDpp(): array
    {
        return $this->countDpp;
    }

    /**
     * @param array<string, int> $countDpp
     */
    public function setCountDpp(array $countDpp): void
    {
        $this->countDpp = $countDpp;
    }

    /**
     * @return array<string, array{count: int, data: array<int, Dpp>}>|null
     */
    public function getCountDppData(): ?array
    {
        return $this->countDppData;
    }

    /**
     * @param array<string, array{count: int, data: array<int, Dpp>}>|null $countDppData
     */
    public function setCountDppData(?array $countDppData): void
    {
        $this->countDppData = $countDppData;
    }

    /**
     * @return array<string, array<Logistics>|int<0, max>>
     */
    public function getCountLogistics(): array
    {
        return $this->countLogistics;
    }

    /**
     * @param array<string, array<Logistics>|int<0, max>> $countLogistics
     */
    public function setCountLogistics(array $countLogistics): void
    {
        $this->countLogistics = $countLogistics;
    }

    public function isExistAssignedDpp(): bool
    {
        return $this->existAssignedDpp;
    }

    public function setExistAssignedDpp(bool $existAssignedDpp): void
    {
        $this->existAssignedDpp = $existAssignedDpp;
    }

    public function isExistLogisticsAssignedToDpp(): bool
    {
        return $this->existLogisticsAssignedToDpp;
    }

    public function setExistLogisticsAssignedToDpp(bool $existLogisticsAssignedToDpp): void
    {
        $this->existLogisticsAssignedToDpp = $existLogisticsAssignedToDpp;
    }
    public function getNodePosition(): ?NodePosition
    {
        return $this->nodePosition;
    }

    public function setNodePosition(?NodePosition $nodePosition): self
    {
        if ($nodePosition && $nodePosition->getNode() !== $this) {
            $nodePosition->setNode($this);
        }

        $this->nodePosition = $nodePosition;

        return $this;
    }

    /**
     * @return Collection<int,Dpp>
     */
    public function getDpps(): Collection
    {
        return $this->dpps;
    }

    public function addDpp(Dpp $dpp): void
    {
        if (!$this->dpps->contains($dpp)) {
            $this->dpps[] = $dpp;
            $dpp->setNode($this);
        }
    }

    /**
     * @return Collection<int, ProductStep>
     */
    public function getProductSteps(): Collection
    {
        return $this->productSteps;
    }

    public function addProductStep(ProductStep $productStep): void
    {
        if (!$this->productSteps->contains($productStep)) {
            $this->productSteps->add($productStep);
            $productStep->setNode($this);
        }
    }
}

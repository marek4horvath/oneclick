<?php

declare(strict_types=1);

namespace App\Entity\SupplyChain;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Api\Filter\DateNullFilter;
use App\Api\Filter\SupplyChainNodesLengthOrderFilter;
use App\DataProviders\SupplyChainDataProvider;
use App\DataProviders\VerifySupplyChainDataProvider;
use App\Entity\Collection\Process;
use App\Entity\Dpp\Dpp;
use App\Entity\Logistics\Logistics;
use App\Entity\Product\Input;
use App\Entity\Product\InputCategory;
use App\Entity\Product\ProductTemplate;
use App\Entity\Step\Step;
use App\Entity\Step\StepsTemplate;
use App\Repository\SupplyChain\SupplyChainTemplateRepository;
use App\StateProcessors\DeleteSupplyChainTemplateProcessor;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\MaxDepth;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Uid\Uuid;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/supply_chain_templates/algorithms/{id}',
            normalizationContext: [
                'groups' => [self::SUPPLY_CHAIN_TEMPLATE_ALGORITHM_READ],
            ]
        ),
        new GetCollection(
            normalizationContext: [
                'groups' => [self::SUPPLY_CHAIN_TEMPLATE_COLLECTION],
            ]
        ),
        new Get(
            normalizationContext: [
                'groups' => [
                    self::SUPPLY_CHAIN_TEMPLATE_CHAIN,
                    Node::SUPPLY_CHAIN_TEMPLATE_NODE_READ,
                    Step::SUPPLY_CHAIN_TEMPLATE_STEP_READ,
                    Input::SUPPLY_CHAIN_TEMPLATE_INPUT_READ,
                    InputCategory::INPUT_INPUT_CATEGORY_READ,
                ],
                AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true,
            ],
            provider: SupplyChainDataProvider::class
        ),
        new Get(
            uriTemplate: '/supply_chain_templates/detail/{id}',
            normalizationContext: [
                'groups' => [
                    self::SUPPLY_CHAIN_TEMPLATE_READ_DETAIL,
                    Node::SUPPLY_CHAIN_TEMPLATE_NODE_READ,
                    Step::SUPPLY_CHAIN_TEMPLATE_STEP_READ,
                    Input::SUPPLY_CHAIN_TEMPLATE_INPUT_READ,
                    InputCategory::INPUT_INPUT_CATEGORY_READ,
                ],
                AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true,
            ],
            provider: SupplyChainDataProvider::class
        ),
        new Get(
            uriTemplate: '/supply_chain/{id}',
            shortName: 'SupplyChain',
            normalizationContext: [
                'groups' => [
                    self::SUPPLY_CHAIN_CHAIN,
                    Node::SUPPLY_CHAIN_NODE_READ,
                    Step::SUPPLY_CHAIN_TEMPLATE_STEP_READ,
                    Input::SUPPLY_CHAIN_TEMPLATE_INPUT_READ,
                    InputCategory::INPUT_INPUT_CATEGORY_READ,
                ],
            ],
            provider: SupplyChainDataProvider::class
        ),
        new Get(
            uriTemplate: '/supply_chain_templates/verify/{id}',
            provider: VerifySupplyChainDataProvider::class
        ),
        new Post(
            normalizationContext: ['groups' => [self::SUPPLY_CHAIN_ID_READ]],
        ),
        new Patch(
            normalizationContext: [
                'groups' => [
                    self::SUPPLY_CHAIN_TEMPLATE_READ,
                    Step::SUPPLY_CHAIN_TEMPLATE_STEP_READ,
                    Input::SUPPLY_CHAIN_TEMPLATE_INPUT_READ,
                    ProductTemplate::SUPPLY_CHAIN_TEMPLATE_PRODUCT_TEMPLATE_READ,
                    StepsTemplate::SUPPLY_CHAIN_TEMPLATE_STEP_TEMPLATE_READ,
                ],
            ],
        ),
        new Delete(
            normalizationContext: ['groups' => [self::SUPPLY_CHAIN_ID_READ]],
            processor: DeleteSupplyChainTemplateProcessor::class,
        ),
    ],
    normalizationContext: [
        'groups' => [
            self::SUPPLY_CHAIN_TEMPLATE_COLLECTION,
            self::SUPPLY_CHAIN_TEMPLATE_READ,
            Node::SUPPLY_CHAIN_TEMPLATE_NODE_READ,
            Step::SUPPLY_CHAIN_TEMPLATE_STEP_READ,
            Input::SUPPLY_CHAIN_TEMPLATE_INPUT_READ,
            ProductTemplate::SUPPLY_CHAIN_TEMPLATE_PRODUCT_TEMPLATE_READ,
            StepsTemplate::SUPPLY_CHAIN_TEMPLATE_STEP_TEMPLATE_READ,
            Process::PROCESS_READ,
        ],
        AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true,
    ],
    denormalizationContext: [
        'groups' => [
            self::SUPPLY_CHAIN_TEMPLATE_WRITE,
            Node::SUPPLY_CHAIN_TEMPLATE_NODE_WRITE,
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
#[ORM\Entity(repositoryClass: SupplyChainTemplateRepository::class)]
#[ApiFilter(DateNullFilter::class, properties: ['deletedAt'])]
#[ApiFilter(OrderFilter::class, properties: ['name'])]
#[ApiFilter(SupplyChainNodesLengthOrderFilter::class, properties: ['order[numberOfNodes]'])]
#[ApiFilter(SearchFilter::class, properties: ['name' => 'partial'])]
class SupplyChainTemplate
{
    public const SUPPLY_CHAIN_ID_READ = 'supply-chain-id-read';
    public const SUPPLY_CHAIN_TEMPLATE_COLLECTION = 'supply-chain-template-collection';
    public const SUPPLY_CHAIN_CHAIN = 'supply-chain-chain';
    public const SUPPLY_CHAIN_TEMPLATE_CHAIN = 'supply-chain-template-chain';
    public const SUPPLY_CHAIN_TEMPLATE_READ = 'supply-chain-template-read';
    public const SUPPLY_CHAIN_TEMPLATE_READ_DETAIL = 'supply-chain-template-read-detail';
    public const SUPPLY_CHAIN_TEMPLATE_WRITE = 'supply-chain-template-write';
    public const SUPPLY_CHAIN_TEMPLATE_ALGORITHM_READ = 'supply-chain-template-algorithm-read';

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups([
        self::SUPPLY_CHAIN_TEMPLATE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_READ_DETAIL,
        self::SUPPLY_CHAIN_CHAIN,
        self::SUPPLY_CHAIN_TEMPLATE_CHAIN,
        self::SUPPLY_CHAIN_TEMPLATE_COLLECTION,
        self::SUPPLY_CHAIN_ID_READ,
    ])]
    private Uuid $id;

    #[ORM\Column(type: Types::STRING, options: ['default' => ''])]
    #[Groups([
        self::SUPPLY_CHAIN_TEMPLATE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_READ_DETAIL,
        self::SUPPLY_CHAIN_CHAIN,
        self::SUPPLY_CHAIN_TEMPLATE_CHAIN,
        self::SUPPLY_CHAIN_TEMPLATE_WRITE,
        self::SUPPLY_CHAIN_TEMPLATE_COLLECTION,
    ])]
    private string $name = '';

    /**
     * @var Collection<int, Node> $nodes
     */
    #[ORM\OneToMany(targetEntity: Node::class, mappedBy: 'supplyChainTemplate', cascade: ['persist'], orphanRemoval: true)]
    #[Groups([
        self::SUPPLY_CHAIN_TEMPLATE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_READ_DETAIL,
        self::SUPPLY_CHAIN_CHAIN,
        self::SUPPLY_CHAIN_TEMPLATE_CHAIN,
        self::SUPPLY_CHAIN_TEMPLATE_WRITE,
    ])]
    #[MaxDepth(1)]
    private Collection $nodes;

    /**
     * @var Collection<int, Dpp>
     */
    #[ORM\OneToMany(targetEntity: Dpp::class, mappedBy: 'supplyChainTemplate', cascade: ['persist'])]
    #[Groups([
        self::SUPPLY_CHAIN_TEMPLATE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_CHAIN,
    ])]
    private Collection $dpps;

    /**
     * @var Collection<int, Logistics>
     */
    #[ORM\OneToMany(targetEntity: Logistics::class, mappedBy: 'supplyChainTemplate', cascade: ['persist'])]
    #[Groups([
        self::SUPPLY_CHAIN_TEMPLATE_READ,
    ])]
    private Collection $logistics;

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['default' => null])]
    private ?int $qrFolderId = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['default' => null])]
    #[Groups([
        self::SUPPLY_CHAIN_TEMPLATE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_COLLECTION,
    ])]
    private ?DateTimeImmutable $deletedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['default' => null])]
    #[Groups([
        self::SUPPLY_CHAIN_TEMPLATE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_COLLECTION,
    ])]
    private ?DateTimeImmutable $createdAt;

    /**
     * @var Collection<int, ProductTemplate>
     */
    #[ORM\ManyToMany(targetEntity: ProductTemplate::class, inversedBy: 'supplyChainTemplates', cascade: ['persist'])]
    #[Groups([
        self::SUPPLY_CHAIN_TEMPLATE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_READ_DETAIL,
        self::SUPPLY_CHAIN_CHAIN,
        self::SUPPLY_CHAIN_TEMPLATE_CHAIN,
        self::SUPPLY_CHAIN_TEMPLATE_WRITE,
        self::SUPPLY_CHAIN_TEMPLATE_COLLECTION,
    ])]
    private Collection $productTemplates;

    /**
     * @var Collection<int, ProductTemplate>
     */
    #[ORM\OneToMany(targetEntity: ProductTemplate::class, mappedBy: 'supplyChainTemplate', cascade: ['persist'], orphanRemoval: true)]
    #[Groups([
        self::SUPPLY_CHAIN_TEMPLATE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_READ_DETAIL,
        self::SUPPLY_CHAIN_CHAIN,
        self::SUPPLY_CHAIN_TEMPLATE_CHAIN,
        self::SUPPLY_CHAIN_TEMPLATE_WRITE,
        self::SUPPLY_CHAIN_TEMPLATE_COLLECTION,
    ])]
    private Collection $nodeTemplates;

    /**
     * @var Collection<int, SupplyChainAlgorithm>
     */
    #[ORM\OneToMany(targetEntity: SupplyChainAlgorithm::class, mappedBy: 'supplyChainTemplate', orphanRemoval: true)]
    #[Groups([self::SUPPLY_CHAIN_TEMPLATE_ALGORITHM_READ])]
    private Collection $supplyChainAlgorithms;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->nodes = new ArrayCollection();
        $this->dpps = new ArrayCollection();
        $this->logistics = new ArrayCollection();
        $this->productTemplates = new ArrayCollection();
        $this->nodeTemplates = new ArrayCollection();
        $this->supplyChainAlgorithms = new ArrayCollection();
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

    /**
     * @return Collection<int, Node>
     */
    public function getNodes(): Collection
    {
        return $this->nodes;
    }

    public function addNode(Node $node): self
    {
        if (!$this->nodes->contains($node)) {
            $this->nodes->add($node);
            $node->setSupplyChainTemplate($this);
        }

        return $this;
    }

    public function removeNode(Node $node): self
    {
        if ($this->nodes->contains($node)) {
            $this->nodes->removeElement($node);
        }

        return $this;
    }

    /**
     * @return Collection<int, Dpp>
     */
    public function getDpps(): Collection
    {
        return $this->dpps;
    }

    public function addDpp(Dpp $dpp): self
    {
        if (!$this->dpps->contains($dpp)) {
            $this->dpps->add($dpp);
            $dpp->setSupplyChainTemplate($this);
        }

        return $this;
    }

    public function removeDpp(Dpp $dpp): self
    {
        if ($this->dpps->contains($dpp)) {
            $this->dpps->removeElement($dpp);
        }

        return $this;
    }

    /**
     * @return Collection<int, Logistics>
     */
    public function getLogistics(): Collection
    {
        return $this->logistics;
    }

    public function addLogistics(Logistics $logistics): self
    {
        if (!$this->logistics->contains($logistics)) {
            $this->logistics->add($logistics);
            $logistics->setSupplyChainTemplate($this);
        }

        return $this;
    }

    public function removeLogistics(Logistics $logistics): self
    {
        if ($this->logistics->contains($logistics)) {
            $this->logistics->removeElement($logistics);
        }

        return $this;
    }


    public function getQrFolderId(): ?int
    {
        return $this->qrFolderId;
    }

    public function setQrFolderId(?int $qrFolderId): void
    {
        $this->qrFolderId = $qrFolderId;
    }

    public function getDeletedAt(): ?DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?DateTimeImmutable $deletedAt): void
    {
        $this->deletedAt = $deletedAt;
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
     * @return Collection<int, ProductTemplate>
     */
    public function getProductTemplates(): Collection
    {
        return $this->productTemplates;
    }

    /**
     * @param Collection<int, ProductTemplate> $productTemplates
     */
    public function setProductTemplates(Collection $productTemplates): self
    {
        foreach ($productTemplates as $productTemplate) {
            $this->addProductTemplate($productTemplate);
        }

        return $this;
    }

    public function addProductTemplate(ProductTemplate $productTemplate): self
    {
        if (!$this->productTemplates->contains($productTemplate)) {
            $this->productTemplates->add($productTemplate);

            if (!$productTemplate->getSupplyChainTemplates()->contains($this)) {
                $productTemplate->addSupplyChainTemplate($this);
            }
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

    /**
     * @return Collection<int, ProductTemplate>
     */
    public function getNodeTemplates(): Collection
    {
        return $this->nodeTemplates;
    }

    public function addNodeTemplate(ProductTemplate $productTemplate): self
    {
        if (!$this->nodeTemplates->contains($productTemplate)) {
            $this->nodeTemplates->add($productTemplate);
            $productTemplate->setSupplyChainTemplate($this);
        }

        return $this;
    }

    public function nodeTemplate(ProductTemplate $productTemplate): self
    {
        if ($this->nodeTemplates->contains($productTemplate)) {
            $this->nodeTemplates->removeElement($productTemplate);
        }

        return $this;
    }

    /**
     * @return Collection<int, SupplyChainAlgorithm>
     */
    public function getSupplyChainAlgorithms(): Collection
    {
        return $this->supplyChainAlgorithms;
    }

    public function addSupplyChainAlgorithm(SupplyChainAlgorithm $supplyChainAlgorithm): self
    {
        if (!$this->supplyChainAlgorithms->contains($supplyChainAlgorithm)) {
            $this->supplyChainAlgorithms->add($supplyChainAlgorithm);
            $supplyChainAlgorithm->setSupplyChainTemplate($this);
        }

        return $this;
    }

    public function removeSupplyChainAlgorithm(SupplyChainAlgorithm $supplyChainAlgorithm): self
    {
        if ($this->supplyChainAlgorithms->contains($supplyChainAlgorithm)) {
            $this->supplyChainAlgorithms->removeElement($supplyChainAlgorithm);
        }

        return $this;
    }


    #[Groups([self::SUPPLY_CHAIN_TEMPLATE_COLLECTION])]
    public function getNumberOfNodes(): ?int
    {
        return $this->nodes->count();
    }
}

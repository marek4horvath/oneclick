<?php

declare(strict_types=1);

namespace App\Entity\Product;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Api\Filter\ProductTemplateInputsLengthOrderFilter;
use App\Api\Filter\ProductTemplateStepsLengthOrderFilter;
use App\Api\Filter\UidFilter;
use App\Controller\ProductTemplate\ProductTemplateController;
use App\Controller\ProductTemplate\UnlinkCompanyFromProductTemplateController;
use App\Entity\Company\Company;
use App\Entity\Quirk\HasName;
use App\Entity\Quirk\HasUid;
use App\Entity\Step\Step;
use App\Entity\Step\StepPosition;
use App\Entity\Step\StepsTemplate;
use App\Entity\SupplyChain\Node;
use App\Entity\SupplyChain\SupplyChainTemplate;
use App\Repository\Product\ProductTemplateRepository;
use App\StateProcessors\CreateProductTemplateProcessor;
use App\StateProcessors\FileDeleteProcessor;
use App\StateProcessors\ImageUploadProcessor;
use App\StateProcessors\ProductTemplateDeleteProcessor;
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
        new GetCollection(),
        new GetCollection(
            uriTemplate: '/product_templates/listing',
            normalizationContext: [
                'groups' => [
                    self::PRODUCT_TEMPLATE_READ_LISTING,
                ],
            ],
        ),
        new GetCollection(
            uriTemplate: '/product_templates/by_ids',
            controller: ProductTemplateController::class,
            openapiContext: [
                'parameters' => [
                    [
                        'name' => 'ids',
                        'in' => 'query',
                        'description' => 'IDs of product templates',
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
            uriTemplate: '/product_templates/companies/{id}',
            normalizationContext: [
                'groups' => [
                    self::PRODUCT_TEMPLATE_COMPANIES,
                    Company::COMPANY_NAME,
                    Company::COMPANY_SITES,
                ],
            ],
        ),
        new Post(
            uriTemplate: '/product_templates/{id}/product_image',
            inputFormats: ['multipart' => ['multipart/form-data']],
            denormalizationContext: ['groups' => [self::PRODUCT_TEMPLATE_IMAGE_UPLOAD]],
            security: "is_granted('ROLE_COMPANY_MANAGER')",
            processor: ImageUploadProcessor::class,
        ),
        new Post(
            openapiContext: [
                'parameters' => [
                    [
                        'name' => 'createStep',
                        'in' => 'query',
                        'description' => 'Determines if new step should be created with product (true or false)',
                        'required' => false,
                        'type' => 'integer',
                    ],
                ],
            ],
            security: "is_granted('ROLE_COMPANY_MANAGER')",
            processor: CreateProductTemplateProcessor::class,
        ),
        new Patch(
            security: "is_granted('ROLE_COMPANY_MANAGER')",
        ),

        new Patch(
            uriTemplate: '/product_templates/{id}/unlink_company',
            controller: UnlinkCompanyFromProductTemplateController::class,
            openapiContext: [
                'parameters' => [
                    [
                        'name' => 'ids',
                        'in' => 'query',
                        'description' => 'IDs of companies',
                        'required' => true,
                        'type' => 'array',
                        'items' => [
                            'type' => 'string',
                        ],
                    ],
                ],
            ],
        ),

        new Delete(
            uriTemplate: '/product_templates/{id}/product_image',
            security: "is_granted('ROLE_COMPANY_MANAGER')",
            processor: FileDeleteProcessor::class,
        ),
        new Delete(
            security: "is_granted('ROLE_COMPANY_MANAGER')",
            processor: ProductTemplateDeleteProcessor::class,
        ),
    ],
    normalizationContext: [
        'groups' => [
            self::PRODUCT_TEMPLATE_READ,
            StepsTemplate::STEP_TEMPLATE_READ,
            Input::STEP_INPUT_READ,
            Step::PRODUCT_TEMPLATE_STEP_READ,
            StepPosition::STEP_POSITION_READ,
        ],
    ],
    denormalizationContext: [
        'groups' => [
            self::PRODUCT_TEMPLATE_WRITE,
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
#[ApiFilter(filterClass: UidFilter::class, properties: ['companies.id' => 'exact'])]
#[ApiFilter(SearchFilter::class, properties: ['name' => 'partial'])]
#[ApiFilter(OrderFilter::class, properties: ['name'])]
#[ApiFilter(ProductTemplateStepsLengthOrderFilter::class, properties: ['order[steps]'])]
#[ApiFilter(ProductTemplateInputsLengthOrderFilter::class, properties: ['order[inputs]'])]
#[ORM\Entity(repositoryClass: ProductTemplateRepository::class)]
#[Vich\Uploadable]
class ProductTemplate
{
    use HasUid;
    use HasName;

    public const PRODUCT_TEMPLATE_READ = 'product-template-read';
    public const PRODUCT_TEMPLATE_READ_LISTING = 'product-template-read-listing';
    public const PRODUCT_TEMPLATE_COMPANIES = 'product-template-companies';
    public const PRODUCT_TEMPLATE_WRITE = 'product-template-write';
    public const SUPPLY_CHAIN_TEMPLATE_PRODUCT_TEMPLATE_READ = 'supply-chain-template-product-template-read';
    public const PRODUCT_TEMPLATE_IMAGE_UPLOAD = 'product-template-image-upload';

    #[Vich\UploadableField(mapping: 'product_images', fileNameProperty: 'productImage')]
    #[Groups([self::PRODUCT_TEMPLATE_IMAGE_UPLOAD])]
    public ?File $file = null;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups([
        self::PRODUCT_TEMPLATE_READ,
        self::PRODUCT_TEMPLATE_READ_LISTING,
        Company::COMPANY_READ,
        self::SUPPLY_CHAIN_TEMPLATE_PRODUCT_TEMPLATE_READ,
    ])]
    private Uuid $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups([
        self::PRODUCT_TEMPLATE_READ,
        self::PRODUCT_TEMPLATE_READ_LISTING,
        self::PRODUCT_TEMPLATE_WRITE,
        Company::COMPANY_READ,
        self::SUPPLY_CHAIN_TEMPLATE_PRODUCT_TEMPLATE_READ,
    ])]
    private string $name = '';

    #[ORM\Column(type: Types::STRING, nullable: true, options: [
        'default' => '',
    ])]
    #[Groups([
        self::PRODUCT_TEMPLATE_READ,
        Company::COMPANY_READ,
        self::SUPPLY_CHAIN_TEMPLATE_PRODUCT_TEMPLATE_READ,
        self::PRODUCT_TEMPLATE_READ_LISTING,
    ])]
    private ?string $productImage = '';

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    #[Groups([
        self::PRODUCT_TEMPLATE_READ,
        self::PRODUCT_TEMPLATE_WRITE,
    ])]
    private bool $haveDpp = false;

    /**
     * @var Collection<int, Company>
     */
    #[ORM\ManyToMany(targetEntity: Company::class, mappedBy: 'productTemplates')]
    #[Groups([
        self::PRODUCT_TEMPLATE_READ,
        self::PRODUCT_TEMPLATE_COMPANIES,
        self::PRODUCT_TEMPLATE_WRITE,
    ])]
    private Collection $companies;

    #[ORM\ManyToOne(targetEntity: StepsTemplate::class, fetch: 'EAGER')]
    #[ORM\JoinColumn]
    #[Groups([
        self::PRODUCT_TEMPLATE_READ,
        self::PRODUCT_TEMPLATE_WRITE,
        Company::COMPANY_READ,
        self::SUPPLY_CHAIN_TEMPLATE_PRODUCT_TEMPLATE_READ,
        Step::PRODUCT_TEMPLATE_DETAILS,
    ])]
    private ?StepsTemplate $stepsTemplate = null;

    #[ORM\Column(type: Types::TEXT, options: ['default' => ''])]
    #[Groups([
        self::PRODUCT_TEMPLATE_READ,
        self::PRODUCT_TEMPLATE_WRITE,
        Company::COMPANY_READ,
        self::SUPPLY_CHAIN_TEMPLATE_PRODUCT_TEMPLATE_READ,
    ])]
    private string $description = '';

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['default' => null])]
    #[Groups([
        self::PRODUCT_TEMPLATE_READ,
    ])]
    private ?DateTimeImmutable $createdAt;

    /**
     * @var Collection<int, Node>
     */
    #[ORM\ManyToMany(targetEntity: Node::class, mappedBy: 'productTemplates')]
    #[Groups([
        self::PRODUCT_TEMPLATE_READ,
    ])]
    private Collection $nodes;

    /**
     * @var Collection<int, SupplyChainTemplate>
     */
    #[ORM\ManyToMany(targetEntity: SupplyChainTemplate::class, mappedBy: 'productTemplates')]
    private Collection $supplyChainTemplates;

    #[ORM\ManyToOne(targetEntity: SupplyChainTemplate::class, inversedBy: 'nodeTemplates')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups([
        self::PRODUCT_TEMPLATE_WRITE,
    ])]
    private ?SupplyChainTemplate $supplyChainTemplate = null;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->companies = new ArrayCollection();
        $this->nodes = new ArrayCollection();
        $this->haveDpp = false;
        $this->supplyChainTemplates = new ArrayCollection();
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(File $file): void
    {
        $this->file = $file;
    }

    public function getProductImage(): ?string
    {
        return $this->productImage;
    }

    public function setProductImage(?string $productImage): void
    {
        $this->productImage = $productImage;
    }

    public function getHaveDpp(): bool
    {
        return $this->haveDpp;
    }

    public function setHaveDpp(bool $haveDpp): void
    {
        $this->haveDpp = $haveDpp;
    }

    /**
     * @return Collection<int, Company>
     */
    public function getCompanies(): Collection
    {
        return $this->companies;
    }

    public function addCompany(Company $company): self
    {
        if (!$this->companies->contains($company)) {
            $this->companies[] = $company;
            $company->addProductTemplate($this);
        }

        return $this;
    }

    public function removeCompany(Company $company): self
    {
        if ($this->companies->removeElement($company)) {
            $company->removeProductTemplate($this);
        }

        return $this;
    }

    public function getStepsTemplate(): ?StepsTemplate
    {
        return $this->stepsTemplate;
    }

    public function setStepsTemplate(?StepsTemplate $stepsTemplate): void
    {
        $this->stepsTemplate = $stepsTemplate;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
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
            $node->addProductTemplate($this);
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
     * @return Collection<int, SupplyChainTemplate>
     */
    public function getSupplyChainTemplates(): Collection
    {
        return $this->supplyChainTemplates;
    }

    public function addSupplyChainTemplate(SupplyChainTemplate $supplyChainTemplate): self
    {
        if (!$this->supplyChainTemplates->contains($supplyChainTemplate)) {
            $this->supplyChainTemplates->add($supplyChainTemplate);

            if (!$supplyChainTemplate->getProductTemplates()->contains($this)) {
                $supplyChainTemplate->addProductTemplate($this);
            }
        }

        return $this;
    }

    public function removeSupplyChainTemplate(SupplyChainTemplate $supplyChainTemplate): self
    {
        if ($this->supplyChainTemplates->contains($supplyChainTemplate)) {
            $this->supplyChainTemplates->removeElement($supplyChainTemplate);
        }

        return $this;
    }

    public function getSupplyChainTemplate(): ?SupplyChainTemplate
    {
        return $this->supplyChainTemplate;
    }

    public function setSupplyChainTemplate(?SupplyChainTemplate $supplyChainTemplate): self
    {
        $this->supplyChainTemplate = $supplyChainTemplate;

        return $this;
    }

    #[Groups([
        ProductTemplate::PRODUCT_TEMPLATE_READ_LISTING,
    ])]
    public function getCompaniesNames(): string
    {
        $names = [];
        foreach ($this->getCompanies() as $company) {
            $names[] = $company->getName();
        }

        return implode(', ', $names);
    }

    #[Groups([
        ProductTemplate::PRODUCT_TEMPLATE_READ_LISTING,
    ])]
    public function getCompaniesIds(): string
    {
        $ids = [];
        foreach ($this->getCompanies() as $company) {
            $ids[] = $company->getId();
        }

        return implode(', ', $ids);
    }

    #[Groups([
        ProductTemplate::PRODUCT_TEMPLATE_READ_LISTING,
    ])]
    public function getTotalSteps(): int
    {
        if ($this->getStepsTemplate()) {
            return $this->getStepsTemplate()->getTotalSteps();
        }

        return 0;
    }

    #[Groups([
        ProductTemplate::PRODUCT_TEMPLATE_READ_LISTING,
    ])]
    public function getTotalInputs(): int
    {
        if ($this->getStepsTemplate()) {
            return $this->getStepsTemplate()->getTotalInputs();
        }

        return 0;
    }
}

<?php

declare(strict_types=1);

namespace App\Entity\Company;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Api\Filter\CompanyDppsLengthOrderFilter;
use App\Api\Filter\CompanyProductTemplatesLengthOrderFilter;
use App\Api\Filter\CompanyUsersLengthOrderFilter;
use App\Api\Filter\UidFilter;
use App\DataTransferObjects\Registration\CompanyRegistrationInput;
use App\Entity\Dpp\Dpp;
use App\Entity\Embeddable\Address;
use App\Entity\Logistics\Logistics;
use App\Entity\Product\ProductTemplate;
use App\Entity\Quirk\HasLatLng;
use App\Entity\Step\ProductStep;
use App\Entity\Step\Step;
use App\Entity\TransportType\TransportType;
use App\Entity\User;
use App\Repository\Company\CompanyRepository;
use App\StateProcessors\AddCompanyProcessor;
use App\StateProcessors\CompanyDeleteProcessor;
use App\StateProcessors\CompanyRegistrationProcessor;
use App\StateProcessors\EditCompanyProcessor;
use App\StateProcessors\FileDeleteProcessor;
use App\StateProcessors\ImageUploadProcessor;
use App\StateProcessors\InviteCompanyProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/companies/listing',
            normalizationContext: [
                'groups' => [
                    self::COMPANY_READ_LISTING,
                ],
            ],
        ),
        new Get(
            normalizationContext: [
                'groups' => [
                    self::COMPANY_READ_DETAIL,
                    Address::ADDRESS_READ,
                ],
            ],
        ),
        new GetCollection(),
        new Post(
            uriTemplate: '/companies/add-company',
            security: "is_granted('ROLE_ADMIN')",
            processor: AddCompanyProcessor::class,
        ),
        new Post(
            uriTemplate: '/companies/register-company',
            normalizationContext: [],
            denormalizationContext: [],
            input: CompanyRegistrationInput::class,
            processor: CompanyRegistrationProcessor::class,
        ),
        new Post(
            uriTemplate: '/companies/invite-company',
            denormalizationContext: ['groups' => [self::COMPANY_EMAIL_INVITE]],
            security: "is_granted('ROLE_ADMIN')",
            processor: InviteCompanyProcessor::class,
        ),
        new Post(
            uriTemplate: '/first_image/companies/{id}/company_logo',
            inputFormats: [
                'multipart' => ['multipart/form-data'],
            ],
            denormalizationContext: [
                'groups' => [self::COMPANY_IMAGE_UPLOAD],
            ],
            security: "is_granted('ROLE_COMPANY_MANAGER')",
            processor: ImageUploadProcessor::class
        ),
        new Post(
            uriTemplate: '/companies/{id}/company_logo',
            inputFormats: [
                'multipart' => ['multipart/form-data'],
            ],
            denormalizationContext: [
                'groups' => [self::COMPANY_IMAGE_UPLOAD],
            ],
            security: "is_granted('ROLE_COMPANY_MANAGER')",
            processor: ImageUploadProcessor::class
        ),
        new Patch(
            security: "is_granted('ROLE_COMPANY_MANAGER')",
            processor: EditCompanyProcessor::class,
        ),
        new Delete(
            uriTemplate: '/companies/{id}/company_logo',
            security: "is_granted('ROLE_COMPANY_MANAGER')",
            processor: FileDeleteProcessor::class,
        ),
        new Delete(
            security: "is_granted('ROLE_COMPANY_MANAGER')",
            processor: CompanyDeleteProcessor::class,
        ),
    ],
    normalizationContext: [
        'groups' => [
            self::COMPANY_READ,
            Address::ADDRESS_READ,
            CompanySite::COMPANY_COMPANY_SITE_READ,
            Step::PRODUCT_TEMPLATE_DETAILS,
        ],
    ],
    denormalizationContext: [
        'groups' => [
            self::COMPANY_WRITE,
            Address::ADDRESS_WRITE,
            CompanySite::COMPANY_COMPANY_SITE_WRITE,
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
#[ApiFilter(filterClass: UidFilter::class, properties: ['id' => 'exact'])]
#[ApiFilter(BooleanFilter::class, properties: ['logisticsCompany', 'productCompany'])]
#[ApiFilter(SearchFilter::class, properties: ['name' => 'partial'])]
#[ApiFilter(OrderFilter::class, properties: ['name'])]
#[ApiFilter(CompanyProductTemplatesLengthOrderFilter::class, properties: ['order[totalProductTemplate]'])]
#[ApiFilter(CompanyDppsLengthOrderFilter::class, properties: ['order[totalDpps]'])]
#[ApiFilter(CompanyUsersLengthOrderFilter::class, properties: ['order[totalUsers]'])]
#[ORM\Entity(repositoryClass: CompanyRepository::class)]
#[Vich\Uploadable]
class Company extends User
{
    use HasLatLng;

    public const COMPANY_READ = 'company-read';
    public const COMPANY_NAME = 'company-name';
    public const COMPANY_SITES = 'company-sites';
    public const COMPANY_IMAGE_UPLOAD = 'company-image-upload';
    public const COMPANY_WRITE = 'company-write';
    public const COMPANY_EMAIL_INVITE = 'company-email-invite';
    public const COMPANY_READ_LISTING = 'company-read-listing';
    public const COMPANY_READ_DETAIL = 'company-read-detail';


    #[Vich\UploadableField(mapping: 'company_logos', fileNameProperty: 'companyLogo')]
    #[Groups([self::COMPANY_IMAGE_UPLOAD])]
    public ?File $file = null;

    #[ORM\Column(type: Types::STRING, options: [
        'default' => '',
    ])]
    #[Groups([
        self::COMPANY_READ,
        self::COMPANY_NAME,
        self::COMPANY_WRITE,
        self::COMPANY_READ_LISTING,
        self::COMPANY_READ_DETAIL,
        Logistics::LOGISTICS_READ,
        ProductTemplate::PRODUCT_TEMPLATE_READ,
        Dpp::DPP_READ_DETAIL,
        ProductStep::STEP_READ,
    ])]
    private string $name = '';

    #[ORM\Column(type: Types::STRING, nullable: true, options: [
        'default' => '',
    ])]
    #[Groups([
        self::COMPANY_READ,
        self::COMPANY_READ_LISTING,
        self::COMPANY_READ_DETAIL,
        Dpp::DPP_READ_DETAIL,
        ProductStep::STEP_READ,
    ])]
    private ?string $companyLogo = '';

    #[ORM\Embedded(class: Address::class)]
    #[Groups([
        self::COMPANY_READ,
        self::COMPANY_WRITE,
        self::COMPANY_READ_DETAIL,
    ])]
    private Address $address;

    #[ORM\Column(type: Types::FLOAT, options: [
        'default' => 0,
    ])]
    #[Groups([
        self::COMPANY_READ,
        ProductTemplate::PRODUCT_TEMPLATE_READ,
        Dpp::DPP_READ_DETAIL,
        self::COMPANY_READ_LISTING,
        self::COMPANY_READ_DETAIL,
    ])]
    private float $latitude = 0;

    #[ORM\Column(type: Types::FLOAT, options: [
        'default' => 0,
    ])]
    #[Groups([
        self::COMPANY_READ,
        ProductTemplate::PRODUCT_TEMPLATE_READ,
        Dpp::DPP_READ_DETAIL,
        self::COMPANY_READ_LISTING,
        self::COMPANY_READ_DETAIL,
    ])]
    private float $longitude = 0;

    #[ORM\Column(type: Types::TEXT, options: ['default' => ''])]
    #[Groups([
        self::COMPANY_READ,
        self::COMPANY_WRITE,
        self::COMPANY_READ_DETAIL,
    ])]
    private string $description = '';

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    #[Groups([
        self::COMPANY_READ,
        self::COMPANY_WRITE,
        self::COMPANY_READ_LISTING,
        self::COMPANY_READ_DETAIL,
    ])]
    private bool $logisticsCompany = false;

    /**
     * @var Collection<int, TransportType>
     */
    #[ORM\ManyToMany(targetEntity: TransportType::class, cascade: ['persist'])]
    #[ORM\JoinTable(name: 'company_transport_type')]
    #[Groups([
        self::COMPANY_READ,
        self::COMPANY_WRITE,
        self::COMPANY_READ_DETAIL,
    ])]
    private Collection $typeOfTransport;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    #[Groups([
        self::COMPANY_READ,
        self::COMPANY_WRITE,
        self::COMPANY_READ_LISTING,
        self::COMPANY_READ_DETAIL,
    ])]
    private bool $productCompany = false;

    /**
     * @var Collection<int, CompanySite>
     */
    #[ORM\OneToMany(targetEntity: CompanySite::class, mappedBy: 'company', cascade: ['persist'], orphanRemoval: true)]
    #[Groups([
        self::COMPANY_READ,
        self::COMPANY_WRITE,
        self::COMPANY_SITES,
    ])]
    private Collection $sites;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'company', orphanRemoval: true)]
    #[Groups([self::COMPANY_READ])]
    private Collection $users;

    #[ORM\Column(type: Types::STRING, length: 128, unique: true)]
    #[Groups([self::COMPANY_READ])]
    private string $slug;

    /**
     * @var Collection<int, ProductTemplate>
     */
    #[ORM\ManyToMany(targetEntity: ProductTemplate::class, inversedBy: 'companies')]
    #[Groups([
        self::COMPANY_READ,
        Step::PRODUCT_TEMPLATE_DETAILS,
    ])]
    private Collection $productTemplates;

    /**
     * @var Collection<int, Logistics>
     */
    #[ORM\OneToMany(targetEntity: Logistics::class, mappedBy: 'company', cascade: ['persist'])]
    #[Groups([self::COMPANY_READ])]
    private Collection $logistics;

    /**
     * @var Collection<int, Dpp>
     */
    #[ORM\OneToMany(targetEntity: Dpp::class, mappedBy: 'company')]
    private Collection $dpps;

    /**
     * @var Collection<int, ProductStep>
     */
    #[ORM\OneToMany(targetEntity: ProductStep::class, mappedBy: 'company')]
    private Collection $productSteps;

    public function __construct()
    {
        parent::__construct();

        $this->sites = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->productTemplates = new ArrayCollection();
        $this->logistics = new ArrayCollection();
        $this->typeOfTransport = new ArrayCollection();
        $this->dpps = new ArrayCollection();
        $this->productSteps = new ArrayCollection();
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(File $file): void
    {
        $this->file = $file;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCompanyLogo(): ?string
    {
        return $this->companyLogo;
    }

    public function setCompanyLogo(?string $companyLogo): void
    {
        $this->companyLogo = $companyLogo;
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

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function isLogisticsCompany(): bool
    {
        return $this->logisticsCompany;
    }

    public function setLogisticsCompany(bool $logisticsCompany): void
    {
        $this->logisticsCompany = $logisticsCompany;
    }

    public function isProductCompany(): bool
    {
        return $this->productCompany;
    }

    public function setProductCompany(bool $productCompany): void
    {
        $this->productCompany = $productCompany;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setCompany($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
        }

        return $this;
    }

    /**
     * @return Collection<int, CompanySite>
     */
    public function getSites(): Collection
    {
        return $this->sites;
    }

    public function addSite(CompanySite $companySite): self
    {
        if (!$this->sites->contains($companySite)) {
            $this->sites->add($companySite);
            $companySite->setCompany($this);
        }

        return $this;
    }

    public function removeSite(CompanySite $companySite): self
    {
        if ($this->sites->contains($companySite)) {
            $this->sites->removeElement($companySite);
        }

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    /**
     * @return Collection<int, TransportType>
     */
    public function getTypeOfTransport(): Collection
    {
        return $this->typeOfTransport;
    }

    public function addTypeOfTransport(TransportType $transportType): self
    {
        if (!$this->typeOfTransport->contains($transportType)) {
            $this->typeOfTransport[] = $transportType;
        }

        return $this;
    }

    public function removeTypeOfTransport(TransportType $transportType): self
    {
        $this->typeOfTransport->removeElement($transportType);

        return $this;
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
            $this->productTemplates[] = $productTemplate;
        }

        return $this;
    }

    public function removeProductTemplate(ProductTemplate $productTemplate): self
    {
        $this->productTemplates->removeElement($productTemplate);

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
            $logistics->setCompany($this);
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

    /**
     * @return Collection<int, Dpp>
     */
    public function getDpps(): Collection
    {
        return $this->dpps;
    }

    /**
     * @return Collection<int, ProductStep>
     */
    public function getProductSteps(): Collection
    {
        return $this->productSteps;
    }

    #[Groups([
        self::COMPANY_READ,
    ])]
    public function getNumberOfDpps(): int
    {
        return $this->dpps->count();
    }

    #[Groups([
        self::COMPANY_READ_LISTING,
    ])]
    public function getTotalProductTemplate(): int
    {
        return $this->getProductTemplates()->count();
    }

    #[Groups([
        self::COMPANY_READ_LISTING,
    ])]
    public function getTotalDpps(): int
    {
        return $this->getDpps()->count();
    }

    #[Groups([
        self::COMPANY_READ_LISTING,
    ])]
    public function getTotalUsers(): int
    {
        return $this->getUsers()->count();
    }

    #[Groups([
        Company::COMPANY_READ_LISTING,
    ])]
    public function getTypeOfTransportsNames(): ?string
    {
        $names = [];
        foreach ($this->getTypeOfTransport() as $typeOfTransport) {
            $names[] = $typeOfTransport->getName();
        }

        return implode(', ', $names);
    }

    /**
     * @return array<int, array{id: string, name: string}>
     */
    #[Groups([
        Company::COMPANY_READ_LISTING,
    ])]
    public function getTypeOfTransportsData(): array
    {

        $typeOfTransportsData = [];

        foreach ($this->getTypeOfTransport() as $typeOfTransport) {
            $typeOfTransportsData[] = [
                'id' => (string) $typeOfTransport->getId(),
                'name' => $typeOfTransport->getName(),
            ];
        }

        return $typeOfTransportsData;
    }

}

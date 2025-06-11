<?php

declare(strict_types=1);

namespace App\Entity\Company;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Api\Filter\UidFilter;
use App\Entity\Embeddable\Address;
use App\Entity\ImageCollection\CompanySiteImage;
use App\Entity\Product\Product;
use App\Entity\Quirk\HasLatLng;
use App\Entity\Step\ProductStep;
use App\Repository\Company\CompanySiteRepository;
use App\StateProcessors\AddCompanySiteProcessor;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ApiResource(
    paginationItemsPerPage: 20,
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
    operations: [
        new GetCollection(),
        new Get(),
        new Post(
            processor: AddCompanySiteProcessor::class,
        ),
        new Patch(
            processor: AddCompanySiteProcessor::class,
        ),
        new Delete(),
    ],
    normalizationContext: [
        'groups' => [
            self::COMPANY_SITE_READ,
            CompanySiteImage::COMPANY_SITE_IMAGE_READ,
            Address::ADDRESS_READ,
        ],
    ],
    denormalizationContext: [
        'groups' => [
            self::COMPANY_SITE_WRITE,
            Address::ADDRESS_WRITE,
        ],
    ],
    order: ['createdAt' => 'DESC'],
)]
#[ORM\Entity(repositoryClass: CompanySiteRepository::class)]
#[ApiFilter(filterClass: UidFilter::class, properties: ['company.id' => 'exact'])]
#[ApiFilter(OrderFilter::class, properties: ['name'])]
#[Vich\Uploadable]
class CompanySite
{
    use HasLatLng;

    public const COMPANY_SITE_READ = 'company-site-read';
    public const COMPANY_SITE_WRITE = 'company-site-write';
    public const DPP_COMPANY_SITE_READ = 'dpp-company-site-read';
    public const COMPANY_COMPANY_SITE_READ = 'company-company-site-read';
    public const COMPANY_COMPANY_SITE_WRITE = 'company-company-site-write';

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups([
        self::COMPANY_SITE_READ,
        self::COMPANY_COMPANY_SITE_READ,
        self::DPP_COMPANY_SITE_READ,
        Company::COMPANY_SITES,
        ProductStep::STEP_READ,
    ])]
    private Uuid $id;

    #[ORM\Column(type: Types::STRING, options: [
        'default' => '',
    ])]
    #[Assert\NotBlank]
    #[Groups([
        self::COMPANY_SITE_READ,
        self::COMPANY_SITE_WRITE,
        self::COMPANY_COMPANY_SITE_READ,
        self::COMPANY_COMPANY_SITE_WRITE,
        self::DPP_COMPANY_SITE_READ,
        Company::COMPANY_SITES,
        ProductStep::STEP_READ,
    ])]
    private string $name = '';

    #[ORM\ManyToOne(targetEntity: Company::class, inversedBy: 'sites')]
    #[Assert\NotBlank]
    #[Groups([
        self::COMPANY_SITE_READ,
        self::COMPANY_SITE_WRITE,
        self::DPP_COMPANY_SITE_READ,
    ])]
    private Company $company;

    #[ORM\Embedded(Address::class)]
    #[Groups([
        self::COMPANY_SITE_READ,
        self::COMPANY_SITE_WRITE,
        self::COMPANY_COMPANY_SITE_READ,
        self::COMPANY_COMPANY_SITE_WRITE,
        self::DPP_COMPANY_SITE_READ,
    ])]
    private Address $address;

    #[ORM\Column(type: Types::FLOAT, options: [
        'default' => 0,
    ])]
    #[Groups([
        self::COMPANY_SITE_READ,
        self::COMPANY_COMPANY_SITE_READ,
        self::DPP_COMPANY_SITE_READ,
    ])]
    private float $latitude = 0;

    #[ORM\Column(type: Types::FLOAT, options: [
        'default' => 0,
    ])]
    #[Groups([
        self::COMPANY_SITE_READ,
        self::COMPANY_COMPANY_SITE_READ,
        self::DPP_COMPANY_SITE_READ,
    ])]
    private float $longitude = 0;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['default' => null])]
    #[Groups([
        self::COMPANY_SITE_READ,
    ])]
    private ?DateTimeImmutable $createdAt;

    /**
     * @var Collection<int, CompanySiteImage>
     */
    #[ORM\OneToMany(targetEntity: CompanySiteImage::class, mappedBy: 'companySite', cascade: ['persist'], orphanRemoval: true)]
    #[Groups([
        self::COMPANY_SITE_READ,
        self::DPP_COMPANY_SITE_READ,
        self::COMPANY_COMPANY_SITE_READ,
    ])]
    private Collection $siteImages;

    /**
     * @var Collection<int, Product>
     */
    #[ORM\OneToMany(targetEntity: Product::class, mappedBy: 'companySite', orphanRemoval: true)]
    private Collection $products;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->siteImages = new ArrayCollection();
        $this->products = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getCompany(): Company
    {
        return $this->company;
    }

    public function setCompany(Company $company): void
    {
        $this->company = $company;
    }

    public function getAddress(): Address
    {
        return $this->address;
    }

    public function setAddress(Address $address): void
    {
        $this->address = $address;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /** @return Collection<int, CompanySiteImage> */
    public function getSiteImages(): Collection
    {
        return $this->siteImages;
    }

    public function addSiteImage(CompanySiteImage $companySiteImage): self
    {
        if (!$this->siteImages->contains($companySiteImage)) {
            $this->siteImages->add($companySiteImage);
            $companySiteImage->setCompanySite($this);
        }

        return $this;
    }

    public function removeSiteImage(CompanySiteImage $companySiteImage): self
    {
        if ($this->siteImages->contains($companySiteImage)) {
            $this->siteImages->removeElement($companySiteImage);
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
            $product->setCompanySite($this);
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
}

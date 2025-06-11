<?php

declare(strict_types=1);

namespace App\Entity\Product;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Api\Filter\ProductInputImageByInputIdFilter;
use App\Repository\Product\ProductImageRepository;
use App\StateProcessors\ImageUploadProcessor;
use App\StateProcessors\RemoveProductInputImageProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(
            inputFormats: ['multipart' => ['multipart/form-data']],
            normalizationContext: [],
            denormalizationContext: ['groups' => [self::PRODUCT_INPUT_IMAGE_WRITE]],
            processor: ImageUploadProcessor::class
        ),
        new Patch(),
        new Delete(
            processor: RemoveProductInputImageProcessor::class,
        ),
    ],
    normalizationContext: ['groups' => [self::PRODUCT_INPUT_IMAGE_READ]],
    denormalizationContext: ['groups' => [self::PRODUCT_INPUT_IMAGE_WRITE]]
)]
#[ORM\Entity(repositoryClass: ProductImageRepository::class)]
#[ORM\Index(columns: ['image'])]
#[ApiFilter(filterClass: ProductInputImageByInputIdFilter::class, properties: ['input.id' => 'exact'])]
#[Vich\Uploadable]
class ProductInputImage
{
    public const PRODUCT_INPUT_IMAGE_READ = 'product-input-image-read';
    public const DPP_PRODUCT_INPUT_IMAGE_READ = 'dpp-product-input-image-read';
    public const PRODUCT_INPUT_IMAGE_WRITE = 'product-input-image_write';


    #[Vich\UploadableField(mapping: 'product_input_collection_images', fileNameProperty: 'image')]
    #[Groups([self::PRODUCT_INPUT_IMAGE_WRITE])]
    public ?File $file = null;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups([
        self::PRODUCT_INPUT_IMAGE_READ,
        self::PRODUCT_INPUT_IMAGE_WRITE,
        self::DPP_PRODUCT_INPUT_IMAGE_READ,
    ])]
    private Uuid $id;

    #[ORM\Column(type: Types::STRING, nullable: true, options: ['default' => ''])]
    #[Groups([
        self::PRODUCT_INPUT_IMAGE_READ,
        self::DPP_PRODUCT_INPUT_IMAGE_READ,
        ProductInputHistory::PRODUCT_INPUT_HISTORY_READ,
    ])]
    private ?string $image = '';

    #[ORM\ManyToOne(targetEntity: ProductInput::class, inversedBy: 'images')]
    #[ORM\JoinColumn(name: 'input_id', referencedColumnName: 'id', nullable: true)]
    #[Groups([
        self::PRODUCT_INPUT_IMAGE_READ,
        self::PRODUCT_INPUT_IMAGE_WRITE,
    ])]
    private ?ProductInput $input = null;

    /**
     * @var Collection<int, ProductInputHistory>
     */
    #[ORM\ManyToMany(targetEntity: ProductInputHistory::class, mappedBy: 'images')]
    #[Groups([
        self::PRODUCT_INPUT_IMAGE_READ,
    ])]
    private Collection $inputHistory;

    public function __construct()
    {
        $this->inputHistory = new ArrayCollection();
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(File $file): void
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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): void
    {
        $this->image = $image;
    }

    public function getInput(): ?ProductInput
    {
        return $this->input;
    }

    public function setInput(?ProductInput $input): void
    {
        $this->input = $input;

        if ($input !== null && !$input->getImages()->contains($this)) {
            $input->getImages()->add($this);
        }
    }

    /**
     * @return Collection<int, ProductInputHistory>
     */
    public function getInputHistory(): Collection
    {
        return $this->inputHistory;
    }

    public function addInputHistory(ProductInputHistory $inputHistory): self
    {
        if (!$this->inputHistory->contains($inputHistory)) {
            $this->inputHistory->add($inputHistory);

            $inputHistory->addImage($this);
        }

        return $this;
    }

    public function removeInputHistory(ProductInputHistory $inputHistory): self
    {
        if ($this->inputHistory->contains($inputHistory)) {
            $this->inputHistory->removeElement($inputHistory);
        }

        return $this;
    }
}

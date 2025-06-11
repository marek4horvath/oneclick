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
use App\Api\Filter\InputCategoryInputsLengthOrderFilter;
use App\Entity\Quirk\HasName;
use App\Entity\Quirk\HasUid;
use App\Repository\Product\InputCategoryRepository;
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

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Patch(),
        new Delete(),
    ],
    normalizationContext: [
        'groups' => [
            self::INPUT_CATEGORY_READ,
            Input::STEP_INPUT_READ,
        ],
    ],
    denormalizationContext: ['groups' => [self::INPUT_CATEGORY_WRITE]],
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
#[ApiFilter(OrderFilter::class, properties: ['name'])]
#[ApiFilter(InputCategoryInputsLengthOrderFilter::class, properties: ['order[inputs]'])]
#[ApiFilter(SearchFilter::class, properties: ['name' => 'partial'])]
#[ORM\Entity(repositoryClass: InputCategoryRepository::class)]
class InputCategory
{
    use HasUid;
    use HasName;
    public const INPUT_CATEGORY_READ = 'input-category-read';
    public const INPUT_CATEGORY_WRITE = 'input-category-write';
    public const INPUT_INPUT_CATEGORY_READ = 'input-input-category-read';

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups([
        self::INPUT_CATEGORY_READ,
        self::INPUT_INPUT_CATEGORY_READ,
    ])]
    private Uuid $id;

    #[ORM\Column(type: Types::STRING, options: ['default' => ''])]
    #[Groups([
        self::INPUT_CATEGORY_READ,
        self::INPUT_INPUT_CATEGORY_READ,
        self::INPUT_CATEGORY_WRITE,
    ])]
    #[Assert\NotBlank]
    private string $name = '';

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['default' => null])]
    #[Groups([
        self::INPUT_CATEGORY_READ,
        self::INPUT_INPUT_CATEGORY_READ,
    ])]
    private ?DateTimeImmutable $createdAt;

    /**
     * @var Collection<int, Input>
     */
    #[ORM\ManyToMany(targetEntity: Input::class, mappedBy: 'inputCategories', cascade: ['persist'])]
    #[Groups([
        self::INPUT_CATEGORY_READ,
        self::INPUT_CATEGORY_WRITE,
    ])]
    private Collection $inputs;

    /**
     * @var Collection<int, ProductInput>
     */
    #[ORM\ManyToMany(targetEntity: ProductInput::class, mappedBy: 'inputCategories')]
    #[Groups([self::INPUT_CATEGORY_READ, self::INPUT_CATEGORY_WRITE])]
    private Collection $productInputs;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->inputs = new ArrayCollection();
        $this->productInputs = new ArrayCollection();
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
     * @return Collection<int, Input>
     */
    public function getInputs(): Collection
    {
        return $this->inputs;
    }

    /**
     * @return $this
     */
    public function addInput(Input $input): self
    {
        if (!$this->inputs->contains($input)) {
            $this->inputs->add($input);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function removeInput(Input $input): self
    {
        if ($this->inputs->contains($input)) {
            $this->inputs->removeElement($input);
        }

        return $this;
    }

    /** @return Collection<int, ProductInput> */
    public function getProductInputs(): Collection
    {
        return $this->productInputs;
    }

    public function addProductInput(ProductInput $productInput): self
    {
        if (!$this->productInputs->contains($productInput)) {
            $this->productInputs->add($productInput);
            $productInput->addInputCategory($this);
        }

        return $this;
    }

    public function removeProductInput(ProductInput $productInput): self
    {
        if ($this->productInputs->contains($productInput)) {
            $this->productInputs->removeElement($productInput);
            $productInput->removeInputCategory($this);
        }

        return $this;
    }
}

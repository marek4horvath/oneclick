<?php

declare(strict_types=1);

namespace App\Entity\Product;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Quirk\HasId;
use App\Repository\Product\ProductAttributeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Patch(),
        new Delete(),
    ]
)]
#[ORM\Entity(repositoryClass: ProductAttributeRepository::class)]
class ProductAttribute
{
    use HasId;

    #[ORM\ManyToOne(targetEntity: ProductTemplate::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ProductTemplate $product;

    #[ORM\ManyToOne(targetEntity: Attribute::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Attribute $attribute;

    #[ORM\Column(type: 'string', length: 255)]
    private string $value;

    public function getProduct(): ProductTemplate
    {
        return $this->product;
    }

    public function setProduct(ProductTemplate $product): void
    {
        $this->product = $product;
    }

    public function getAttribute(): Attribute
    {
        return $this->attribute;
    }

    public function setAttribute(Attribute $attribute): void
    {
        $this->attribute = $attribute;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setName(string $value): void
    {
        $this->value = $value;
    }
}

<?php declare(strict_types=1);

namespace App\Entity\SupplyChain;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\SupplyChain\SupplyChainAlgorithmRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: SupplyChainAlgorithmRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new Post(),
        new Patch(
            denormalizationContext: [self::SUPPLY_CHAIN_ALGORITHM_PATCH],
        ),
        new Delete(),
    ],
    normalizationContext: [self::SUPPLY_CHAIN_ALGORITHM_READ],
    denormalizationContext: [self::SUPPLY_CHAIN_ALGORITHM_WRITE],
)]
class SupplyChainAlgorithm
{
    public const SUPPLY_CHAIN_ALGORITHM_READ = 'supply-chain-algorithm-read';
    public const SUPPLY_CHAIN_ALGORITHM_WRITE = 'supply-chain-algorithm-write';
    public const SUPPLY_CHAIN_ALGORITHM_PATCH = 'supply-chain-algorithm-patch';

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups([
        self::SUPPLY_CHAIN_ALGORITHM_READ,
        SupplyChainTemplate::SUPPLY_CHAIN_TEMPLATE_ALGORITHM_READ,
    ])]
    private Uuid $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups([
        self::SUPPLY_CHAIN_ALGORITHM_READ,
        self::SUPPLY_CHAIN_ALGORITHM_WRITE,
        self::SUPPLY_CHAIN_ALGORITHM_PATCH,
        SupplyChainTemplate::SUPPLY_CHAIN_TEMPLATE_ALGORITHM_READ,
    ])]
    private string $name = '';

    #[ORM\ManyToOne(targetEntity: SupplyChainTemplate::class, inversedBy: 'supplyChainAlgorithms')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([
        self::SUPPLY_CHAIN_ALGORITHM_READ,
        self::SUPPLY_CHAIN_ALGORITHM_WRITE,
    ])]
    private SupplyChainTemplate $supplyChainTemplate;

    /** @var array<string> */
    #[ORM\Column(type: Types::JSON, nullable: false)]
    #[Groups([
        self::SUPPLY_CHAIN_ALGORITHM_READ,
        self::SUPPLY_CHAIN_ALGORITHM_WRITE,
        self::SUPPLY_CHAIN_ALGORITHM_PATCH,
        SupplyChainTemplate::SUPPLY_CHAIN_TEMPLATE_ALGORITHM_READ,
    ])]
    private array $algorithm = [];

    /** @var array<string,string> */
    #[ORM\Column(type: Types::JSON, nullable: false)]
    #[Groups([
        self::SUPPLY_CHAIN_ALGORITHM_READ,
        self::SUPPLY_CHAIN_ALGORITHM_WRITE,
        self::SUPPLY_CHAIN_ALGORITHM_PATCH,
        SupplyChainTemplate::SUPPLY_CHAIN_TEMPLATE_ALGORITHM_READ,
    ])]
    private array $inputs = [];

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getSupplyChainTemplate(): SupplyChainTemplate
    {
        return $this->supplyChainTemplate;
    }

    public function setSupplyChainTemplate(SupplyChainTemplate $supplyChainTemplate): self
    {
        $this->supplyChainTemplate = $supplyChainTemplate;

        return $this;
    }

    /** @return array<string> */
    public function getAlgorithm(): array
    {
        return $this->algorithm;
    }

    /** @param array<string> $algorithm */
    public function setAlgorithm(array $algorithm): self
    {
        $this->algorithm = $algorithm;

        return $this;
    }

    /** @return array<string, string> */
    public function getInputs(): array
    {
        return $this->inputs;
    }

    /** @param array<string, string> $inputs */
    public function setInputs(array $inputs): self
    {
        $this->inputs = $inputs;

        return $this;
    }
}

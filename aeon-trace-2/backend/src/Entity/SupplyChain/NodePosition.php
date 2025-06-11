<?php

declare(strict_types=1);

namespace App\Entity\SupplyChain;

use App\Entity\Quirk\HasUid;
use App\Repository\SupplyChain\NodePositionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: NodePositionRepository::class)]
class NodePosition
{
    use HasUid;

    public const NODE_POSITION_READ = 'node_position:read';
    public const NODE_POSITION_WRITE = 'node_position:write';

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups([
        Node::NODE_READ,
        Node::NODE_WRITE,
        self::NODE_POSITION_READ,
        self::NODE_POSITION_WRITE,
        Node::SUPPLY_CHAIN_TEMPLATE_NODE_READ,
    ])]
    private Uuid $id;

    #[ORM\OneToOne(inversedBy: 'nodePosition', targetEntity: Node::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Node $node = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    #[Groups([
        Node::NODE_READ,
        Node::NODE_WRITE,
        self::NODE_POSITION_READ,
        self::NODE_POSITION_WRITE,
        Node::SUPPLY_CHAIN_TEMPLATE_NODE_READ,
    ])]
    private ?float $x = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    #[Groups([
        Node::NODE_READ,
        Node::NODE_WRITE,
        self::NODE_POSITION_READ,
        self::NODE_POSITION_WRITE,
        Node::SUPPLY_CHAIN_TEMPLATE_NODE_READ,
    ])]
    private ?float $y = null;

    public function getNode(): ?Node
    {
        return $this->node;
    }

    public function setNode(Node $node): self
    {
        $this->node = $node;

        return $this;
    }

    public function getX(): ?float
    {
        return $this->x;
    }

    public function setX(?float $x): self
    {
        $this->x = $x;

        return $this;
    }

    public function getY(): ?float
    {
        return $this->y;
    }

    public function setY(?float $y): self
    {
        $this->y = $y;

        return $this;
    }
}

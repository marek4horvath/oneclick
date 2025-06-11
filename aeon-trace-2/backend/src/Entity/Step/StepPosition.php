<?php

declare(strict_types=1);

namespace App\Entity\Step;

use App\Entity\Quirk\HasUid;
use App\Entity\SupplyChain\Node;
use App\Repository\Step\StepPositionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: StepPositionRepository::class)]
class StepPosition
{
    use HasUid;

    public const STEP_POSITION_READ = 'step_position:read';
    public const STEP_POSITION_WRITE = 'step_position:write';

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups([
        Node::NODE_READ,
        StepPosition::STEP_POSITION_READ,
        StepPosition::STEP_POSITION_WRITE,
        Step::STEP_READ,
        Step::STEP_TEMPLATE_STEP_READ,
        Step::STEP_WRITE,
        Step::STEP_TEMPLATE_STEP_WRITE,
    ])]
    private Uuid $id;

    #[ORM\OneToOne(inversedBy: 'stepPosition', targetEntity: Step::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Step $step = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    #[Groups([
        Step::STEP_READ,
        Step::STEP_WRITE,
        Node::NODE_READ,
        self::STEP_POSITION_READ,
        self::STEP_POSITION_WRITE,
    ])]
    private ?float $x = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    #[Groups([
        Step::STEP_READ,
        Step::STEP_WRITE,
        Node::NODE_READ,
        self::STEP_POSITION_READ,
        self::STEP_POSITION_WRITE,
    ])]
    private ?float $y = null;

    public function getStep(): ?Step
    {
        return $this->step;
    }

    public function setStep(Step $step): void
    {
        $this->step = $step;
    }

    public function getX(): ?float
    {
        return $this->x;
    }

    public function setX(?float $x): void
    {
        $this->x = $x;
    }

    public function getY(): ?float
    {
        return $this->y;
    }

    public function setY(?float $y): void
    {
        $this->y = $y;
    }
}

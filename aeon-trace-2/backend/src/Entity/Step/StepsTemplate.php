<?php

declare(strict_types=1);

namespace App\Entity\Step;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Quirk\HasName;
use App\Entity\Quirk\HasUid;
use App\Repository\Step\StepsTemplateRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

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
            self::STEP_TEMPLATE_READ,
            Step::STEP_TEMPLATE_STEP_READ,
        ],
    ],
    denormalizationContext: [
        'groups' => [
            self::STEP_TEMPLATE_WRITE,
            Step::STEP_TEMPLATE_STEP_WRITE,
        ],
    ]
)]
#[ORM\Entity(repositoryClass: StepsTemplateRepository::class)]
class StepsTemplate
{
    use HasUid;
    use HasName;

    public const STEP_TEMPLATE_READ = 'step-template-read';
    public const STEP_TEMPLATE_WRITE = 'step-template-write';
    public const SUPPLY_CHAIN_TEMPLATE_STEP_TEMPLATE_READ = 'supply-chain-template-step-template-read';

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups([
        self::STEP_TEMPLATE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_STEP_TEMPLATE_READ,
    ])]
    private Uuid $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups([
        self::STEP_TEMPLATE_READ,
        self::STEP_TEMPLATE_WRITE,
        self::SUPPLY_CHAIN_TEMPLATE_STEP_TEMPLATE_READ,
        Step::PRODUCT_TEMPLATE_DETAILS,
    ])]
    private string $name = '';

    /**
     * @var Collection<int, Step>
     */
    #[ORM\OneToMany(targetEntity: Step::class, mappedBy: 'stepsTemplate', cascade: ['persist'], orphanRemoval: true)]
    #[Groups([
        self::STEP_TEMPLATE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_STEP_TEMPLATE_READ,
        Step::PRODUCT_TEMPLATE_DETAILS,
        StepPosition::STEP_POSITION_READ,
    ])]
    private Collection $steps;

    public function __construct()
    {
        $this->steps = new ArrayCollection();
    }

    /**
     * @return Collection<int, Step>
     */
    public function getSteps(): Collection
    {
        return $this->steps;
    }

    public function addStep(Step $step): self
    {
        if (!$this->steps->contains($step)) {
            $this->steps->add($step);
            $step->setStepsTemplate($this);
        }

        return $this;
    }

    public function removeStep(Step $step): self
    {
        if ($this->steps->contains($step)) {
            $this->steps->removeElement($step);
        }

        return $this;
    }

    public function getTotalSteps(): int
    {
        return $this->steps->count();
    }

    public function getTotalInputs(): int
    {
        $totalCount = 0;
        foreach ($this->steps as $step) {
            $totalCount += $step->getInputs()->count();
        }

        return $totalCount;
    }
}

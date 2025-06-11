<?php

declare(strict_types=1);

namespace App\Entity\Step;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\DataProviders\QrEntityDataProvider;
use App\Entity\Collection\Process;
use App\Entity\Company\Company;
use App\Entity\Dpp\Dpp;
use App\Entity\Product\Input;
use App\Entity\Product\ProductTemplate;
use App\Entity\Quirk\HasName;
use App\Entity\Quirk\HasQr;
use App\Entity\Quirk\HasUid;
use App\Entity\SupplyChain\Node;
use App\Repository\Step\StepRepository;
use App\StateProcessors\FileDeleteProcessor;
use App\StateProcessors\ImageUploadProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Attribute\MaxDepth;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ApiResource(
    operations: [
        new Get(),
        new Get(
            uriTemplate: '/step/getByQrId',
            openapiContext: [
                'parameters' => [
                    [
                        'name' => 'companySlug',
                        'in' => 'query',
                        'description' => 'Company slug',
                        'required' => true,
                        'type' => 'string',
                    ],
                    [
                        'name' => 'qrId',
                        'in' => 'query',
                        'description' => 'QR identificator',
                        'required' => true,
                        'type' => 'integer',
                    ],
                ],
            ],
            provider: QrEntityDataProvider::class,
        ),
        new Get(
            uriTemplate: '/step/inputs/{id}',
            normalizationContext: [
                'groups' => [
                    self::STEP_INPUTS,
                    Input::STEP_INPUT_READ,
                ],
            ],
        ),
        new GetCollection(),
        new Post(
            uriTemplate: '/steps/{id}/step_image',
            inputFormats: ['multipart' => ['multipart/form-data']],
            denormalizationContext: ['groups' => [self::STEP_IMAGE_UPLOAD]],
            processor: ImageUploadProcessor::class
        ),
        new Post(),
        new Patch(),
        new Delete(
            uriTemplate: '/steps/{id}/step_image',
            processor: FileDeleteProcessor::class,
        ),
        new Delete(),
    ],
    normalizationContext: [
        'groups' => [
            self::STEP_READ,
            Input::STEP_INPUT_READ,
            self::STEP_INPUT_CATEGORY_READ,
        ],
    ],
    denormalizationContext: [
        'groups' => [
            self::STEP_WRITE,
        ],
    ]
)]
#[ORM\Entity(repositoryClass: StepRepository::class)]
#[Vich\Uploadable]
class Step
{
    use HasUid;
    use HasName;
    use HasQr;

    public const URL_PATH = 'step';
    public const STEP_READ = 'step-read';
    public const STEP_INPUTS = 'step-inputs';
    public const STEP_IMAGE_UPLOAD = 'company-image-upload';
    public const STEP_TEMPLATE_STEP_READ = 'step-template-step-read';
    public const STEP_WRITE = 'step-write';
    public const STEP_TEMPLATE_STEP_WRITE = 'step-template-step-write';
    public const SUPPLY_CHAIN_TEMPLATE_STEP_READ = 'supply-chain-template-step-read';
    public const PRODUCT_TEMPLATE_STEP_READ = 'product-template-step-read';
    public const LOGISTICS_TEMPLATE_STEP_READ = 'logistics-template-step-read';
    public const STEP_INPUT_CATEGORY_READ = 'step-input-category-read';
    public const PRODUCT_TEMPLATE_DETAILS = 'product-template-details';


    #[Vich\UploadableField(mapping: 'step_images', fileNameProperty: 'stepImage')]
    #[Groups([self::STEP_IMAGE_UPLOAD])]
    public ?File $file = null;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups([
        self::STEP_READ,
        Node::NODE_STEPS_READ,
        self::STEP_TEMPLATE_STEP_READ,
        self::PRODUCT_TEMPLATE_STEP_READ,
        self::LOGISTICS_TEMPLATE_STEP_READ,
        Node::NODE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_STEP_READ,
    ])]
    private Uuid $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups([
        self::STEP_READ,
        Node::NODE_STEPS_READ,
        self::STEP_TEMPLATE_STEP_READ,
        self::PRODUCT_TEMPLATE_STEP_READ,
        self::LOGISTICS_TEMPLATE_STEP_READ,
        self::STEP_WRITE,
        self::STEP_TEMPLATE_STEP_WRITE,
        Node::NODE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_STEP_READ,
    ])]
    private string $name = '';

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['default' => null])]
    #[Groups([
        self::STEP_READ,
        self::STEP_TEMPLATE_STEP_READ,
        self::PRODUCT_TEMPLATE_STEP_READ,
        self::STEP_WRITE,
        self::STEP_TEMPLATE_STEP_WRITE,
        Node::NODE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_STEP_READ,
    ])]
    private ?int $quantity = null;

    #[ORM\Column(type: Types::STRING, length: 255, options: ['default' => ''])]
    #[Groups([
        self::STEP_READ,
        self::STEP_TEMPLATE_STEP_READ,
        self::PRODUCT_TEMPLATE_STEP_READ,
        self::STEP_WRITE,
        self::STEP_TEMPLATE_STEP_WRITE,
        Node::NODE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_STEP_READ,
    ])]
    private string $measurementType = '';

    #[ORM\Column(type: Types::STRING, length: 255, options: ['default' => ''])]
    #[Groups([
        self::STEP_READ,
        self::STEP_TEMPLATE_STEP_READ,
        self::PRODUCT_TEMPLATE_STEP_READ,
        self::STEP_WRITE,
        self::STEP_TEMPLATE_STEP_WRITE,
        Node::NODE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_STEP_READ,
    ])]
    private string $unitMeasurement = '';

    #[ORM\Column(type: Types::STRING, length: 255, options: ['default' => ''])]
    #[Groups([
        self::STEP_READ,
        self::STEP_TEMPLATE_STEP_READ,
        self::PRODUCT_TEMPLATE_STEP_READ,
        self::STEP_WRITE,
        self::STEP_TEMPLATE_STEP_WRITE,
        Node::NODE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_STEP_READ,
    ])]
    private string $unitSymbol = '';

    #[ORM\Column(type: Types::STRING, nullable: true, options: ['default' => ''])]
    #[Groups([
        self::STEP_READ,
        self::STEP_TEMPLATE_STEP_READ,
        self::PRODUCT_TEMPLATE_STEP_READ,
        self::LOGISTICS_TEMPLATE_STEP_READ,
        Node::NODE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_STEP_READ,
    ])]
    private ?string $stepImage = '';

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Groups([
        self::STEP_READ,
        self::STEP_TEMPLATE_STEP_READ,
        self::PRODUCT_TEMPLATE_STEP_READ,
        self::LOGISTICS_TEMPLATE_STEP_READ,
        self::STEP_WRITE,
        self::STEP_TEMPLATE_STEP_WRITE,
        Node::NODE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_STEP_READ,
    ])]
    private string $batchTypeOfStep;

    #[ORM\ManyToOne(targetEntity: Process::class, inversedBy: 'steps')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups([
        self::STEP_READ,
        self::STEP_TEMPLATE_STEP_READ,
        self::PRODUCT_TEMPLATE_STEP_READ,
        self::LOGISTICS_TEMPLATE_STEP_READ,
        self::STEP_WRITE,
        self::STEP_TEMPLATE_STEP_WRITE,
        Node::NODE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_STEP_READ,
    ])]
    private Process $process;

    #[ORM\ManyToOne(targetEntity: StepsTemplate::class, inversedBy: 'steps')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups([
        self::STEP_READ,
        self::STEP_WRITE,
        Node::NODE_READ,
    ])]
    private ?StepsTemplate $stepsTemplate = null;

    /**
     * @var Collection<int, Step>
     */
    #[ORM\ManyToMany(targetEntity: Step::class, inversedBy: 'steps', cascade: ['persist'])]
    #[ORM\JoinTable(name: 'step_parents_step_children')]
    #[Groups([
        self::STEP_READ,
        self::STEP_WRITE,
        self::STEP_TEMPLATE_STEP_READ,
        self::PRODUCT_TEMPLATE_STEP_READ,
        self::LOGISTICS_TEMPLATE_STEP_READ,
        Node::NODE_READ,
    ])]
    #[MaxDepth(1)]
    private Collection $parentSteps;

    #[ORM\ManyToOne(targetEntity: Company::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups([
        self::STEP_READ,
        self::PRODUCT_TEMPLATE_STEP_READ,
        self::STEP_WRITE,
        Node::NODE_READ,
    ])]
    private ?Company $company = null;

    #[ORM\ManyToOne(targetEntity: ProductTemplate::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups([
        self::STEP_READ,
        self::STEP_INPUTS,
        self::STEP_WRITE,
        Node::NODE_READ,
    ])]
    private ?ProductTemplate $productTemplate = null;

    #[ORM\Column(type: Types::INTEGER, length: 2)]
    #[Groups([
        self::STEP_READ,
        self::STEP_TEMPLATE_STEP_READ,
        self::PRODUCT_TEMPLATE_STEP_READ,
        self::LOGISTICS_TEMPLATE_STEP_READ,
        self::STEP_WRITE,
        self::STEP_TEMPLATE_STEP_WRITE,
        Node::NODE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_STEP_READ,
    ])]
    private int $sort = 1;

    #[Vich\UploadableField(mapping: 'step_qrs', fileNameProperty: 'qrImage')]
    public ?File $qrFile = null;

    #[ORM\Column(type: Types::STRING, nullable: true, options: [
        'default' => '',
    ])]
    #[Groups([self::STEP_READ])]
    private ?string $qrImage = '';

    #[ORM\Column(type: Types::BOOLEAN, nullable: false, options: [
        'default' => false,
    ])]
    #[Groups([self::STEP_WRITE])]
    private bool $createQr = false;

    /**
     * @var Collection<int, Step>
     */
    #[ORM\ManyToMany(targetEntity: Step::class, mappedBy: 'parentSteps', cascade: ['persist'])]
    #[Groups([
        self::STEP_READ,
        self::STEP_WRITE,
        self::STEP_TEMPLATE_STEP_READ,
        self::PRODUCT_TEMPLATE_STEP_READ,
        self::LOGISTICS_TEMPLATE_STEP_READ,
        Node::NODE_READ,
    ])]
    #[MaxDepth(1)]
    private Collection $steps;

    /**
     * @var Collection<int, Input>
     */
    #[ORM\OneToMany(targetEntity: Input::class, mappedBy: 'step', cascade: ['persist'], orphanRemoval: true)]
    #[Groups([
        self::STEP_READ,
        self::STEP_INPUTS,
        Node::NODE_STEPS_READ,
        self::STEP_TEMPLATE_STEP_READ,
        self::PRODUCT_TEMPLATE_STEP_READ,
        self::LOGISTICS_TEMPLATE_STEP_READ,
        Node::NODE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_STEP_READ,
        self::PRODUCT_TEMPLATE_DETAILS,
    ])]
    private Collection $inputs;

    /**
     * @var Collection<int, Node>
     */
    #[ORM\ManyToMany(targetEntity: Node::class, mappedBy: 'steps')]
    #[Groups([
        self::STEP_READ,
        self::STEP_TEMPLATE_STEP_READ,
        self::STEP_WRITE,
        self::STEP_TEMPLATE_STEP_WRITE,
    ])]
    private Collection $nodes;

    /**
     * @var Collection<int, ProductStep>
     */
    #[ORM\OneToMany(targetEntity: ProductStep::class, mappedBy: 'stepTemplateReference', orphanRemoval: true)]
    private Collection $inheritors;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Groups([
        self::STEP_READ,
        self::PRODUCT_TEMPLATE_STEP_READ,
        self::LOGISTICS_TEMPLATE_STEP_READ,
        self::STEP_WRITE,
        Node::NODE_READ,
    ])]
    private ?int $qrId = null;

    /**
     * @var Collection<int, Dpp>
     */
    #[ORM\ManyToMany(targetEntity: Dpp::class, mappedBy: 'steps')]
    #[Groups([
        self::STEP_READ,
        self::STEP_WRITE,
        Node::NODE_READ,
    ])]
    private Collection $dpps;

    #[ORM\OneToOne(targetEntity: StepPosition::class, cascade: ['persist', 'remove'], mappedBy: 'step')]
    #[Groups([
        Node::NODE_READ,
        Node::NODE_WRITE,
        StepPosition::STEP_POSITION_READ,
        StepPosition::STEP_POSITION_WRITE,
        self::STEP_READ,
        self::STEP_TEMPLATE_STEP_READ,
        self::STEP_WRITE,
        self::STEP_TEMPLATE_STEP_WRITE,
    ])]
    private ?StepPosition $stepPosition = null;

    public function __construct()
    {
        $this->parentSteps = new ArrayCollection();
        $this->steps = new ArrayCollection();
        $this->inputs = new ArrayCollection();
        $this->inheritors = new ArrayCollection();
        $this->nodes = new ArrayCollection();
        $this->dpps = new ArrayCollection();
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(File $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function getStepImage(): ?string
    {
        return $this->stepImage;
    }

    public function setStepImage(?string $stepImage): self
    {
        $this->stepImage = $stepImage;

        return $this;
    }

    public function getStepsTemplate(): ?StepsTemplate
    {
        return $this->stepsTemplate;
    }

    public function setStepsTemplate(?StepsTemplate $stepsTemplate): self
    {
        $this->stepsTemplate = $stepsTemplate;

        return $this;
    }

    /**
     * @return Collection<int, Step>
     */
    public function getParentSteps(): ?Collection
    {
        return $this->parentSteps;
    }

    /**
     * Returns an array containing the names of parent steps.
     *
     * @return string[] An array of parent step names.
     */
    #[Groups([
        self::STEP_READ,
        self::STEP_TEMPLATE_STEP_READ,
        self::PRODUCT_TEMPLATE_STEP_READ,
        self::LOGISTICS_TEMPLATE_STEP_READ,
        Node::NODE_READ,
    ])]
    public function getParentStepNames(): array
    {
        return $this->parentSteps->map(fn (Step $step) => $step->getName())->toArray();
    }

    public function addParentStep(Step $step): self
    {
        if (!$this->parentSteps->contains($step)) {
            $this->parentSteps->add($step);
            $step->addStep($this);
        }

        return $this;
    }

    public function removeParentStep(Step $step): self
    {
        if ($this->parentSteps->contains($step)) {
            $this->parentSteps->removeElement($step);
        }

        return $this;
    }

    public function getSort(): int
    {
        return $this->sort;
    }

    public function setSort(int $sort): self
    {
        $this->sort = $sort;

        return $this;
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
            $step->addParentStep($this);
        }

        return $this;
    }

    public function removeStep(Step $step): self
    {
        if ($this->steps->contains($step)) {
            $this->steps->removeElement($step);
            $step->removeParentStep($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Input>
     */
    public function getInputs(): Collection
    {
        return $this->inputs;
    }

    public function addInput(Input $input): self
    {
        if (!$this->inputs->contains($input)) {
            $this->inputs->add($input);
            $input->setStep($this);
        }

        return $this;
    }

    public function removeInput(Input $input): self
    {
        if ($this->inputs->contains($input)) {
            $this->inputs->removeElement($input);
        }

        return $this;
    }

    /**
     * @return Collection<int, ProductStep>
     */
    public function getInheritors(): Collection
    {
        return $this->inheritors;
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
            $node->addStep($this);
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

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getMeasurementType(): string
    {
        return $this->measurementType;
    }

    public function setMeasurementType(string $measurementType): self
    {
        $this->measurementType = $measurementType;

        return $this;
    }

    public function getUnitSymbol(): string
    {
        return $this->unitSymbol;
    }

    public function setUnitSymbol(string $unitSymbol): self
    {
        $this->unitSymbol = $unitSymbol;

        return $this;
    }

    public function getUnitMeasurement(): string
    {
        return $this->unitMeasurement;
    }

    public function setUnitMeasurement(string $unitMeasurement): self
    {
        $this->unitMeasurement = $unitMeasurement;

        return $this;
    }

    public function getBatchTypeOfStep(): string
    {
        return $this->batchTypeOfStep;
    }

    public function setBatchTypeOfStep(string $batchTypeOfStep): self
    {
        $this->batchTypeOfStep = $batchTypeOfStep;

        return $this;
    }

    public function getProcess(): Process
    {
        return $this->process;
    }

    public function setProcess(Process $process): self
    {
        $this->process = $process;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getProductTemplate(): ?ProductTemplate
    {
        return $this->productTemplate;
    }

    public function setProductTemplate(?ProductTemplate $productTemplate): self
    {
        $this->productTemplate = $productTemplate;

        return $this;
    }

    /**
     * @return Collection<int, Dpp>
     */
    public function getDpps(): Collection
    {
        return $this->dpps;
    }

    public function addDpp(Dpp $dpp): self
    {
        if (!$this->dpps->contains($dpp)) {
            $this->dpps[] = $dpp;
            $dpp->addStep($this);
        }

        return $this;
    }

    public function removeDpp(Dpp $dpp): self
    {
        if ($this->dpps->removeElement($dpp)) {
            $dpp->removeStep($this);
        }

        return $this;
    }

    public function getStepPosition(): ?StepPosition
    {
        return $this->stepPosition;
    }

    public function setStepPosition(?StepPosition $stepPosition): self
    {
        if ($stepPosition && $stepPosition->getStep() !== $this) {
            $stepPosition->setStep($this);
        }

        $this->stepPosition = $stepPosition;

        return $this;
    }

    /**
     * @return array<int, string>
     */
    #[Groups([
        self::STEP_READ,
        self::STEP_TEMPLATE_STEP_READ,
        self::PRODUCT_TEMPLATE_STEP_READ,
        Node::NODE_READ,
        self::SUPPLY_CHAIN_TEMPLATE_STEP_READ,
    ])]
    public function getParentStepIds(): array
    {
        $ids = [];

        foreach ($this->parentSteps as $parentStep) {
            $ids[] = $parentStep->getId()->jsonSerialize();
        }

        return $ids;
    }
}

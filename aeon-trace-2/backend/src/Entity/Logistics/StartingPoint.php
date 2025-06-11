<?php

declare(strict_types=1);

namespace App\Entity\Logistics;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'starting_point')]
class StartingPoint
{
    public const STARTING_POINT_READ = 'starting-point-read';
    public const STARTING_POINT_WRITE = 'starting-point-write';

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups([
        self::STARTING_POINT_READ,
    ])]
    private Uuid $id;

    #[ORM\Column(type: Types::FLOAT, options: ['default' => 0])]
    #[Groups([
        self::STARTING_POINT_READ,
        self::STARTING_POINT_WRITE,
        Logistics::LOGISTICS_WRITE_JSON,
    ])]
    private float $latitude;

    #[ORM\Column(type: Types::FLOAT, options: ['default' => 0])]
    #[Groups([
        self::STARTING_POINT_READ,
        self::STARTING_POINT_WRITE,
        Logistics::LOGISTICS_WRITE_JSON,
    ])]
    private float $longitude;

    /**
     * @var Collection<int, Logistics>
     */
    #[ORM\ManyToMany(targetEntity: Logistics::class, mappedBy: 'startingPointCoordinates')]
    #[Groups([
        self::STARTING_POINT_READ,
    ])]
    private Collection $logistics;

    public function __construct()
    {
        $this->logistics = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): void
    {
        $this->id = $id;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): self
    {
        $this->longitude = $longitude;

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
            $logistics->addStartingPointCoordinate($this);
        }

        return $this;
    }

    public function removeLogistics(Logistics $logistics): self
    {
        if ($this->logistics->removeElement($logistics)) {
            $logistics->removeStartingPointCoordinate($this);
        }

        return $this;
    }
}

<?php

declare(strict_types=1);

namespace App\Entity\TransportType;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\DataProviders\TransportTypeDataProvider;
use App\Entity\Company\Company;
use App\Entity\Logistics\LogisticsTemplate;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ApiResource(
    operations: [
        new GetCollection(
            provider: TransportTypeDataProvider::class,
        ),
        new Get(),
    ],
    normalizationContext: ['groups' => [self::TRANSPORT_TYPE_READ]],
    denormalizationContext: ['groups' => [self::TRANSPORT_TYPE_WRITE]],
)]
#[ORM\Entity]
class TransportType
{
    public const TRANSPORT_TYPE_READ = 'transport_type_read';
    public const TRANSPORT_TYPE_WRITE = 'transport_type_write';

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups([
        self::TRANSPORT_TYPE_READ,
        Company::COMPANY_READ,
        Company::COMPANY_READ_LISTING,
        LogisticsTemplate::LOGISTICS_TEMPLATE_READ,
    ])]
    private Uuid $id;

    #[ORM\Column(type: 'string', length: 50)]
    #[Groups([
        self::TRANSPORT_TYPE_READ,
        self::TRANSPORT_TYPE_WRITE,
        Company::COMPANY_READ,
        Company::COMPANY_READ_LISTING,
        LogisticsTemplate::LOGISTICS_TEMPLATE_READ,
    ])]
    private string $name;

    /**
     * @var Collection<int, LogisticsTemplate>
     */
    #[ORM\OneToMany(mappedBy: 'typeOfTransport', targetEntity: LogisticsTemplate::class)]
    private Collection $logisticsTemplates;

    public function __construct()
    {
        $this->logisticsTemplates = new ArrayCollection();
    }

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

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return Collection<int, LogisticsTemplate>
     */
    public function getLogisticsTemplates(): Collection
    {
        return $this->logisticsTemplates;
    }

    /**
     * Returns only id and name of associated LogisticsTemplates.
     * @return array<int, array{id: string, name: string}>
     */
    #[Groups([self::TRANSPORT_TYPE_READ])]
    public function getLogisticsTemplateData(): array
    {
        return $this->logisticsTemplates->map(function (LogisticsTemplate $logisticsTemplate) {
            return [
                'id' => (string) $logisticsTemplate->getId(),
                'name' => $logisticsTemplate->getName(),
            ];
        })->toArray();
    }

    public function addLogisticsTemplate(LogisticsTemplate $logisticsTemplate): self
    {
        if (!$this->logisticsTemplates->contains($logisticsTemplate)) {
            $this->logisticsTemplates[] = $logisticsTemplate;
            $logisticsTemplate->setTypeOfTransport($this);
        }

        return $this;
    }

    public function removeLogisticsTemplate(LogisticsTemplate $logisticsTemplate): self
    {
        if ($this->logisticsTemplates->removeElement($logisticsTemplate)) {
            if ($logisticsTemplate->getTypeOfTransport() === $this) {
                $logisticsTemplate->setTypeOfTransport(null);
            }
        }

        return $this;
    }
}

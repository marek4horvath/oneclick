<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Dpp;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Dpp\Dpp;
use App\Entity\Step\ProductStep;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource]
class DppGeoData
{
    #[Groups([
        Dpp::DPP_READ,
        ProductStep::STEP_READ,
    ])]
    private ?float $lat;

    #[Groups([
        Dpp::DPP_READ,
        ProductStep::STEP_READ,
    ])]
    private ?float $lng;

    #[Groups([
        Dpp::DPP_READ,
        ProductStep::STEP_READ,
    ])]
    private ?string $name;

    public function __construct(?float $latitude, ?float $longitude, ?string $name)
    {
        $this->lat = $latitude;
        $this->lng = $longitude;
        $this->name = $name;
    }

    public function getLat(): ?float
    {
        return $this->lat;
    }

    public function getLng(): ?float
    {
        return $this->lng;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}

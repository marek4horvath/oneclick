<?php declare(strict_types=1);

namespace App\Entity\Quirk;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Define GET and SET methods for entities that needs to have lat and lng
 */
trait HasLatLng
{
    #[ORM\Column(type: Types::FLOAT, options: [
        'default' => 0,
    ])]
    private float $latitude = 0;

    #[ORM\Column(type: Types::FLOAT, options: [
        'default' => 0,
    ])]
    private float $longitude = 0;

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): void
    {
        $this->latitude = $latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): void
    {
        $this->longitude = $longitude;
    }
}

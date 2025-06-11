<?php declare(strict_types=1);

namespace App\Entity\Collection;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\DataProviders\CountryDataProvider;

#[ApiResource(
    operations: [
        new GetCollection(
            provider: CountryDataProvider::class
        ),
    ]
)]
class Country
{
    /** @var array<string> */
    private array $countries;

    /**
     * @return array<string>
     */
    public function getCountries(): array
    {
        return $this->countries;
    }

    /**
     * @param array<string> $countries
     */
    public function setCountries(array $countries): void
    {
        $this->countries = $countries;
    }


}

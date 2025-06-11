<?php declare(strict_types=1);

namespace App\DataProviders;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Collection\Country;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @template T of Country
 * @implements ProviderInterface<Country>
 */
readonly class CountryDataProvider implements ProviderInterface
{
    public function __construct(
        private TranslatorInterface $translatorInterface,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $country = new Country();

        $countries = [
            'ALBANIA',
            'ANDORRA',
            'AUSTRIA',
            'BELARUS',
            'BELGIUM',
            'BOSNIA_AND_HERZEGOVINA',
            'BULGARIA',
            'CROATIA',
            'CYPRUS',
            'CZECHIA',
            'DENMARK',
            'ESTONIA',
            'FINLAND',
            'FRANCE',
            'GERMANY',
            'GREECE',
            'HUNGARY',
            'ICELAND',
            'IRELAND',
            'ITALY',
            'KOSOVO',
            'LATVIA',
            'LIECHTENSTEIN',
            'LITHUANIA',
            'LUXEMBOURG',
            'MALTA',
            'MOLDOVA',
            'MONACO',
            'MONTENEGRO',
            'NETHERLANDS',
            'NORTH_MACEDONIA',
            'NORWAY',
            'POLAND',
            'PORTUGAL',
            'ROMANIA',
            'RUSSIA',
            'SAN_MARINO',
            'SERBIA',
            'SLOVAKIA',
            'SLOVENIA',
            'SPAIN',
            'SWEDEN',
            'SWITZERLAND',
            'UKRAINE',
            'UNITED_KINGDOM',
            'VATICAN_CITY',
        ];

        $translatedCountries = [];

        foreach ($countries as $oneCountry) {
            $translatedCountries[] = $this->translatorInterface->trans($oneCountry, [], 'messages');
        }

        $country->setCountries($translatedCountries);

        return $country;
    }
}

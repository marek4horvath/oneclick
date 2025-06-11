<?php declare(strict_types=1);

namespace App\Services\Geo;

use App\Entity\Company\Company;
use App\Entity\Company\CompanySite;
use App\Entity\Product\Product;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class GeoService
{
    private const API_URL = 'GOOGLE_GEO_API_URL';
    private const API_KEY = 'GOOGLE_GEO_API_KEY';

    private string|bool $apiKey;
    private string|bool $apiUrl;
    public function __construct(
        private HttpClientInterface $httpClient,
        private ParameterBagInterface $parameterBag,
    ) {
        $apiKey = $this->parameterBag->get(self::API_KEY);
        $this->apiKey = is_array($apiKey) ? false : strval($apiKey);

        $apiUrl = $this->parameterBag->get(self::API_URL);
        $this->apiUrl = is_array($apiUrl) ? false : strval($apiUrl);
    }

    /**
     * @throws TransportExceptionInterface
     * @return array<float>
     */
    public function getGeoLocation(CompanySite|Company|Product $data): array
    {
        $address = $data->getAddress()->getHouseNo() . ' ' . $data->getAddress()->getStreet() . ', ' . $data->getAddress()->getCity() . ', ' . $data->getAddress()->getCountry();
        $geoData = $this->httpClient->request('GET', $this->apiUrl . '/maps/api/geocode/json?address=' . $address . '&key=' . $this->apiKey);

        try {
            $content = $geoData->getContent();
        } catch (ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|TransportExceptionInterface $e) {
            throw new BadRequestException($e->getMessage());
        }

        /** @var array<string[]> $contentArray */
        $contentArray = json_decode($content, true);

        if (!$contentArray) {
            throw new NotFoundHttpException('Address could not be found.');
        }

        if (!isset($contentArray['results']) || !isset($contentArray['results'][0])) {
            throw new NotFoundHttpException('Address could not be found.');
        }

        /** @var array<string[]> $results */
        $results = $contentArray['results'][0];

        if (count($results['geometry']) > 0) {

            /** @var array<float> $coordinates */
            $coordinates = $results['geometry']['location'];
        } else {
            throw new NotFoundHttpException('Address could not be found.');
        }

        return $coordinates;
    }
}

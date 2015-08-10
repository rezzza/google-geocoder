<?php

namespace Rezzza\GoogleGeocoder;

/**
 * @author Sébastien HOUZÉ <sebastien.houze@verylastroom.com>
 */
class GoogleGeocodeRepository implements GoogleGeocodeRepositoryInterface
{
    /**
     * @var GoogleGeocodeClient
     */
    private $client;

    public function __construct(GoogleGeocodeClient $client)
    {
        $this->client = $client;
    }

    public function findByPlaceIdWithLanguage($placeId, $language)
    {
        // placeId should return only one result
        $results = $this->client->executeQuery([
            'placeid' => $placeId,
            'language' => $language
        ]);

        if (count($results) <= 0) {
            return null;
        }

        return $results->first();
    }

    public function findByCoordinatesWithLanguage($latitude, $longitude, $language)
    {
        return $this->client->executeQuery([
            'latlng' => sprintf('%s,%s', $latitude, $longitude),
            'language' => $language
        ]);
    }

    public function findByAddressWithLanguage($address, $language)
    {
        return $this->client->executeQuery([
            'address' => $address,
            'language' => $language
        ]);
    }

    public function findByLocalityAndCountryCodeWithLanguage($locality, $countryCode, $language)
    {
        $components = join('|', [
            sprintf('country:%s', $countryCode),
            sprintf('locality:%s', $locality)
        ]);

        return $this->client->executeQuery([
            'components' => $components,
            'language' => $language
        ]);
    }

    public function findByLocalityAndCountryCodeAndAministrativeAreaWithLanguage($locality, $countryCode, $administrativeArea, $language)
    {
        $components = join('|', [
            sprintf('country:%s', $countryCode),
            sprintf('locality:%s', $locality),
            sprintf('administrative_area:%s', $administrativeArea)
        ]);

        return $this->client->executeQuery([
            'components' => $components,
            'language' => $language
        ]);
    }
}

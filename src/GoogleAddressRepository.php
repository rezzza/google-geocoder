<?php

namespace Rezzza\GoogleGeocoder;

/**
 * @author Sébastien HOUZÉ <sebastien.houze@verylastroom.com>
 */
class GoogleAddressRepository implements GoogleAddressRepositoryInterface
{
    /**
     * @var GoogleGeocodeClient
     */
    private $client;

    /** @var Model\AddressFactory */
    private $addressFactory;

    public function __construct(GoogleGeocodeClient $client, Model\AddressFactory $addressFactory)
    {
        $this->client = $client;
        $this->addressFactory = $addressFactory;
    }

    public function findByPlaceIdWithLanguage($placeId, $language)
    {
        // placeId should return only one result
        $results = $this->findAddressesBy([
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
        return $this->findAddressesBy([
            'latlng' => sprintf('%s,%s', $latitude, $longitude),
            'language' => $language
        ]);
    }

    public function findByAddressWithLanguage($address, $language)
    {
        return $this->findAddressesBy([
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

        return $this->findAddressesBy([
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

        return $this->findAddressesBy([
            'components' => $components,
            'language' => $language
        ]);
    }

    private function findAddressesBy(array $queryParams)
    {
        $json = $this->client->executeQuery($queryParams);

        return $this->addressFactory->createFromDecodedResultCollection(
            array_key_exists('results', $json) ? $json['results'] : [$json['result']]
        );
    }
}

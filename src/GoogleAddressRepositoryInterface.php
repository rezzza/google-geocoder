<?php

namespace Rezzza\GoogleGeocoder;

interface GoogleAddressRepositoryInterface
{
    /**
     * @param string $placeId
     * @param string $language
     *
     * @return \Rezzza\GoogleGeocoder\Model\AddressCollection
     */
    public function findByPlaceIdWithLanguage($placeId, $language);

    /**
     * @param double $latitude
     * @param double $longitude
     * @param string $language
     */
    public function findByCoordinatesWithLanguage($latitude, $longitude, $language);

    /**
     * @param string $address
     * @param string $language
     *
     * @return \Rezzza\GoogleGeocoder\Model\AddressCollection
     */
    public function findByAddressWithLanguage($address, $language);

    /**
     * @param string $locality
     * @param string $countryCode
     * @param string $language
     *
     * @return \Rezzza\GoogleGeocoder\Model\AddressCollection
     */
    public function findByLocalityAndCountryCodeWithLanguage($locality, $countryCode, $language);

    /**
     * @param string $locality
     * @param string $countryCode
     * @param string $administrativeArea
     * @param string $language
     *
     * @return \Rezzza\GoogleGeocoder\Model\AddressCollection
     */
    public function findByLocalityAndCountryCodeAndAministrativeAreaWithLanguage($locality, $countryCode, $administrativeArea, $language);
}

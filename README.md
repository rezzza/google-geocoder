# google-geocoder
[![Build Status](https://travis-ci.org/rezzza/google-geocoder.svg?branch=master)](https://travis-ci.org/rezzza/google-geocoder)
Simple lib to wrap Google geocoder Web Service

# Installation
```
$ composer require rezzza/google-geocoder
```

# Usage
You should build on your own the repository :
```php
$googleAddressRepository = new Rezzza\GoogleGeocoder\GoogleAddressRepository(
    new Rezzza\GoogleGeocoder\GoogleGeocodeClient(
        new Ivory\HttpAdapter\CurlHttpAdapter(),
        'YOUR_GOOGLE_API_KEY'
    ),
    new Rezzza\GoogleGeocoder\Model\AddressFactory
);
```

Then you can use all methods available in the repository :
* `findByPlaceIdWithLanguage($placeId, $language)`
* `findByCoordinatesWithLanguage($latitude, $longitude, $language)`
* `findByAddressWithLanguage($address, $language)`
* `findByLocalityAndCountryCodeWithLanguage($locality, $countryCode, $language)`
* `findByLocalityAndCountryCodeAndAministrativeAreaWithLanguage($locality, $countryCode, $administrativeArea, $language)`

All these methods will return a `Rezzza\GoogleGeocoder\Model\AddressCollection` except the first one that will return directly a `Rezzza\GoogleGeocoder\Model\Address` (as a placeId is uniq).

<?php

namespace Rezzza\GoogleGeocoder\Tests\Units\Model;

use mageekguy\atoum;

use Rezzza\GoogleGeocoder\Model\AddressFactory as SUT;

class AddressFactory extends atoum
{
    public function test_it_create_an_address_with_valid_payload()
    {
        $this
            ->given(
                $payload = json_decode(
                    '[
                        {
                            "address_components" : [
                                {
                                    "long_name" : "Paris",
                                    "short_name" : "Paris",
                                    "types" : [ "locality", "political" ]
                                },
                                {
                                    "long_name" : "Paris",
                                    "short_name" : "75",
                                    "types" : [ "administrative_area_level_2", "political" ]
                                },
                                {
                                    "long_name" : "Île-de-France",
                                    "short_name" : "IDF",
                                    "types" : [ "administrative_area_level_1", "political" ]
                                },
                                {
                                    "long_name" : "France",
                                    "short_name" : "FR",
                                    "types" : [ "country", "political" ]
                                }
                            ],
                            "formatted_address" : "Paris, France",
                            "name": "Paris",
                            "geometry" : {
                                "bounds" : {
                                    "northeast" : {
                                        "lat" : 48.9021449,
                                        "lng" : 2.4699208
                                    },
                                    "southwest" : {
                                        "lat" : 48.815573,
                                        "lng" : 2.224199
                                    }
                                },
                                "location" : {
                                    "lat" : 48.856614,
                                    "lng" : 2.3522219
                                },
                                "location_type" : "APPROXIMATE",
                                "viewport" : {
                                    "northeast" : {
                                        "lat" : 48.9021449,
                                        "lng" : 2.4699208
                                    },
                                    "southwest" : {
                                        "lat" : 48.815573,
                                        "lng" : 2.224199
                                    }
                                }
                            },
                            "place_id" : "ChIJD7fiBh9u5kcRYJSMaMOCCwQ",
                            "types" : [ "locality", "political" ]
                        }
                    ]',
                    true
                ),
                $sut = new SUT
            )
            ->when(
                $result = $sut->createFromDecodedResultCollection($payload)
            )
            ->then
                ->object($result)
                ->isInstanceOf('Rezzza\GoogleGeocoder\Model\AddressCollection')
            ->and
                ->integer($result->count())
                ->isEqualTo(1)
            ->and($address = $result->first())
                ->object($address)
                ->isInstanceOf('Rezzza\GoogleGeocoder\Model\Address')
                ->and
                    ->string($address->getPlaceId())
                    ->isIdenticalTo('ChIJD7fiBh9u5kcRYJSMaMOCCwQ')
                ->and
                    ->variable($address->getStreetNumber())
                    ->isNull()
                ->and
                    ->variable($address->getRoute())
                    ->isNull()
                ->and
                    ->variable($address->getPostalCode())
                    ->isNull()
                ->and
                    ->string($address->getLocality())
                    ->isIdenticalTo('Paris')
                    ->and($administrativeAreaCollection = $address->getAdministrativeAreas())
                        ->object($administrativeAreaCollection)
                        ->isInstanceOf('Rezzza\GoogleGeocoder\Model\AdministrativeAreaLevelCollection')
                    ->and
                        ->integer($administrativeAreaCollection->count())
                        ->isEqualTo(2)
                    ->and($areaLevel1 = $administrativeAreaCollection->get(1))
                        ->integer($areaLevel1->getLevel())
                        ->isEqualTo(1)
                        ->and
                            ->string($areaLevel1->getLongName())
                            ->isIdenticalTo('Île-de-France')
                        ->and
                            ->string($areaLevel1->getShortName())
                            ->isIdenticalTo('IDF')
                    ->and($areaLevel2 = $administrativeAreaCollection->get(2))
                        ->integer($areaLevel2->getLevel())
                        ->isEqualTo(2)
                        ->and
                            ->string($areaLevel2->getLongName())
                            ->isIdenticalTo('Paris')
                        ->and
                            ->string($areaLevel2->getShortName())
                            ->isIdenticalTo('75')
                    ->and
                        ->exception(function() use($administrativeAreaCollection) {
                            $administrativeAreaCollection->get(3);
                        })
                        ->isInstanceOf('Rezzza\GoogleGeocoder\Exception\GoogleGeocodeInvalidArgumentException')
                        ->hasMessage('Administrative level 3 is not set for this address.')
                ->and($country = $address->getCountry())
                    ->object($country)
                    ->isInstanceOf('Rezzza\GoogleGeocoder\Model\Country')
                    ->and
                        ->string($country->getLongName())
                        ->isIdenticalTo('France')
                    ->and
                        ->string($country->getShortName())
                        ->isIdenticalTo('FR')
                ->and($coordinates = $address->getCoordinates())
                    ->object($coordinates)
                    ->isInstanceOf('Rezzza\GoogleGeocoder\Model\Coordinates')
                    ->and
                        ->float($coordinates->getLatitude())
                        ->isIdenticalTo(48.856614)
                    ->and
                        ->float($coordinates->getLongitude())
                        ->isIdenticalTo(2.3522219)
                ->and($viewport = $address->getViewport())
                    ->object($viewport)
                    ->isInstanceOf('Rezzza\GoogleGeocoder\Model\Viewport')
                    ->and
                        ->and($southWest = $viewport->getSouthWest())
                            ->object($southWest)
                            ->isInstanceOf('Rezzza\GoogleGeocoder\Model\Coordinates')
                            ->and
                                ->float($southWest->getLatitude())
                                ->isIdenticalTo(48.815573)
                            ->and
                                ->float($southWest->getLongitude())
                                ->isIdenticalTo(2.224199)
                        ->and($northEast = $viewport->getNorthEast())
                            ->object($northEast)
                            ->isInstanceOf('Rezzza\GoogleGeocoder\Model\Coordinates')
                            ->and
                                ->float($northEast->getLatitude())
                                ->isIdenticalTo(48.9021449)
                            ->and
                                ->float($northEast->getLongitude())
                                ->isIdenticalTo(2.4699208)
                ->and($type = $address->getTypes())
                    ->phpArray($type)->containsValues(['locality', 'political'])
                ->and($name = $address->getName())
                    ->variable($name)->isEqualTo('Paris')
                ->and($formattedAddress = $address->getFormattedAddress())
                    ->variable($formattedAddress)->isEqualTo('Paris, France')
        ;
    }
}

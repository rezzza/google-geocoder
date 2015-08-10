<?php

namespace Rezzza\GoogleGeocoder\Tests\Units;

use mageekguy\atoum;
use Rezzza\GoogleGeocoder\Exception\GoogleGeocodeNoResultException;
use Rezzza\GoogleGeocoder\GoogleGeocodeClient as SUT;

/**
 * @author Sébastien HOUZÉ <sebastien.houze@verylastroom.com>
 */
class GoogleGeocodeClient extends atoum
{
    private $mockAdapter;

    private $messageFactory;

    private $apiKey;

    public function beforeTestMethod($method)
    {
        $this->apiKey = null;
        $this->mockAdapter = new \mock\Ivory\HttpAdapter\CurlHttpAdapter();
        $this->messageFactory = new \Ivory\HttpAdapter\Message\MessageFactory();
    }

    public function test_it_returns_valid_response_for_a_city_geocoding()
    {
        $this
            ->given(
                $apiKey = 'someApiKey',
                $response = $this->messageFactory->createResponse(
                    200,
                    \Ivory\HttpAdapter\Message\RequestInterface::PROTOCOL_VERSION_1_1,
                    ['Content-Type: application/json'],
                    '{
                        "results" : [
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
                        ],
                        "status" : "OK"
                    }'
                ),
                $this->calling($this->mockAdapter)->get = $response,
                $SUT = new SUT($this->mockAdapter, $apiKey)
            )
            ->when(
                $response = $SUT->executeQuery(
                    [
                        'language' => 'fr',
                        'components' => 'country:FR|locality:paris'
                    ]
                )
            )
            ->then
                ->object($response)
                ->isInstanceOf('Rezzza\GoogleGeocoder\Model\AddressCollection')
            ->and
                ->integer($response->count())
                ->isEqualTo(1)
            ->and($address = $response->first())
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
            ->and
                ->mock($this->mockAdapter)
                    ->call('get')
                        ->withIdenticalArguments(
                            'https://maps.googleapis.com/maps/api/geocode/json?key=someApiKey&language=fr&components=country%3AFR%7Clocality%3Aparis'
                            )
                            ->once()
            ;
    }

    public function test_that_throwed_exception_error_message_overrides_default()
    {
        $this
            ->given(
                $response = $this->messageFactory->createResponse(
                    200,
                    \Ivory\HttpAdapter\Message\RequestInterface::PROTOCOL_VERSION_1_1,
                    ['Content-Type: application/json'],
                    '{"results" : [], "status" : "UNKNOWN_ERROR", "error_message": "foobar"}'
                ),
                $this->calling($this->mockAdapter)->get = $response,
                $SUT = new SUT($this->mockAdapter, $this->apiKey)
            )
            ->then(
                $this->exception(function () use ($SUT) {
                    $SUT->executeQuery([]);
                })
                ->isInstanceOf('Rezzza\GoogleGeocoder\Exception\GoogleGeocodeException')
                ->hasMessage('foobar')
            );
    }

    public function test_it_throws_response_decode_exception()
    {
        $this
            ->given(
                $response = $this->messageFactory->createResponse(
                    200,
                    \Ivory\HttpAdapter\Message\RequestInterface::PROTOCOL_VERSION_1_1,
                    ['Content-Type: application/json'],
                    '{ "key": SomeInvalidJSONMuhahahahahaha!!!! }'
                ),
                $this->calling($this->mockAdapter)->get = $response,
                $SUT = new SUT($this->mockAdapter, $this->apiKey)
            )
            ->then(
                $this->exception(function() use($SUT) {
                    $SUT->executeQuery([]);
                })
                ->isInstanceOf('Rezzza\GoogleGeocoder\Exception\GoogleGeocodeResponseDecodeException')
                ->hasCode(4)
                ->message
                    ->matches('#^(Syntax error|unexpected character)$#')
            )
            ->and(
                $this->mock($this->mockAdapter)
                    ->call('get')
                        ->once()
            );
    }

    public function test_it_throws_no_result_exception()
    {
        $this
            ->given(
                $response = $this->messageFactory->createResponse(
                    200,
                    \Ivory\HttpAdapter\Message\RequestInterface::PROTOCOL_VERSION_1_1,
                    ['Content-Type: application/json'],
                    '{"results" : [], "status" : "ZERO_RESULTS"}'
                ),
                $this->calling($this->mockAdapter)->get = $response,
                $SUT = new SUT($this->mockAdapter, $this->apiKey)
            )
            ->then(
                $this->exception(function() use($SUT) {
                    $SUT->executeQuery(['address' => 'someInexistantAddress']);
                })
                ->isInstanceOf('Rezzza\GoogleGeocoder\Exception\GoogleGeocodeNoResultException')
                ->hasMessage('No result found.')
            )
            ->and(
                $this->mock($this->mockAdapter)
                    ->call('get')
                        ->withIdenticalArguments(
                            'https://maps.googleapis.com/maps/api/geocode/json?address=someInexistantAddress'
                        )
                            ->once()
            );
    }

    public function test_it_throws_protocol_exception()
    {
        $this
            ->given(
                $response = $this->messageFactory->createResponse(
                    500,
                    \Ivory\HttpAdapter\Message\RequestInterface::PROTOCOL_VERSION_1_1,
                    [],
                    'Oops'
                ),
                $this->calling($this->mockAdapter)->get = $response,
                $SUT = new SUT($this->mockAdapter, $this->apiKey)
            )
            ->then(
                $this->exception(function() use($SUT) {
                    $SUT->executeQuery([]);
                })
                ->isInstanceOf('Rezzza\GoogleGeocoder\Exception\GoogleGeocodeProtocolException')
                ->hasMessage('Internal Server Error')
                ->hasCode(500)
            )
            ->and(
                $this->mock($this->mockAdapter)
                    ->call('get')
                        ->once()
            );
    }

    public function test_it_throws_protocol_exception_from_http_adapter()
    {
        $this
            ->given(
                $SUT = new SUT($this->mockAdapter, $this->apiKey),
                $httpAdapterException = \Ivory\HttpAdapter\HttpAdapterException::cannotFetchUri(
                    'http://www.google.com',
                    get_class($this->mockAdapter),
                    'this is an error message'
                ),
                $this->calling($this->mockAdapter)->get->throw = $httpAdapterException
            )
            ->then(
                $this->exception(function() use($SUT) {
                    $SUT->executeQuery([]);
                })
                ->isInstanceOf('Rezzza\GoogleGeocoder\Exception\GoogleGeocodeProtocolException')
                ->hasMessage('An error occurred when fetching the URI "http://www.google.com" with the adapter "mock\Ivory\HttpAdapter\CurlHttpAdapter" ("this is an error message").')
            )
            ->and(
                $this->mock($this->mockAdapter)
                    ->call('get')
                        ->once()
            );
    }

    public function test_it_throws_quota_exceeded_exception()
    {
        $this
            ->given(
                $response = $this->messageFactory->createResponse(
                    200,
                    \Ivory\HttpAdapter\Message\RequestInterface::PROTOCOL_VERSION_1_1,
                    ['Content-Type: application/json'],
                    '{"results" : [], "status" : "OVER_QUERY_LIMIT"}'
                ),
                $this->calling($this->mockAdapter)->get = $response,
                $SUT = new SUT($this->mockAdapter, $this->apiKey)
            )
            ->then(
                $this->exception(function() use($SUT) {
                    $SUT->executeQuery(['latlng' => '42.1,2.1', 'language' => 'en']);
                })
                ->isInstanceOf('Rezzza\GoogleGeocoder\Exception\GoogleGeocodeQuotaExceededException')
                ->hasMessage('Query limit exceeded.')
            )
            ->and(
            $this->mock($this->mockAdapter)
                ->call('get')
                    ->withIdenticalArguments(
                        'https://maps.googleapis.com/maps/api/geocode/json?latlng=42.1%2C2.1&language=en'
                    )
                        ->once()
            );
    }

    public function test_it_throws_request_denied_exception()
    {
        $this
            ->given(
                $response = $this->messageFactory->createResponse(
                    200,
                    \Ivory\HttpAdapter\Message\RequestInterface::PROTOCOL_VERSION_1_1,
                    ['Content-Type: application/json'],
                    '{"results" : [], "status" : "REQUEST_DENIED"}'
                ),
                $this->calling($this->mockAdapter)->get = $response,
                $SUT = new SUT($this->mockAdapter, $this->apiKey)
            )
            ->then(
                $this->exception(function() use($SUT) {
                    $SUT->executeQuery([]);
                })
                ->isInstanceOf('Rezzza\GoogleGeocoder\Exception\GoogleGeocodeRequestDeniedException')
                ->hasMessage('Unauthorized request.')
            )
            ->and(
                $this->mock($this->mockAdapter)
                    ->call('get')
                        ->once()
            );
    }

    public function test_it_throws_invalid_request_exception()
    {
        $this
            ->given(
                $response = $this->messageFactory->createResponse(
                    200,
                    \Ivory\HttpAdapter\Message\RequestInterface::PROTOCOL_VERSION_1_1,
                    ['Content-Type: application/json'],
                    '{"results" : [], "status" : "INVALID_REQUEST"}'
                ),
                $this->calling($this->mockAdapter)->get = $response,
                $SUT = new SUT($this->mockAdapter, $this->apiKey)
            )
            ->then(
                $this->exception(function() use($SUT) {
                    $SUT->executeQuery([]);
                })
                ->isInstanceOf('Rezzza\GoogleGeocoder\Exception\GoogleGeocodeInvalidRequestException')
                ->hasMessage('Invalid request.')
            )
            ->and(
                $this->mock($this->mockAdapter)
                    ->call('get')
                        ->once()
            );
    }

    public function test_it_throws_unknown_exception()
    {
        $this
            ->given(
                $response = $this->messageFactory->createResponse(
                    200,
                    \Ivory\HttpAdapter\Message\RequestInterface::PROTOCOL_VERSION_1_1,
                    ['Content-Type: application/json'],
                    '{"results" : [], "status" : "UNKNOWN_ERROR", "error_message": "foobar"}'
                ),
                $this->calling($this->mockAdapter)->get = $response,
                $SUT = new SUT($this->mockAdapter, $this->apiKey)
            )
            ->then(
                $this->exception(function() use($SUT) {
                    $SUT->executeQuery([]);
                })
                ->isInstanceOf('Rezzza\GoogleGeocoder\Exception\GoogleGeocodeUnknownException')
                ->hasMessage('foobar')
            )
            ->and(
                $this->mock($this->mockAdapter)
                    ->call('get')
                        ->once()
            );
    }
}

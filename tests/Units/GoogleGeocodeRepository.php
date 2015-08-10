<?php

namespace Rezzza\GoogleGeocoder\Tests\Units;

use mageekguy\atoum;
use Rezzza\GoogleGeocoder\GoogleGeocodeRepository as SUT;

/**
 * @author Sébastien HOUZÉ <sebastien.houze@verylastroom.com>
 */
class GoogleGeocodeRepository extends atoum
{
    private $mockClient;

    public function beforeTestMethod($method)
    {
        $apiKey = null;
        $mockAdapter = new \mock\Ivory\HttpAdapter\CurlHttpAdapter();
        $messageFactory = new \Ivory\HttpAdapter\Message\MessageFactory();
        $response = $messageFactory->createResponse(
            200,
            \Ivory\HttpAdapter\Message\RequestInterface::PROTOCOL_VERSION_1_1,
            ['Content-Type: application/json'],
            '{
                "results" : [],
                "status" : "OK"
            }'
        );
        $this->calling($mockAdapter)->get = $response;
        $this->mockClient = new \mock\Rezzza\GoogleGeocoder\GoogleGeocodeClient($mockAdapter, $apiKey);
    }

    /*
     * @dataProvider
     */
    public function test_that_each_facade_method_calls_client_execute_query($method, $methodArgs, $clientExpectedArgs)
    {
        $this
            ->given(
                $SUT = new \mock\Rezzza\GoogleGeocoder\GoogleGeocodeRepository($this->mockClient)
            )
            ->when(
                $SUT->{$method}(...$methodArgs)
            )
            ->then(
                $this->mock($this->mockClient)
                        ->call('executeQuery')
                            ->withIdenticalArguments($clientExpectedArgs)
                                ->once()
            );
    }

    protected function test_that_each_facade_method_calls_client_execute_queryDataProvider()
    {
        return [
            // findByPlaceIdWithLanguage
            [
                'findByPlaceIdWithLanguage',
                ['PlaceIdHéhé', 'fr'],
                ['placeid' => 'PlaceIdHéhé', 'language' => 'fr']
            ],
            [
                'findByPlaceIdWithLanguage',
                ['PlaceIdHoho', 'it'],
                ['placeid' => 'PlaceIdHoho', 'language' => 'it']
            ],
            // findByCoordinatesWithLanguage
            [
                'findByCoordinatesWithLanguage',
                [48.85661400, 2.352221901, 'fr'],
                ['latlng' => '48.856614,2.352221901', 'language' => 'fr']
            ],
            [
                'findByCoordinatesWithLanguage',
                [0.0, 0.0, 'es'],
                ['latlng' => '0,0', 'language' => 'es']
            ],
            // findByAddressWithLanguage
            [
                'findByAddressWithLanguage',
                ['14 chemin du pont Marseille', 'fr'],
                ['address' => '14 chemin du pont Marseille', 'language' => 'fr']
            ],
            // findByLocalityAndCountryCodeWithLanguage
            [
                'findByLocalityAndCountryCodeWithLanguage',
                ['paris', 'FR', 'fr'],
                ['components' => 'country:FR|locality:paris', 'language' => 'fr']
            ],
            [
                'findByLocalityAndCountryCodeWithLanguage',
                ['Lyon', 'FR', 'en'],
                ['components' => 'country:FR|locality:Lyon', 'language' => 'en']
            ],
            // findByLocalityAndCountryCodeAndAministrativeAreaWithLanguage
            [
                'findByLocalityAndCountryCodeAndAministrativeAreaWithLanguage',
                ['Paris', 'FR', 'Île de France', 'fr'],
                ['components' => 'country:FR|locality:Paris|administrative_area:Île de France', 'language' => 'fr']
            ]
        ];
    }
}

<?php

namespace Rezzza\GoogleGeocoder\Tests\Units;

use mageekguy\atoum;
use Rezzza\GoogleGeocoder\GoogleAddressRepository as SUT;

/**
 * @author Sébastien HOUZÉ <sebastien.houze@verylastroom.com>
 */
class GoogleAddressRepository extends atoum
{
    private $mockClient;

    private $mockAdapter;

    public function beforeTestMethod($method)
    {
        $apiKey = null;
        $this->mockAdapter = new \mock\Ivory\HttpAdapter\CurlHttpAdapter();
        $this->mockClient = new \mock\Rezzza\GoogleGeocoder\GoogleGeocodeClient($this->mockAdapter, $apiKey);
    }

    /*
     * @dataProvider
     */
    public function test_that_each_facade_method_calls_client_execute_query($method, $methodArgs, $clientExpectedArgs)
    {
        $this
            ->given(
                $this->givenGoogleReturns('{
                    "results" : [],
                    "status" : "OK"
                }'),
                $SUT = new SUT($this->mockClient, new \Rezzza\GoogleGeocoder\Model\AddressFactory)
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
            ],
            // findLocalityByCoordinatesWithLanguage
            [
                'findLocalityByCoordinatesWithLanguage',
                [51.789464747, 3.354421901, 'fr'],
                ['latlng' => '51.789464747,3.354421901', 'result_type' => 'locality', 'language' => 'fr']
            ]
        ];
    }

    /**
     * @dataProvider allFacadeWithArgs
     */
    public function test_that_each_method_return_address_collection($method, $arguments)
    {
        $this
            ->given(
                $this->givenGoogleReturns('{
                    "results" : [],
                    "status" : "OK"
                }'),
                $addressFactory = new \mock\Rezzza\GoogleGeocoder\Model\AddressFactory,
                $expectedResult = new \Rezzza\GoogleGeocoder\Model\AddressCollection,
                $this->calling($addressFactory)->createFromDecodedResultCollection = $expectedResult,
                $sut = new SUT($this->mockClient, $addressFactory)
            )
            ->when(
                $result = $sut->{$method}(...$arguments)
            )
            ->then
                ->object($result)
                    ->isIdenticalTo($expectedResult)
        ;
    }

    protected function allFacadeWithArgs()
    {
        return [
            [
                'findByCoordinatesWithLanguage',
                [48.85661400, 2.352221901, 'fr']
            ],
            [
                'findByAddressWithLanguage',
                ['14 chemin du pont Marseille', 'fr']
            ],
            [
                'findByLocalityAndCountryCodeWithLanguage',
                ['paris', 'FR', 'fr']
            ],
            [
                'findByLocalityAndCountryCodeAndAministrativeAreaWithLanguage',
                ['Paris', 'FR', 'Île de France', 'fr']
            ]
        ];
    }

    public function test_by_place_id_return_null_if_no_result()
    {
        $this
            ->given(
                $this->givenGoogleReturns('{
                    "results" : [],
                    "status" : "OK"
                }'),
                $addressFactory = new \mock\Rezzza\GoogleGeocoder\Model\AddressFactory,
                $expectedResult = new \Rezzza\GoogleGeocoder\Model\AddressCollection,
                $this->calling($addressFactory)->createFromDecodedResultCollection = $expectedResult,
                $sut = new SUT($this->mockClient, $addressFactory)
            )
            ->when(
                $result = $sut->findByPlaceIdWithLanguage('LJLJ898', 'fr')
            )
            ->then
                ->variable($result)->isNull()
        ;
    }

    public function test_by_place_id_return_one_result_only()
    {
        $this
            ->given(
                $this->givenGoogleReturns('{
                    "results" : [],
                    "status" : "OK"
                }'),
                $addressFactory = new \mock\Rezzza\GoogleGeocoder\Model\AddressFactory,
                $expectedResult = new \Rezzza\GoogleGeocoder\Model\AddressCollection([
                    new \Rezzza\GoogleGeocoder\Model\Address('LKDJFLSDK87987', 'locality'),
                    new \Rezzza\GoogleGeocoder\Model\Address('KSDK898hjhYUY787', 'locality')
                ]),
                $this->calling($addressFactory)->createFromDecodedResultCollection = $expectedResult,
                $sut = new SUT($this->mockClient, $addressFactory)
            )
            ->when(
                $result = $sut->findByPlaceIdWithLanguage('LJLJ898', 'fr')
            )
            ->then
                ->object($result)->isInstanceOf('Rezzza\GoogleGeocoder\Model\Address')
        ;
    }

    public function test_locality_by_coordinates_returns_locality_result()
    {
        $this
            ->given(
                $this->givenGoogleReturns('{
                    "results" : [],
                    "status" : "OK"
                }'),
                $addressFactory = new \mock\Rezzza\GoogleGeocoder\Model\AddressFactory,
                $expectedResult = new \Rezzza\GoogleGeocoder\Model\AddressCollection([
                    new \Rezzza\GoogleGeocoder\Model\Address('CH7uJiKLOpKHgFfv', 'political'),
                    new \Rezzza\GoogleGeocoder\Model\Address('KSDK898hjhYUY787', 'locality'),
                    new \Rezzza\GoogleGeocoder\Model\Address('LKDJFLSDK87987', 'point-of-intereset')
                ]),
                $this->calling($addressFactory)->createFromDecodedResultCollection = $expectedResult,
                $sut = new SUT($this->mockClient, $addressFactory)
            )
            ->when(
                $result = $sut->findLocalityByCoordinatesWithLanguage(51.67849494, 3.848474747, 'fr')
            )
            ->then
                ->object($result)->isInstanceOf('Rezzza\GoogleGeocoder\Model\Address')
                ->variable($result->getPlaceId())->isEqualTo('KSDK898hjhYUY787')
        ;
    }

    private function givenGoogleReturns($payload)
    {
        $messageFactory = new \Ivory\HttpAdapter\Message\MessageFactory();
        $response = $messageFactory->createResponse(
            200,
            \Ivory\HttpAdapter\Message\RequestInterface::PROTOCOL_VERSION_1_1,
            ['Content-Type: application/json'],
            $payload
        );
        $this->calling($this->mockAdapter)->get = $response;
    }
}

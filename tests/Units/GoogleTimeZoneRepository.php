<?php

namespace Rezzza\GoogleGeocoder\Tests\Units;

use mageekguy\atoum;
use Rezzza\GoogleGeocoder\GoogleTimeZoneRepository as SUT;

class GoogleTimeZoneRepository extends atoum
{
    private $mockGeocodeClient;

    public function beforeTestMethod($method)
    {
        $this->mockGenerator->orphanize('__construct');
        $this->mockGeocodeClient = new \mock\Rezzza\GoogleGeocoder\GoogleGeocodeClient;
    }

    public function test_valid_response_leads_to_valid_time_zone()
    {
        $this
            ->given(
                $this->calling($this->mockGeocodeClient)->executeQuery = [
                    'dstOffset' => 3600,
                    'rawOffset' => 3600,
                    'timeZoneId' => 'Europe/Paris',
                    'timeZoneName' => 'Paris, France'
                ],
                $sut = new SUT($this->mockGeocodeClient)
            )
            ->when(
                $result = $sut->findByLocation(45.98081, 2.68769869)
            )
            ->then
                ->object($result)
                    ->isEqualTo(new \Rezzza\GoogleGeocoder\Model\TimeZone('Europe/Paris', 'Paris, France', 3600, 3600))
        ;
    }

    public function test_it_sends_correct_parameters_to_geocode_client()
    {
        $this
            ->given(
                $this->calling($this->mockGeocodeClient)->executeQuery = [
                    'dstOffset' => 3600,
                    'rawOffset' => 3600,
                    'timeZoneId' => 'Europe/Paris',
                    'timeZoneName' => 'Paris, France'
                ],
                $sut = new SUT($this->mockGeocodeClient),
                $this->function->time = 123456789
            )
            ->when(
                $result = $sut->findByLocation(45.98081, 2.68769869)
            )
            ->then
                ->mock($this->mockGeocodeClient)
                    ->call('executeQuery')
                    ->withArguments([
                        'location' => '45.98081,2.68769869',
                        'timestamp' => 123456789,
                        'sensor' => 'false'
                    ])
                    ->once()
        ;
    }

    /**
     * @dataProvider invalidJson
     */
    public function test_invalid_json_leads_to_exception($invalidJson)
    {
        $this
            ->given(
                $this->calling($this->mockGeocodeClient)->executeQuery = $invalidJson,
                $sut = new SUT($this->mockGeocodeClient)
            )
            ->exception(function () use ($sut) {
                $sut->findByLocation(45.98081, 2.68769869);
            })
                ->hasMessage('Missing mandatory parameters : dstOffset, rawOffset, timeZoneId or timeZoneName')
        ;
    }

    public function invalidJson()
    {
        return [
            [[]],
            [['dstOffset' => 3600]],
            [['timeZoneName' => 'Paris, France']],
            [['rawOffset' => 3600]],
            [['timeZoneId' => 'Europe/Paris']],
            [['dstOffset' => 3600, 'rawOffset' => 3600]],
            [['rawOffset' => 3600, 'timeZoneId' => 'Europe/Paris']],
            [['dstOffset' => 3600, 'timeZoneName' => 'Paris, France']],
            [['dstOffset' => 3600, 'timeZoneId' => 'Europe/Paris']],
            [['timeZoneId' => 'Europe/Paris', 'timeZoneName' => 'Paris, France']],
            [['rawOffset' => 3600, 'timeZoneName' => 'Paris, France']],
            [['rawOffset' => 3600, 'timeZoneId' => 'Europe/Paris', 'timeZoneName' => 'Paris, France']],
            [['dstOffset' => 3600, 'rawOffset' => 3600, 'timeZoneName' => 'Paris, France']],
            [['dstOffset' => 3600, 'timeZoneId' => 'Europe/Paris', 'timeZoneName' => 'Paris, France']],
            [['dstOffset' => 3600, 'rawOffset' => 3600, 'timeZoneId' => 'Europe/Paris']]
        ];
    }
}

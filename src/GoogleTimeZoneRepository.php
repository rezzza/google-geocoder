<?php

namespace Rezzza\GoogleGeocoder;

class GoogleTimeZoneRepository implements GoogleTimeZoneRepositoryInterface
{
    private $geocodeClient;

    public function __construct(GoogleGeocodeClient $geocodeClient)
    {
        $this->geocodeClient = $geocodeClient;
    }

    public function findByLocation($latitude, $longitude)
    {
        $json = $this->geocodeClient->executeQuery([
            'location' => sprintf('%s,%s', $latitude, $longitude),
            'timestamp' => time(),
            'sensor' => 'false'
        ]);

        if (
            !array_key_exists('dstOffset', $json) ||
            !array_key_exists('rawOffset', $json) ||
            !array_key_exists('timeZoneId', $json) ||
            !array_key_exists('timeZoneName', $json)
        ) {
            throw new \LogicException('Missing mandatory parameters : dstOffset, rawOffset, timeZoneId or timeZoneName');
        }

        return new Model\TimeZone($json['timeZoneId'], $json['timeZoneName'], $json['dstOffset'], $json['rawOffset']);
    }
}

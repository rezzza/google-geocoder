<?php

namespace Rezzza\GoogleGeocoder\Model;

/**
 * @author Sébastien HOUZÉ <sebastien.houze@verylastroom.com>
 */
final class Coordinates
{
    /**
     * @var double
     */
    private $latitude;

    /**
     * @var double
     */
    private $longitude;

    /**
     * @param double $latitude
     * @param double $longitude
     */
    public function __construct($latitude, $longitude)
    {
        $this->latitude  = $latitude;
        $this->longitude = $longitude;
    }

    /**
     * @return double
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @return double
     */
    public function getLongitude()
    {
        return $this->longitude;
    }
}

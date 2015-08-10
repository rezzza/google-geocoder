<?php

namespace Rezzza\GoogleGeocoder\Model;

/**
 * @author Sébastien HOUZÉ <sebastien.houze@verylastroom.com>
 */
final class Viewport
{
    /**
     * @var Coordinates
     */
    private $northEast;

    /**
     * @var Coordinates
     */
    private $southWest;

    public function __construct(Coordinates $northEast, Coordinates $southWest)
    {
        $this->northEast = $northEast;
        $this->southWest  = $southWest;
    }

    /**
     * @return Coordinates
     */
    public function getNorthEast()
    {
        return $this->northEast;
    }

    /**
     * @return Coordinates
     */
    public function getSouthWest()
    {
        return $this->southWest;
    }
}

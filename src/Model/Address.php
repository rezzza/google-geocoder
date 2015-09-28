<?php

namespace Rezzza\GoogleGeocoder\Model;

/**
 * @author Sébastien HOUZÉ <sebastien.houze@verylastroom.com>
 */
final class Address
{
    /**
     * @var string
     */
    private $placeId;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string|int
     */
    private $streetNumber;

    /**
     * @var string
     */
    private $route;

    /**
     * @var string
     */
    private $postalCode;

    /**
     * @var string
     */
    private $locality;

    /**
     * @var AdministrativeAreaLevelCollection
     */
    private $administrativeAreas;

    /**
     * @var Country
     */
    private $country;

    /*
     * @var Coordinates
     */
    private $coordinates;

    /**
     * @var Viewport
     */
    private $viewport;

    /**
     * @param string $placeId
     * @param string $streetNumber
     * @param string $route
     * @param string $postalCode
     * @param string $locality
     */
    public function __construct(
        $placeId,
        $type,
        $streetNumber = null,
        $route = null,
        $postalCode = null,
        $locality = null,
        AdministrativeAreaLevelCollection $administrativeAreas = null,
        Country $country = null,
        Coordinates $coordinates = null,
        Viewport $viewport = null
    )
    {
        $this->placeId = $placeId;
        $this->type = $type;
        $this->streetNumber = $streetNumber;
        $this->route = $route;
        $this->postalCode = $postalCode;
        $this->locality = $locality;
        $this->administrativeAreas = $administrativeAreas ?: new AdministrativeAreaLevelCollection();
        $this->country = $country;
        $this->coordinates = $coordinates;
        $this->viewport = $viewport;
    }

    /**
     * @return string
     */
    public function getPlaceId()
    {
        return $this->placeId;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string|int
     */
    public function getStreetNumber()
    {
        return $this->streetNumber;
    }

    /**
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * @return string
     */
    public function getLocality()
    {
        return $this->locality;
    }

    /**
     * @return AdministrativeAreaLevelCollection
     */
    public function getAdministrativeAreas()
    {
        return $this->administrativeAreas;
    }

    /**
     * @return Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @return Coordinates
     */
    public function getCoordinates()
    {
        return $this->coordinates;
    }

    /**
     * @return double
     */
    public function getLatitude()
    {
        if (null === $this->coordinates) {
            return null;
        }
        return $this->coordinates->getLatitude();
    }

    /**
     * @return double
     */
    public function getLongitude()
    {
        if (null === $this->coordinates) {
            return null;
        }
        return $this->coordinates->getLongitude();
    }

    /**
     * @return Viewport
     */
    public function getViewport()
    {
        return $this->viewport;
    }
}

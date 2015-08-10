<?php

namespace Rezzza\GoogleGeocoder\Model;

/**
 * @author Sébastien HOUZÉ <sebastien.houze@verylastroom.com>
 */
final class Country
{
    /**
     * @var string
     */
    private $longName;

    /**
     * @var string
     */
    private $shortName;

    /**
     * @param string $longName
     * @param string $shortName
     */
    public function __construct($longName, $shortName)
    {
        $this->longName = $longName;
        $this->shortName = $shortName;
    }

    /**
     * Returns the country longName
     *
     * @return string
     */
    public function getLongName()
    {
        return $this->longName;
    }

    /**
     * Returns the ISO 2 country code.
     *
     * @return string
     */
    public function getShortName()
    {
        return $this->shortName;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getLongName();
    }
}

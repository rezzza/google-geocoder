<?php

namespace Rezzza\GoogleGeocoder\Model;

/**
 * @author Sébastien HOUZÉ <sebastien.houze@verylastroom.com>
 */
final class AdministrativeAreaLevel
{
    /**
     * @var int
     */
    private $level;

    /**
     * @var string
     */
    private $longName;

    /**
     * @var string
     */
    private $shortName;

    /**
     * @param int    $level
     * @param string $longName
     * @param string $shortName
     */
    public function __construct($level, $longName, $shortName)
    {
        $this->level = $level;
        $this->longName = $longName;
        $this->shortName = $shortName;
    }

    /**
     * @return int Level number [1,5]
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @return string
     */
    public function getLongName()
    {
        return $this->longName;
    }

    /**
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

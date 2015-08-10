<?php

namespace Rezzza\GoogleGeocoder\Model;

class TimeZone
{
    private $id;

    private $name;

    private $dstOffset;

    private $rawOffset;

    public function __construct($id, $name, $dstOffset, $rawOffset)
    {
        $this->id = $id;
        $this->name = $name;
        $this->dstOffset = $dstOffset;
        $this->rawOffset = $rawOffset;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDstOffset()
    {
        return $this->dstOffset;
    }

    public function getRawOffset()
    {
        return $this->rawOffset;
    }
}

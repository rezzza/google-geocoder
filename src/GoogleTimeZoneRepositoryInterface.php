<?php

namespace Rezzza\GoogleGeocoder;

interface GoogleTimeZoneRepositoryInterface
{
    public function findByLocation($latitude, $longitude);
}

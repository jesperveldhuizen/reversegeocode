<?php

namespace Fieldhousen\ReverseGeocode;

/**
 * Class DistanceCalculator.
 */
class DistanceCalculator
{
    /** @var int */
    private const EARTH_RADIUS = 6371000;

    /**
     * @param float $latitudeFrom
     * @param float $longitudeFrom
     * @param float $latitudeTo
     * @param float $longitudeTo
     *
     * @return float
     */
    public static function calculate(float $latitudeFrom, float $longitudeFrom, float $latitudeTo, float $longitudeTo)
    {
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return round($angle * self::EARTH_RADIUS, 2);
    }
}

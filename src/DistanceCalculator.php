<?php

declare(strict_types=1);

namespace Fieldhousen\ReverseGeocode;

class DistanceCalculator
{
    private const EARTH_RADIUS = 6371000;

    public static function calculate(float $latitudeFrom, float $longitudeFrom, float $latitudeTo, float $longitudeTo): float
    {
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(sin($latDelta / 2) ** 2 + cos($latFrom) * cos($latTo) * sin($lonDelta / 2) ** 2));

        return round($angle * self::EARTH_RADIUS, 2);
    }
}

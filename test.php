<?php

namespace Fieldhousen\ReverseGeocode;

require_once 'vendor/autoload.php';

$key = $_SERVER['argv'][1] ?? null;
$lat = $_SERVER['argv'][2] ?? null;
$lng = $_SERVER['argv'][3] ?? null;

$geocoder = new ReverseGeocoder($key);
$address = $geocoder->geocode((float) $lat, (float)$lng);

print_r($address);

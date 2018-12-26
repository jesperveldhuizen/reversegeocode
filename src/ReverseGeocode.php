<?php

namespace Fieldhousen\ReverseGeocode;

/**
 * Class ReverseGeocode
 */
class ReverseGeocode
{

    /** @var string */
    private $key;

    /**
     * ReverseGeocode constructor.
     * @param string $key
     */
    public function __construct(string $key)
    {
        $this->key = $key;
    }

    /**
     * @param float $lat
     * @param float $lng
     * @return array|null
     */
    public function geocode(float $lat, float $lng)
    {
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=%s,%s&key=%s';
        $data = file_get_contents(sprintf($url, $lat, $lng, $this->key));

        $result = \json_decode($data, true);
        if (empty($result) || empty($result['results'])) {
            return null;
        }

        $street = null;
        $city = null;
        $country = null;

        foreach ($result['results'][0]['address_components'] as $component) {
            if (in_array('route', $component['types'])) {
                $street = $component['long_name'];
            }

            if (in_array('locality', $component['types'])) {
                $city = $component['long_name'];
            }

            if (in_array('country', $component['types'])) {
                $country = $component['short_name'];
            }
        }

        return [
            'street' => $street,
            'city' => $city,
            'country' => $country
        ];
    }
}

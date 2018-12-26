<?php

namespace Fieldhousen\ReverseGeocode;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class ReverseGeocode.
 */
class ReverseGeocode
{
    /** @var string */
    private $key;

    /** @var Client */
    private $client;

    /**
     * ReverseGeocode constructor.
     *
     * @param string $key
     */
    public function __construct(string $key)
    {
        $this->key = $key;
        $this->client = new Client(['base_uri' => 'https://maps.googleapis.com/maps/api/']);
    }

    /**
     * @param float $lat
     * @param float $lng
     *
     * @return array|null
     */
    public function geocode(float $lat, float $lng)
    {
        try {
            $url = 'geocode/json?latlng=%s,%s&key=%s';
            $response = $this->client->request('GET', sprintf($url, $lat, $lng, $this->key));
        } catch (\Exception | GuzzleException $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }

        $result = \json_decode($response->getBody()->getContents(), true);
        if (empty($result) || empty($result['results'])) {
            return null;
        }

        $types = [
            'route',
            'street_address',
            'point_of_interest',
            'postal_code',
            'locality',
        ];

        $results = $result['results'];
        foreach ($types as $type) {
            $address = $this->findByType($results, $type);
            if (null === $address) {
                continue;
            }

            return $this->extractAddress($address['address_components']);
        }

        return $this->extractAddress($results[0]['address_components']);
    }

    /**
     * @param array  $results
     * @param string $type
     *
     * @return array|null
     */
    private function findByType(array $results, string $type)
    {
        if (empty($results)) {
            return null;
        }

        foreach ($results as $result) {
            if (!in_array($type, $result['types'])) {
                continue;
            }

            return $result;
        }

        return null;
    }

    /**
     * @param array $components
     *
     * @return array
     */
    private function extractAddress(array $components)
    {
        $street = null;
        $city = null;
        $country = null;

        foreach ($components as $component) {
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
            'country' => $country,
        ];
    }
}

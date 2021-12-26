<?php

declare(strict_types=1);

namespace Fieldhousen\ReverseGeocode;

use Fieldhousen\ReverseGeocode\DTO\Address;
use Symfony\Component\HttpClient\HttpClient;

class ReverseGeocoder
{
    private const MAPS_URL = 'https://maps.googleapis.com';
    private const MAPS_PATH = '/maps/api/geocode/json?latlng=%s,%s&key=%s';

    public function __construct(
        private string $key
    ) {
    }

    public function geocode(float $lat, float $lng): ?Address
    {
        $client = HttpClient::createForBaseUri(self::MAPS_URL, [
            'verify_host' => false,
            'verify_peer' => false,
        ]);

        $url = sprintf(self::MAPS_PATH, $lat, $lng, $this->key);
        $response = $client->request('GET', $url);

        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException('No result!');
        }

        $body = json_decode($response->getContent(), true);
        if (!isset($body['results']) || count($body['results']) === 0) {
            return null;
        }

        $types = [
            'point_of_interest',
            'route',
            'street_address',
            'postal_code',
            'locality',
        ];

        $results = $body['results'];
        foreach ($types as $type) {
            $address = $this->findByType($results, $type);
            if (null === $address) {
                continue;
            }

            return $this->extractAddress($address['address_components']);
        }

        return $this->extractAddress($results[0]['address_components']);
    }

    private function findByType(array $results, string $type): ?array
    {
        if (count($results) === 0) {
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

    private function extractAddress(array $components): Address
    {
        $street = null;
        $streetNr = null;
        $city = null;
        $country = null;

        foreach ($components as $component) {
            if (in_array('route', $component['types'])) {
                $street = $component['long_name'];
            }

            if (in_array('street_number', $component['types'])) {
                $streetNr = $component['long_name'];
            }

            if (in_array('locality', $component['types'])) {
                $city = $component['long_name'];
            }

            if (in_array('country', $component['types'])) {
                $country = $component['short_name'];
            }
        }

        return new Address($street, $streetNr, $city, $country);
    }
}

<?php

declare(strict_types=1);

namespace Fieldhousen\ReverseGeocode\DTO;

class Address
{
    public function __construct(
        private ?string $street,
        private ?string $streetNr,
        private ?string $city,
        private ?string $country
    ) {
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function getStreetNr(): ?string
    {
        return $this->streetNr;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }
}

<?php

namespace App\Service;

use Geocoder\Provider\Provider;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Provider\Nominatim\Nominatim;

class GeocodingService
{
    private Provider $geocoder;

    public function __construct(Provider $nominatimGeocoder)
    {
        $this->geocoder = $nominatimGeocoder;
    }

    public function geocodeAddress(string $address): ?array
    {
        $results = $this->geocoder->geocodeQuery(GeocodeQuery::create($address));
        if ($results->isEmpty()) {
            return null;
        }

        $location = $results->first();
        return [
            'latitude' => $location->getCoordinates()->getLatitude(),
            'longitude' => $location->getCoordinates()->getLongitude(),
            'city' => $location->getLocality(),
            'postal_code' => $location->getPostalCode(),
            'country' => $location->getCountry()->getName(),
        ];
    }
}

<?php

namespace Rezzza\GoogleGeocoder\Model;

use Rezzza\GoogleGeocoder\Exception\GoogleGeocodeInvalidResultException;

/**
 * @author Sébastien HOUZÉ <sebastien.houze@verylastroom.com>
 */
class AddressFactory
{
    public function createFromDecodedResultCollection(array $results)
    {
        $addresses = [];

        foreach ($results as $result) {
            $addresses[] = $this->createAddressFromDecodedResult($result);
        }

        return new AddressCollection($addresses);
    }

    private function createAddressFromDecodedResult(array $result)
    {
        $this->guardAgainstInvalidResult($result);

        foreach ($result as $key => $section) {
            switch ($key) {
                case 'address_components':
                    list(
                        $streetNumber,
                        $route,
                        $postalCode,
                        $locality,
                        $administrativeAreas,
                        $country
                    ) = $this->createComponentsFromAddressComponents($section);
                break;
                case 'geometry';
                    $this->guardAgainstInvalidGeometry($section);

                    $viewport = $this->createViewportFromViewport($section['viewport']);
                    $coordinates = $this->createCoordinatesFromLocation(
                        $section['location']
                    );
                break;
                case 'place_id':
                    $placeId = $section;
                break;
            }
        }

        return new Address(
            $placeId,
            $streetNumber,
            $route,
            $postalCode,
            $locality,
            $administrativeAreas,
            $country,
            $coordinates,
            $viewport
        );
    }

    private function createComponentsFromAddressComponents($addressComponents)
    {
        $components = [
            'street_number' => null,
            'route' => null,
            'postal_code' => null,
            'locality' => null,
            'administrativeAreas' => null,
            'country' => null
        ];

        $parseComponents = function($components) {
            $administrativeAreas = [];

            foreach ($components as $component) {
                $this->guardAgainstInvalidAddressComponent($component);

                $primaryType = $component['types'][0];

                switch ($primaryType) {
                    case 'street_number':
                    case 'route':
                    case 'postal_code':
                    case 'locality':
                        yield $primaryType => (string) $component['long_name'];
                    break;

                    case 'administrative_area_level_1':
                    case 'administrative_area_level_2':
                    case 'administrative_area_level_3':
                    case 'administrative_area_level_4':
                    case 'administrative_area_level_5':
                        $administrativeAreas[] = new AdministrativeAreaLevel(
                            (int) substr($primaryType, -1, 1), // extract level
                            (string) $component['long_name'],
                            (string) $component['short_name']
                        );
                    break;

                    case 'country':
                            yield $primaryType => new Country(
                                $component['long_name'],
                                $component['short_name']
                            );
                    break;
                }
            }

            if (count($administrativeAreas) > 0) {
                yield 'administrativeAreas' => new AdministrativeAreaLevelCollection(
                    $administrativeAreas
                );
            }
        };

        foreach ($parseComponents($addressComponents) as $key => $value) {
            $components[$key] = $value;
        }

        return array_values($components);
    }

    private function createViewportFromViewport(array $viewport)
    {
        return new Viewport(
            new Coordinates(
                (double) $viewport['northeast']['lat'],
                (double) $viewport['northeast']['lng']
            ),
            new Coordinates(
                (double) $viewport['southwest']['lat'],
                (double) $viewport['southwest']['lng']
            )
        );
    }

    private function createCoordinatesFromLocation(array $location)
    {
        return new Coordinates((double) $location['lat'], (double) $location['lng']);
    }

    private function guardAgainstInvalidResult(array $result)
    {
        if (
            !array_key_exists('address_components', $result) ||
            !array_key_exists('geometry', $result) ||
            !array_key_exists('place_id', $result)
        ) {
            throw new GoogleGeocodeInvalidResultException(
                'One of the required result property (address_components, geometry, place_id) is missing.'
            );
        }
    }

    public function guardAgainstInvalidAddressComponent(array $addressComponent)
    {
        if (
            !array_key_exists('long_name', $addressComponent) ||
            !array_key_exists('short_name', $addressComponent) ||
            !array_key_exists('types', $addressComponent)
        ) {
            throw new GoogleGeocodeInvalidResultException(
                'One of the required result property (long_name, short_name, types) is missing.'
            );
        }
    }

    private function guardAgainstInvalidGeometry(array $geometry)
    {
        if (
            !array_key_exists('location', $geometry) ||
            !array_key_exists('viewport', $geometry)
        ) {
            throw new GoogleGeocodeInvalidResultException(
                'One of the required result.geometry property (location, viewport) is missing.'
            );
        }
    }
}

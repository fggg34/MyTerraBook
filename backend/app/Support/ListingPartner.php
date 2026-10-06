<?php

namespace App\Support;

use App\Models\Car;
use App\Models\Location;
use App\Services\Partners\GreenlightSettings;

class ListingPartner
{
    /**
     * @return array{id: string, name: string, logo_url: ?string}|null
     */
    public function fromCar(Car $car): ?array
    {
        $host = $car->relationLoaded('host') ? $car->host : $car->host()->first();
        if ($host) {
            return [
                'id' => 'host-'.$host->id,
                'name' => $host->name,
                'logo_url' => $host->company_logo_url,
            ];
        }

        if ($car->external_provider === GreenlightSettings::PROVIDER) {
            $settings = app(GreenlightSettings::class);

            return [
                'id' => GreenlightSettings::PROVIDER,
                'name' => $settings->displayName(),
                'logo_url' => $settings->logoUrl(),
            ];
        }

        return null;
    }

    public function nameForLocation(Location $location): ?string
    {
        if ($location->external_provider === GreenlightSettings::PROVIDER) {
            $name = app(GreenlightSettings::class)->displayName();

            return $name !== '' ? $name : null;
        }

        $name = trim((string) ($location->host?->name ?? ''));
        if ($name !== '') {
            return $name;
        }

        $cars = $location->relationLoaded('cars')
            ? $location->cars
            : $location->cars()->publiclyVisible()->with('host')->get();

        foreach ($cars as $car) {
            $partner = $this->fromCar($car);
            if ($partner !== null) {
                return $partner['name'];
            }
        }

        return null;
    }
}

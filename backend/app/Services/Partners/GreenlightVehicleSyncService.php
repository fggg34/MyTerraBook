<?php

namespace App\Services\Partners;

use App\Enums\DriveType;
use App\Enums\ListingApprovalStatus;
use App\Models\Car;
use App\Models\CarUnit;
use App\Models\DailyFare;
use App\Models\Location;
use App\Models\MainCategory;
use App\Models\PriceType;
use App\Models\SubCategory;
use Illuminate\Support\Str;

class GreenlightVehicleSyncService
{
    public function __construct(
        private readonly GreenlightPartnerClient $client,
        private readonly GreenlightSettings $settings,
        private readonly GreenlightMoney $money,
    ) {}

    /**
     * @return array{locations: int, vehicles: int, deactivated: int}
     */
    public function sync(): array
    {
        $locations = $this->syncLocations($this->client->locations());
        $this->settings->storeInsurancePlanIds(
            array_values(array_filter(array_map(
                fn (array $plan): string => (string) ($plan['id'] ?? ''),
                $this->client->insurancePlans(),
            ))),
        );

        $seenIds = [];
        $vehicleCount = 0;
        foreach ($this->client->vehicles() as $product) {
            $externalId = (string) ($product['id'] ?? '');
            if ($externalId === '') {
                continue;
            }
            $this->upsertVehicle($product, $locations);
            $seenIds[] = $externalId;
            $vehicleCount++;
        }

        $deactivated = 0;
        if ($seenIds !== []) {
            $deactivated = Car::query()
                ->where('external_provider', GreenlightSettings::PROVIDER)
                ->whereNotIn('external_vehicle_id', $seenIds)
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

        return [
            'locations' => count($locations),
            'vehicles' => $vehicleCount,
            'deactivated' => $deactivated,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return array<string, Location>
     */
    private function syncLocations(array $rows): array
    {
        $map = [];
        foreach ($rows as $row) {
            $externalId = (string) ($row['id'] ?? $row['locationId'] ?? '');
            if ($externalId === '') {
                continue;
            }

            $name = trim((string) ($row['name'] ?? '')) ?: 'Greenlight location';
            $location = Location::query()->firstOrNew([
                'external_provider' => GreenlightSettings::PROVIDER,
                'external_id' => $externalId,
            ]);

            if (! $location->exists) {
                $matched = $this->matchExistingLocation($name);
                if ($matched) {
                    $location = $matched;
                    $location->external_provider = GreenlightSettings::PROVIDER;
                    $location->external_id = $externalId;
                }
            }

            $address = trim(implode(', ', array_filter([
                (string) ($row['address'] ?? ''),
                (string) ($row['city'] ?? ''),
                (string) ($row['postalCode'] ?? ''),
            ])));

            $location->fill([
                'is_active' => true,
                'host_user_id' => $location->host_user_id,
            ]);
            if (! $location->exists || ! filled($location->name)) {
                $location->name = $name;
            }
            if (! filled($location->address) && $address !== '') {
                $location->address = $address;
            }
            if (! $location->exists) {
                $location->slug = Location::uniqueSlugFromName($name);
            }
            $location->save();
            $map[$externalId] = $location;
        }

        return $map;
    }

    /**
     * @param  array<string, mixed>  $product
     * @param  array<string, Location>  $locations
     */
    private function upsertVehicle(array $product, array $locations): Car
    {
        $externalId = (string) $product['id'];
        $name = trim((string) ($product['name'] ?? (trim(($product['make'] ?? '').' '.($product['model'] ?? ''))))) ?: 'Greenlight vehicle';
        $photos = array_values(array_filter(array_map('strval', $product['photos'] ?? [])));

        $car = Car::query()->firstOrNew([
            'external_provider' => GreenlightSettings::PROVIDER,
            'external_vehicle_id' => $externalId,
        ]);

        $car->fill([
            'user_id' => null,
            'sub_category_id' => $this->resolveSubCategory($product)->id,
            'name' => $name,
            'description' => $product['description'] ?? $car->description,
            'transmission' => $this->mapTransmission((string) ($product['transmission'] ?? '')),
            'fuel_type' => $this->mapFuel((string) ($product['fuelType'] ?? '')),
            'drive_type' => $this->mapDrive((string) ($product['drivetrain'] ?? '')),
            'seats' => (int) ($product['seatCount'] ?? 5),
            'sleeps' => (int) ($product['sleepingSpots'] ?? 0),
            'bags' => (int) ($product['luggageCapacity'] ?? 0),
            'year' => (int) ($product['year'] ?? 0) ?: null,
            'main_image_path' => $photos[0] ?? $car->main_image_path,
            'details_image_paths' => $photos !== [] ? $photos : $car->details_image_paths,
            'units_available' => max(1, (int) ($product['totalUnits'] ?? $product['freeCount'] ?? $car->units_available ?? 1)),
            'is_active' => true,
            'listing_status' => ListingApprovalStatus::Approved,
        ]);
        if (! $car->exists) {
            $car->slug = Car::uniqueSlugFromName($name);
        }
        $car->save();

        $this->syncVehicleLocations($car, $product, $locations);

        $this->syncFares($car, (int) ($product['baseDailyRateIsk'] ?? 0));
        $this->ensureUnit($car);

        return $car;
    }

    private function matchExistingLocation(string $name): ?Location
    {
        $slug = Str::slug($name);

        return Location::query()
            ->whereNull('host_user_id')
            ->where(function ($query): void {
                $query->whereNull('external_id')
                    ->orWhere('external_id', '');
            })
            ->where(function ($query) use ($name, $slug): void {
                $query->whereRaw('LOWER(name) = ?', [mb_strtolower($name)]);
                if ($slug !== '') {
                    $query->orWhere('slug', $slug);
                }
            })
            ->orderBy('id')
            ->first();
    }

    /**
     * @param  array<string, mixed>  $product
     * @param  array<string, Location>  $locations
     */
    private function syncVehicleLocations(Car $car, array $product, array $locations): void
    {
        if ($locations === []) {
            return;
        }

        $attachIds = $this->resolveVehicleLocationIds($product, $locations);
        $pivot = collect($attachIds)
            ->unique()
            ->mapWithKeys(fn (int $id): array => [$id => ['allows_pickup' => true, 'allows_dropoff' => true]])
            ->all();

        $car->locations()->sync($pivot);
    }

    /**
     * @param  array<string, mixed>  $product
     * @param  array<string, Location>  $locations
     * @return list<int>
     */
    private function resolveVehicleLocationIds(array $product, array $locations): array
    {
        $allIds = array_values(array_map(fn (Location $location): int => $location->id, $locations));

        if (filter_var($product['availableAtAllLocations'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            return $allIds;
        }

        $resolved = [];
        foreach ($this->externalLocationIdsFromProduct($product) as $externalId) {
            if (isset($locations[$externalId])) {
                $resolved[] = $locations[$externalId]->id;
            }
        }

        return $resolved !== [] ? array_values(array_unique($resolved)) : $allIds;
    }

    /**
     * @param  array<string, mixed>  $product
     * @return list<string>
     */
    private function externalLocationIdsFromProduct(array $product): array
    {
        $ids = collect();

        foreach ($product['locationIds'] ?? [] as $id) {
            $ids->push((string) $id);
        }

        foreach ($product['locations'] ?? [] as $row) {
            if (is_string($row) || is_numeric($row)) {
                $ids->push((string) $row);

                continue;
            }
            if (is_array($row)) {
                $ids->push((string) ($row['id'] ?? $row['locationId'] ?? ''));
            }
        }

        $home = (string) ($product['homeLocationId'] ?? '');
        if ($home !== '') {
            $ids->push($home);
        }

        return $ids
            ->map(fn (string $id): string => trim($id))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $product
     */
    private function resolveSubCategory(array $product): SubCategory
    {
        $sleeps = (int) ($product['sleepingSpots'] ?? 0);
        $main = MainCategory::ensureBySlug($sleeps > 0 ? 'campervan' : 'car', [
            'name' => $sleeps > 0 ? 'Campervan' : 'Car',
        ]);

        $slug = Str::slug((string) ($product['categorySlug'] ?? $product['categoryName'] ?? 'greenlight'));
        if ($slug === '') {
            $slug = 'greenlight';
        }

        return SubCategory::ensureBySlug($slug, $main->id, [
            'name' => (string) ($product['categoryName'] ?? 'Greenlight'),
            'is_search_filter' => true,
        ]);
    }

    private function syncFares(Car $car, int $dailyRateIsk): void
    {
        if ($dailyRateIsk <= 0) {
            return;
        }

        $cents = $this->money->fromIsk($dailyRateIsk)['cents'];
        $types = PriceType::query()->where('is_active', true)->orderBy('id')->get();
        if ($types->isEmpty()) {
            $types = collect([PriceType::query()->create(['name' => 'Basic', 'slug' => 'basic', 'is_active' => true])]);
        }

        foreach ($types as $index => $type) {
            $multiplier = 1 + ($index * 0.2);
            DailyFare::query()->updateOrCreate(
                [
                    'car_id' => $car->id,
                    'price_type_id' => $type->id,
                    'from_days' => 1,
                    'to_days' => 30,
                ],
                ['price_per_day_cents' => (int) round($cents * $multiplier)],
            );
        }
    }

    private function ensureUnit(Car $car): void
    {
        if ($car->carUnits()->exists()) {
            return;
        }

        CarUnit::query()->create([
            'car_id' => $car->id,
            'is_active' => true,
            'sort_order' => 0,
        ]);
    }

    private function mapTransmission(string $value): string
    {
        $normalized = strtolower($value);

        return str_contains($normalized, 'manual') ? 'manual' : 'automatic';
    }

    private function mapFuel(string $value): string
    {
        $normalized = strtolower($value);

        return match (true) {
            str_contains($normalized, 'diesel') => 'diesel',
            str_contains($normalized, 'electric') => 'electric',
            str_contains($normalized, 'hybrid') => 'hybrid',
            default => 'petrol',
        };
    }

    private function mapDrive(string $value): DriveType
    {
        $normalized = strtoupper(str_replace([' ', '-', '_'], '', $value));

        return match (true) {
            str_contains($normalized, '4WD') || str_contains($normalized, 'FOUR') => DriveType::FourByFour,
            str_contains($normalized, 'AWD') => DriveType::Awd,
            str_contains($normalized, 'RWD') => DriveType::Rwd,
            default => DriveType::Fwd,
        };
    }
}

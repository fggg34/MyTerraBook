<?php

namespace App\Services\Partners;

use App\Enums\DriveType;
use App\Enums\ListingApprovalStatus;
use App\Models\Car;
use App\Models\Location;
use App\Models\MainCategory;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CarenVehicleSyncService
{
    public function __construct(private readonly CarenPartnerClient $client) {}

    public function sync(User $host): array
    {
        return $this->importCatalogue($host, $this->client->catalogue($host));
    }

    public function importCatalogue(User $host, array $catalogue): array
    {
        foreach (array_merge($catalogue['vehicles'], $catalogue['pickups'], $catalogue['dropoffs']) as $row) {
            if (! is_array($row) || empty($row['Id']) || blank($row['Name'] ?? null)) {
                throw new RuntimeException('Caren returned a record without an ID or name. No listings were changed.');
            }
        }

        return DB::transaction(function () use ($host, $catalogue): array {
            $rentalId = (int) $catalogue['rental']['Id'];
            $host->forceFill(['partner_rental_id' => $rentalId])->save();
            $locations = [];
            foreach (['pickups' => 'allows_pickup', 'dropoffs' => 'allows_dropoff'] as $key => $permission) {
                foreach ($catalogue[$key] as $row) {
                    $location = Location::query()->firstOrNew([
                        'host_user_id' => $host->id, 'external_provider' => 'caren',
                        'external_id' => $rentalId.':'.$row['Id'],
                    ]);
                    $location->fill([
                        'name' => $row['Name'], 'description' => $row['Description'] ?? null,
                        'country_code' => $host->country_code ?: 'IS', 'is_active' => true,
                    ]);
                    $location->save();
                    $locations[$location->id] ??= ['allows_pickup' => false, 'allows_dropoff' => false];
                    $locations[$location->id][$permission] = true;
                }
            }

            $seen = [];
            $skipped = 0;
            foreach ($catalogue['vehicles'] as $row) {
                if (preg_match('/^test(?:\b|\d)/i', trim($row['Name']))) {
                    $skipped++;

                    continue;
                }

                $externalId = $rentalId.':'.$row['Id'];
                $car = Car::query()->firstOrNew([
                    'user_id' => $host->id, 'external_provider' => 'caren', 'external_vehicle_id' => $externalId,
                ]);
                $beds = max(0, (int) ($row['Beds'] ?? 0));
                $camper = $beds > 0 || str_contains(strtolower($row['Name']), 'camper') || str_contains(strtolower($row['Name']), 'rooftop');
                $main = MainCategory::ensureBySlug($camper ? 'campervan' : 'car', ['name' => $camper ? 'Campervan' : 'Car']);
                $sub = SubCategory::ensureBySlug('caren-'.$main->slug, $main->id, ['name' => 'Caren '.($camper ? 'campers' : 'cars'), 'is_search_filter' => true]);
                $fuel = strtolower($row['FuelName'] ?? '');
                $drive = strtoupper($row['DriveName'] ?? '');
                $photos = collect([$row['Image'] ?? null])->merge(collect($row['Images'] ?? [])->pluck('Url'))
                    ->filter()->map(fn ($url) => $this->imageUrl($host, $url))->filter()->unique()->values()->all();
                $car->fill([
                    'sub_category_id' => $sub->id, 'name' => trim($row['Name']),
                    'description' => $row['Text'] ?? $row['Summary'] ?? null,
                    'transmission' => str_contains(strtolower($row['TransmissionName'] ?? ''), 'automatic') || str_contains(strtolower($row['TransmissionName'] ?? ''), 'automated') ? 'automatic' : 'manual',
                    'fuel_type' => match (true) {
                        str_contains($fuel, 'hybrid') => 'hybrid',
                        str_contains($fuel, 'diesel') => 'diesel',
                        str_contains($fuel, 'electric') => 'electric',
                        default => 'petrol',
                    },
                    'drive_type' => match ($drive) {
                        '4WD', '4X4' => DriveType::FourByFour,
                        'AWD' => DriveType::Awd,
                        'RWD' => DriveType::Rwd,
                        default => DriveType::Fwd,
                    },
                    'seats' => (int) ($row['Seats'] ?? 0), 'sleeps' => $beds,
                    'bags' => (int) ($row['LargeBags'] ?? 0) + (int) ($row['SmallBags'] ?? 0),
                    'year' => preg_match('/\b(19|20)\d{2}\b/', (string) ($row['Model'] ?? ''), $year) ? (int) $year[0] : null,
                    'main_image_path' => $photos[0] ?? null, 'details_image_paths' => $photos,
                ]);
                if (! $car->exists) {
                    $car->fill(['is_active' => false, 'listing_status' => ListingApprovalStatus::Draft, 'units_available' => 0]);
                }
                $car->save();
                $car->locations()->sync($locations);
                $seen[] = $externalId;
            }

            // An empty catalogue may indicate a provider outage.
            $deactivated = $seen === [] ? 0 : $host->cars()->where('external_provider', 'caren')
                ->whereNotIn('external_vehicle_id', $seen)->where('is_active', true)->update(['is_active' => false]);

            return ['vehicles' => count($seen), 'locations' => count($locations), 'skipped' => $skipped, 'deactivated' => $deactivated];
        });
    }

    private function imageUrl(User $host, string $path): ?string
    {
        if (str_starts_with($path, '/') && ! str_starts_with($path, '//')) {
            return rtrim($host->partner_api_base_url ?: CarenPartnerClient::BASE_URL, '/').$path;
        }

        return filter_var($path, FILTER_VALIDATE_URL) && parse_url($path, PHP_URL_SCHEME) === 'https' ? $path : null;
    }
}

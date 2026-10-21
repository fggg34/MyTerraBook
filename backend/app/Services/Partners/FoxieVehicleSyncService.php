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

class FoxieVehicleSyncService
{
    public function __construct(private readonly FoxiePartnerClient $client) {}

    public function sync(User $host): array
    {
        return $this->importCatalogue($host, $this->client->catalogue($host));
    }

    /**
     * @param  array{locations: list<array<string, mixed>>, vehicles: list<array<string, mixed>>}  $catalogue
     * @return array{vehicles: int, locations: int, skipped: int, deactivated: int}
     */
    public function importCatalogue(User $host, array $catalogue): array
    {
        foreach ($catalogue['locations'] as $row) {
            if ($this->label($row) === null) {
                throw new RuntimeException('Foxie returned a location without an ID or name. No listings were changed.');
            }
        }
        foreach ($catalogue['vehicles'] as $row) {
            if ($this->label($row) === null) {
                throw new RuntimeException('Foxie returned a vehicle without an ID or name. No listings were changed.');
            }
        }

        return DB::transaction(function () use ($host, $catalogue): array {
            $locations = [];
            foreach ($catalogue['locations'] as $row) {
                [$externalId, $name] = $this->label($row);
                $location = Location::query()->firstOrNew([
                    'host_user_id' => $host->id,
                    'external_provider' => 'foxie',
                    'external_id' => $externalId,
                ]);
                $location->fill([
                    'name' => $name,
                    'address' => $this->text($row['address'] ?? null, 255),
                    'country_code' => $host->country_code ?: 'IS',
                    'latitude' => $this->coordinate($row['latitude'] ?? $row['lat'] ?? null),
                    'longitude' => $this->coordinate($row['longitude'] ?? $row['lng'] ?? null),
                    'default_opening_time' => '10:00:00',
                    'suggested_preselected_time' => '10:00:00',
                    'is_active' => true,
                ]);
                $location->save();
                $locations[$externalId] = $location->id;
            }

            $seen = [];
            $skipped = 0;
            foreach ($catalogue['vehicles'] as $row) {
                [$externalId, $name] = $this->label($row);
                if (preg_match('/^test(?:\b|\d)/i', $name)) {
                    $skipped++;

                    continue;
                }

                $car = Car::query()->firstOrNew([
                    'user_id' => $host->id,
                    'external_provider' => 'foxie',
                    'external_vehicle_id' => $externalId,
                ]);
                $camper = $this->isCamper($name, $row);
                $main = MainCategory::ensureBySlug($camper ? 'campervan' : 'car', [
                    'name' => $camper ? 'Campervan' : 'Car',
                ]);
                $sub = SubCategory::ensureBySlug('foxie-'.$main->slug, $main->id, [
                    'name' => 'Foxie '.($camper ? 'campers' : 'cars'),
                    'is_search_filter' => true,
                ]);
                $photos = $this->photos($row);
                $drive = str_replace(['×', '✕'], 'X', strtoupper((string) ($row['drive'] ?? $row['drive_type'] ?? '')));
                $fuel = strtolower((string) ($row['fuel'] ?? $row['fuel_type'] ?? ''));
                $car->fill([
                    'sub_category_id' => $sub->id,
                    'name' => $name,
                    'description' => $this->text($row['description'] ?? $row['summary'] ?? $row['text'] ?? null, 5000),
                    'transmission' => str_contains(strtolower((string) ($row['transmission'] ?? '')), 'automatic') ? 'automatic' : 'manual',
                    'fuel_type' => match (true) {
                        str_contains($fuel, 'hybrid') => 'hybrid',
                        str_contains($fuel, 'diesel') => 'diesel',
                        str_contains($fuel, 'electric') => 'electric',
                        default => 'petrol',
                    },
                    'drive_type' => match (true) {
                        str_contains($drive, '4X4') || str_contains($drive, '4WD') => DriveType::FourByFour,
                        str_contains($drive, 'AWD') => DriveType::Awd,
                        str_contains($drive, 'RWD') => DriveType::Rwd,
                        default => DriveType::Fwd,
                    },
                    'seats' => $this->whole($row['seats'] ?? $row['passengers'] ?? 0),
                    'sleeps' => $this->whole($row['sleeps'] ?? $row['beds'] ?? $row['berths'] ?? 0),
                    'bags' => $this->whole($row['bags'] ?? 0),
                    'year' => $this->year($row['year'] ?? null),
                    'main_image_path' => $photos[0] ?? null,
                    'details_image_paths' => $photos,
                    'pickup_time_from' => '10:00:00',
                    'dropoff_time_from' => '10:00:00',
                    'is_active' => true,
                    'listing_status' => ListingApprovalStatus::Approved,
                ]);
                if (! $car->exists) {
                    $car->units_available = $this->whole($row['units'] ?? 0);
                }
                $car->save();
                $car->locations()->sync($this->attachments($locations, $row));
                $seen[] = $externalId;
            }

            $deactivated = $seen === [] ? 0 : $host->cars()->where('external_provider', 'foxie')
                ->whereNotIn('external_vehicle_id', $seen)->where('is_active', true)->update(['is_active' => false]);

            return ['vehicles' => count($seen), 'locations' => count($locations), 'skipped' => $skipped, 'deactivated' => $deactivated];
        });
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array{0: string, 1: string}|null
     */
    private function label(array $row): ?array
    {
        $id = $row['id'] ?? null;
        $name = trim((string) ($row['name'] ?? $row['title'] ?? ''));
        if (! is_numeric($id) || (int) $id < 1 || $name === '') {
            return null;
        }

        return [(string) (int) $id, $name];
    }

    /**
     * @param  array<string, int>  $locations
     * @param  array<string, mixed>  $row
     * @return array<int, array{allows_pickup: bool, allows_dropoff: bool}>
     */
    private function attachments(array $locations, array $row): array
    {
        $pickups = $this->idList($row['pickup_locations'] ?? $row['pickupLocations'] ?? null);
        $dropoffs = $this->idList($row['return_locations'] ?? $row['dropoff_locations'] ?? $row['returnLocations'] ?? null);
        $attach = [];
        foreach ($locations as $externalId => $locationId) {
            $id = (int) $externalId;
            $allowsPickup = $pickups === null || in_array($id, $pickups, true);
            $allowsDropoff = $dropoffs === null || in_array($id, $dropoffs, true);
            if ($allowsPickup || $allowsDropoff) {
                $attach[$locationId] = ['allows_pickup' => $allowsPickup, 'allows_dropoff' => $allowsDropoff];
            }
        }

        return $attach;
    }

    /**
     * @return list<int>|null
     */
    private function idList(mixed $raw): ?array
    {
        if (! is_array($raw)) {
            return null;
        }
        $ids = [];
        foreach ($raw as $item) {
            if (is_numeric($item)) {
                $ids[] = (int) $item;
            } elseif (is_array($item) && is_numeric($item['id'] ?? null)) {
                $ids[] = (int) $item['id'];
            }
        }

        return $ids;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function isCamper(string $name, array $row): bool
    {
        $category = $row['category'] ?? '';
        if (is_array($category)) {
            $category = implode(' ', array_filter($category, 'is_string'));
        }
        $haystack = strtolower($name.' '.(is_string($category) ? $category : ''));
        if ((str_contains($haystack, 'minibus') || preg_match('/\bbus\b/', $haystack)) && ! str_contains($haystack, 'camper')) {
            return false;
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return list<string>
     */
    private function photos(array $row): array
    {
        $raw = [];
        foreach (['image', 'image_url', 'thumbnail', 'thumbnail_url'] as $key) {
            if (is_string($row[$key] ?? null) && $row[$key] !== '') {
                $raw[] = $row[$key];
            }
        }
        foreach (['images', 'photos'] as $key) {
            if (! is_array($row[$key] ?? null)) {
                continue;
            }
            foreach ($row[$key] as $item) {
                if (is_string($item) && $item !== '') {
                    $raw[] = $item;
                } elseif (is_array($item) && is_string($item['url'] ?? $item['src'] ?? null)) {
                    $raw[] = $item['url'] ?? $item['src'];
                }
            }
        }

        return collect($raw)->map(fn (string $url) => $this->imageUrl($url))->filter()->unique()->values()->all();
    }

    private function imageUrl(string $path): ?string
    {
        if (str_starts_with($path, '/') && ! str_starts_with($path, '//')) {
            return 'https://foxiecampers.is'.$path;
        }

        return filter_var($path, FILTER_VALIDATE_URL) && parse_url($path, PHP_URL_SCHEME) === 'https' ? $path : null;
    }

    private function text(mixed $value, int $limit): ?string
    {
        if (! is_string($value)) {
            return null;
        }
        $text = trim(preg_replace('/\s+/', ' ', strip_tags($value)) ?? '');

        return $text === '' ? null : mb_substr($text, 0, $limit);
    }

    private function coordinate(mixed $value): ?string
    {
        if (! is_numeric($value)) {
            return null;
        }
        $number = (float) $value;

        return $number >= -180 && $number <= 180 ? (string) $number : null;
    }

    private function whole(mixed $value): int
    {
        return is_numeric($value) ? max(0, (int) $value) : 0;
    }

    private function year(mixed $value): ?int
    {
        $year = $this->whole($value);

        return $year >= 1980 && $year <= 2100 ? $year : null;
    }
}

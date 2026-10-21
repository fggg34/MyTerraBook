<?php

namespace App\Services\Partners;

use App\Models\Car;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Throwable;

class CarenAvailabilityService
{
    public function __construct(private readonly CarenPartnerClient $client) {}

    /**
     * @return array{blocked_dates: list<string>, blocked: list<array<string, mixed>>, booked: list<array<string, mixed>>, units_available: int}
     */
    public function forCar(Car $car, ?int $pickupLocationId, ?int $dropoffLocationId): array
    {
        $host = $car->host;
        if (! $host || $car->external_provider !== 'caren' || ! $host->partner_api_enabled
            || $host->partner_provider !== 'caren'
            || ! preg_match('/^(\d+):(\d+)$/', (string) $car->external_vehicle_id, $match)) {
            throw new RuntimeException('Caren availability could not be checked.');
        }

        $rentalId = (int) $match[1];
        $classId = (int) $match[2];
        $from = now()->startOfDay();
        $to = now()->startOfDay()->addDays(365);
        $classIds = $this->classIds($host->id, $rentalId, $classId);
        $key = 'caren-calendar:'.hash('sha256', json_encode([
            $host->id, $host->partner_api_base_url, $host->partner_api_username,
            $host->partner_api_key, $rentalId, $classIds, $from->toDateString(), $to->toDateString(),
        ]));

        try {
            $bundle = Cache::remember($key, 600, fn () => $this->client->rentalCalendar($host, $rentalId, $classIds, $from, $to));
        } catch (Throwable) {
            throw new RuntimeException('Caren availability could not be checked.');
        }

        return $this->present($bundle, $rentalId, $classId, $car, $pickupLocationId, $dropoffLocationId, $from, $to);
    }

    /**
     * @return list<int>
     */
    private function classIds(int $hostId, int $rentalId, int $classId): array
    {
        $ids = [$classId];
        $vehicles = Car::query()
            ->where('user_id', $hostId)
            ->where('external_provider', 'caren')
            ->where('external_vehicle_id', 'like', $rentalId.':%')
            ->pluck('external_vehicle_id');

        foreach ($vehicles as $externalId) {
            if (preg_match('/^'.preg_quote((string) $rentalId, '/').':(\d+)$/', (string) $externalId, $match)) {
                $ids[] = (int) $match[1];
            }
        }

        $ids = array_values(array_unique(array_filter($ids, static fn (int $id) => $id > 0)));
        sort($ids);

        return $ids;
    }

    /**
     * @param  array{availability?: mixed, pickups?: mixed, dropoffs?: mixed}  $bundle
     * @return array{blocked_dates: list<string>, blocked: list<array<string, mixed>>, booked: list<array<string, mixed>>, units_available: int}
     */
    private function present(
        array $bundle,
        int $rentalId,
        int $classId,
        Car $car,
        ?int $pickupLocationId,
        ?int $dropoffLocationId,
        Carbon $from,
        Carbon $to,
    ): array {
        $windowStart = $from->toDateString();
        $windowEnd = $to->toDateString();
        $blocked = $this->unavailableClassDates($bundle['availability'] ?? [], $classId, $windowStart, $windowEnd);
        $units = $this->unitsAvailable($bundle['availability'] ?? [], $classId);

        $externalIds = $this->selectedExternalIds($car, $rentalId, $pickupLocationId, $dropoffLocationId);
        $pickupIds = $externalIds['pickup'];
        $dropoffIds = $externalIds['dropoff'];
        foreach ($this->closuresFor($bundle['pickups'] ?? [], $pickupIds) as $range) {
            $blocked = array_merge($blocked, $this->expandRange($range, $windowStart, $windowEnd));
        }
        foreach ($this->closuresFor($bundle['dropoffs'] ?? [], $dropoffIds) as $range) {
            $blocked = array_merge($blocked, $this->expandRange($range, $windowStart, $windowEnd));
        }

        $blocked = array_values(array_unique($blocked));
        sort($blocked);

        return [
            'blocked_dates' => $blocked,
            'blocked' => array_map(static function (string $day): array {
                return [
                    'id' => $day,
                    'source' => 'caren',
                    'start' => $day.'T00:00:00',
                    'end' => $day.'T00:00:00',
                    'units_blocked' => 1,
                    'notes' => 'Unavailable in Caren',
                ];
            }, $blocked),
            'booked' => [],
            'units_available' => $units,
        ];
    }

    /**
     * A day is blocked when Caren marks the class unavailable or has no free units.
     *
     * @return list<string>
     */
    private function unavailableClassDates(mixed $availability, int $classId, string $windowStart, string $windowEnd): array
    {
        if (! is_array($availability)) {
            return [];
        }

        $dates = [];
        foreach ($availability as $row) {
            if (! is_array($row) || (int) ($row['ClassId'] ?? 0) !== $classId) {
                continue;
            }
            foreach ($row['DatesAvailability'] ?? [] as $day) {
                if (! is_array($day)) {
                    continue;
                }
                $date = $this->dateKey($day['Date'] ?? null);
                if (! $date || $date < $windowStart || $date > $windowEnd) {
                    continue;
                }
                $available = ($day['IsAvailable'] ?? false) === true && (int) ($day['AvailableUnits'] ?? 0) > 0;
                if (! $available) {
                    $dates[] = $date;
                }
            }
        }

        return $dates;
    }

    private function unitsAvailable(mixed $availability, int $classId): int
    {
        if (! is_array($availability)) {
            return 0;
        }

        $units = 0;
        foreach ($availability as $row) {
            if (! is_array($row) || (int) ($row['ClassId'] ?? 0) !== $classId) {
                continue;
            }
            foreach ($row['DatesAvailability'] ?? [] as $day) {
                if (is_array($day)) {
                    $units = max($units, (int) ($day['AvailableUnits'] ?? 0));
                }
            }
        }

        return $units;
    }

    /**
     * @return array{pickup: list<int>, dropoff: list<int>}
     */
    private function selectedExternalIds(Car $car, int $rentalId, ?int $pickupLocationId, ?int $dropoffLocationId): array
    {
        $wanted = array_values(array_filter([$pickupLocationId, $dropoffLocationId]));
        if ($wanted === []) {
            return ['pickup' => [], 'dropoff' => []];
        }

        $locations = $car->locations()
            ->where('locations.is_active', true)
            ->where('external_provider', 'caren')
            ->where('host_user_id', $car->user_id)
            ->whereIn('locations.id', $wanted)
            ->get();

        $pickup = [];
        $dropoff = [];
        foreach ($locations as $location) {
            if (! preg_match('/^'.preg_quote((string) $rentalId, '/').':(\d+)$/', (string) $location->external_id, $match)) {
                continue;
            }
            $externalId = (int) $match[1];
            if ((int) $location->id === $pickupLocationId && $location->pivot?->allows_pickup) {
                $pickup[] = $externalId;
            }
            if ((int) $location->id === $dropoffLocationId && $location->pivot?->allows_dropoff) {
                $dropoff[] = $externalId;
            }
        }

        return ['pickup' => $pickup, 'dropoff' => $dropoff];
    }

    /**
     * @param  list<int>  $externalIds
     * @return list<array<string, mixed>>
     */
    private function closuresFor(mixed $locations, array $externalIds): array
    {
        if (! is_array($locations) || $externalIds === []) {
            return [];
        }

        $wanted = array_fill_keys($externalIds, true);
        $ranges = [];
        foreach ($locations as $location) {
            if (! is_array($location) || ! isset($wanted[(int) ($location['Id'] ?? 0)])) {
                continue;
            }
            foreach ($location['ClosedDates'] ?? [] as $range) {
                if (is_array($range)) {
                    $ranges[] = $range;
                }
            }
        }

        return $ranges;
    }

    /**
     * @param  array<string, mixed>  $range
     * @return list<string>
     */
    private function expandRange(array $range, string $windowStart, string $windowEnd): array
    {
        $from = $this->dateKey($range['From'] ?? null);
        $to = $this->dateKey($range['To'] ?? null);
        if (! $from || ! $to || $to < $from) {
            return [];
        }

        $start = max($from, $windowStart);
        $end = min($to, $windowEnd);
        if ($end < $start) {
            return [];
        }

        $dates = [];
        $cursor = Carbon::parse($start);
        $last = Carbon::parse($end);
        $guard = 0;
        while ($cursor->lte($last) && $guard < 400) {
            $dates[] = $cursor->toDateString();
            $cursor->addDay();
            $guard++;
        }

        return $dates;
    }

    private function dateKey(mixed $value): ?string
    {
        if (! is_string($value) || ! preg_match('/^(\d{4}-\d{2}-\d{2})/', $value, $match)) {
            return null;
        }

        return $match[1];
    }
}

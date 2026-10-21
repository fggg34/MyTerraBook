<?php

namespace App\Services\Partners;

use App\Models\Car;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Throwable;

class FoxieAvailabilityService
{
    public function __construct(private readonly FoxiePartnerClient $client) {}

    /**
     * @return array{blocked_dates: list<string>, blocked: list<array<string, mixed>>, booked: list<array<string, mixed>>, units_available: int}
     */
    public function forCar(Car $car): array
    {
        $host = $car->host;
        if (! $host || $car->external_provider !== 'foxie' || ! $host->partner_api_enabled
            || $host->partner_provider !== 'foxie'
            || ! preg_match('/^[1-9]\d*$/', (string) $car->external_vehicle_id)) {
            throw new RuntimeException('Foxie availability could not be checked.');
        }

        $from = now()->startOfDay();
        $to = now()->startOfDay()->addDays(365);
        $vehicleId = (int) $car->external_vehicle_id;
        $key = 'foxie-calendar:'.hash('sha256', json_encode([
            $host->id, $host->partner_api_key, $vehicleId, $from->toDateString(), $to->toDateString(),
        ]));

        try {
            $detail = Cache::remember($key, 600, fn () => $this->client->vehicle($host, $vehicleId));
        } catch (Throwable) {
            throw new RuntimeException('Foxie availability could not be checked.');
        }

        $dates = $this->blockedDates($detail, $from, $to);
        $blocked = array_map(fn (string $day) => [
            'id' => $vehicleId.'-'.$day,
            'source' => 'foxie',
            'start' => $day,
            'end' => $day,
            'units_blocked' => 1,
            'notes' => null,
        ], $dates);

        return [
            'booked' => [],
            'blocked' => $blocked,
            'blocked_dates' => $dates,
            'units_available' => max(0, (int) ($detail['units'] ?? $car->units_available)),
        ];
    }

    /**
     * @param  array<string, mixed>  $detail
     * @return list<string>
     */
    private function blockedDates(array $detail, Carbon $from, Carbon $to): array
    {
        $availability = is_array($detail['availability'] ?? null) ? $detail['availability'] : [];
        $sources = [
            $detail['blocked_dates'] ?? null,
            $detail['blockedDates'] ?? null,
            $detail['unavailable_dates'] ?? null,
            $detail['unavailableDates'] ?? null,
            $detail['blocked'] ?? null,
            $availability['blocked_dates'] ?? null,
            $availability['blocked'] ?? null,
        ];
        $dates = [];
        foreach ($sources as $raw) {
            if (! is_array($raw)) {
                continue;
            }
            foreach ($raw as $item) {
                $this->collect($dates, $item, $from, $to);
            }
        }
        $list = array_keys($dates);
        sort($list);

        return $list;
    }

    /**
     * @param  array<string, true>  $dates
     */
    private function collect(array &$dates, mixed $item, Carbon $from, Carbon $to): void
    {
        if (is_string($item)) {
            $this->add($dates, $item, $from, $to);

            return;
        }
        if (! is_array($item)) {
            return;
        }
        if (array_key_exists('available', $item) && $item['available'] === true) {
            return;
        }
        if (array_key_exists('is_available', $item) && $item['is_available'] === true) {
            return;
        }
        $single = $item['date'] ?? $item['Date'] ?? null;
        if (is_string($single)) {
            $this->add($dates, $single, $from, $to);

            return;
        }
        $start = $item['from'] ?? $item['From'] ?? $item['start'] ?? $item['Start'] ?? null;
        $end = $item['to'] ?? $item['To'] ?? $item['end'] ?? $item['End'] ?? null;
        if (! is_string($start) || ! is_string($end)) {
            return;
        }
        try {
            $cursor = Carbon::parse(substr($start, 0, 10))->startOfDay();
            $last = Carbon::parse(substr($end, 0, 10))->startOfDay();
        } catch (Throwable) {
            return;
        }
        if ($last->lt($cursor)) {
            return;
        }
        for ($guard = 0; $cursor->lte($last) && $guard < 400; $guard++) {
            $this->add($dates, $cursor->toDateString(), $from, $to);
            $cursor->addDay();
        }
    }

    /**
     * @param  array<string, true>  $dates
     */
    private function add(array &$dates, string $value, Carbon $from, Carbon $to): void
    {
        if (! preg_match('/^(\d{4}-\d{2}-\d{2})/', $value, $match)) {
            return;
        }
        $day = $match[1];
        if ($day < $from->toDateString() || $day > $to->toDateString()) {
            return;
        }
        $dates[$day] = true;
    }
}

<?php

namespace App\Services;

use App\Models\Location;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class LocationHoursService
{
    /**
     * @return array{from: string, to: string}|null  HH:MM strings
     */
    public function effectiveDailyWindow(Location $location): ?array
    {
        $from = $this->normalizeTime($location->default_opening_time);
        $to = $this->normalizeTime($location->default_closing_time);

        if ($from === null || $to === null) {
            return null;
        }

        return ['from' => $from, 'to' => $to];
    }

    /**
     * @param  Collection<int, Location>  $locations
     * @return array{from: string, to: string}|null
     */
    public function intersectionForLocations(Collection $locations): ?array
    {
        if ($locations->isEmpty()) {
            return null;
        }

        $fromMinutes = null;
        $toMinutes = null;

        foreach ($locations as $location) {
            $window = $this->effectiveDailyWindow($location);
            if ($window === null) {
                return null;
            }

            $open = $this->timeToMinutes($window['from']);
            $close = $this->timeToMinutes($window['to']);

            $fromMinutes = $fromMinutes === null ? $open : max($fromMinutes, $open);
            $toMinutes = $toMinutes === null ? $close : min($toMinutes, $close);
        }

        if ($fromMinutes === null || $toMinutes === null || $fromMinutes >= $toMinutes) {
            return null;
        }

        return [
            'from' => $this->minutesToTime($fromMinutes),
            'to' => $this->minutesToTime($toMinutes),
        ];
    }

    /**
     * @param  array{from: string, to: string}|null  $bounds
     */
    public function assertWindowWithinBounds(?string $from, ?string $to, ?array $bounds, string $label): void
    {
        if ($from === null && $to === null) {
            return;
        }

        if ($from === null || $to === null) {
            throw ValidationException::withMessages([
                $label => 'Both start and end times are required.',
            ]);
        }

        if ($bounds === null) {
            throw ValidationException::withMessages([
                $label => 'Selected locations do not have opening hours configured. Ask an admin to set opening times.',
            ]);
        }

        $fromNorm = $this->normalizeTime($from);
        $toNorm = $this->normalizeTime($to);

        if ($fromNorm === null || $toNorm === null) {
            throw ValidationException::withMessages([
                $label => 'Invalid time format.',
            ]);
        }

        $fromMin = $this->timeToMinutes($fromNorm);
        $toMin = $this->timeToMinutes($toNorm);
        $boundFrom = $this->timeToMinutes($bounds['from']);
        $boundTo = $this->timeToMinutes($bounds['to']);

        if ($fromMin >= $toMin) {
            throw ValidationException::withMessages([
                $label => 'Start time must be before end time.',
            ]);
        }

        if ($fromMin < $boundFrom || $toMin > $boundTo) {
            throw ValidationException::withMessages([
                $label => "Times must be within {$bounds['from']}–{$bounds['to']} for all selected locations.",
            ]);
        }
    }

    public function normalizeTime(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $str = (string) $value;
        if (preg_match('/^(\d{1,2}):(\d{2})/', $str, $m)) {
            return sprintf('%02d:%02d', (int) $m[1], (int) $m[2]);
        }

        return null;
    }

    public function timeToMinutes(string $time): int
    {
        [$h, $m] = array_map('intval', explode(':', $this->normalizeTime($time) ?? '00:00'));

        return $h * 60 + $m;
    }

    public function minutesToTime(int $minutes): string
    {
        $minutes = max(0, min(24 * 60 - 1, $minutes));

        return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    }

    /**
     * @return list<string> 30-minute step times between from and to inclusive
     */
    public function timeOptionsForWindow(?string $from, ?string $to): array
    {
        $fromNorm = $this->normalizeTime($from);
        $toNorm = $this->normalizeTime($to);

        if ($fromNorm === null || $toNorm === null) {
            return [];
        }

        $start = $this->timeToMinutes($fromNorm);
        $end = $this->timeToMinutes($toNorm);

        if ($start >= $end) {
            return [];
        }

        $options = [];
        for ($m = $start; $m <= $end; $m += 30) {
            $options[] = $this->minutesToTime($m);
        }

        return $options;
    }
}

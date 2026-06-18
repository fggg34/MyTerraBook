<?php

namespace App\Support;

use App\Models\Characteristic;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * Resolves listing spec icons (transmission, fuel, drive) from the
 * characteristics catalogue so admin icon changes apply on public pages.
 */
class VehicleSpecIconResolver
{
    /** @var array<string, list<string>> */
    private const TRANSMISSION_NAMES = [
        'manual' => ['Manual Transmission'],
        'automatic' => ['Automatic Transmission'],
    ];

    /** @var array<string, list<string>> */
    private const DRIVE_NAMES = [
        'fwd' => ['Front-wheel Drive'],
        'rwd' => ['Rear-wheel Drive'],
        'awd' => ['4WD / AWD'],
        '4wd' => ['4WD / AWD'],
    ];

    /** @var array<string, list<string>> */
    private const FUEL_NAMES = [
        'petrol' => ['Petrol'],
        'diesel' => ['Diesel'],
        'electric' => ['Electric'],
        'hybrid' => ['Hybrid'],
    ];

    /** @return array{icon: ?string, icon_url: ?string}|null */
    public static function forTransmission(?string $value): ?array
    {
        return self::resolve('transmission', $value, self::TRANSMISSION_NAMES);
    }

    /** @return array{icon: ?string, icon_url: ?string}|null */
    public static function forFuelType(?string $value): ?array
    {
        return self::resolve('fuel', $value, self::FUEL_NAMES);
    }

    /** @return array{icon: ?string, icon_url: ?string}|null */
    public static function forDriveType(?string $value): ?array
    {
        return self::resolve('drive', $value, self::DRIVE_NAMES);
    }

    /**
     * @param  array<string, list<string>>  $nameMap
     * @return array{icon: ?string, icon_url: ?string}|null
     */
    private static function resolve(string $kind, ?string $value, array $nameMap): ?array
    {
        if (! filled($value)) {
            return null;
        }

        $key = strtolower(trim($value));

        foreach ($nameMap[$key] ?? [] as $name) {
            $characteristic = self::catalog()->get($name);
            if ($characteristic) {
                return self::format($characteristic);
            }
        }

        $match = match ($kind) {
            'transmission' => self::matchTransmission($key),
            'fuel' => self::matchFuel($key),
            'drive' => self::matchDrive($key),
            default => null,
        };

        return $match ? self::format($match) : null;
    }

    private static function matchTransmission(string $key): ?Characteristic
    {
        $needle = str_contains($key, 'auto') ? 'automatic' : (str_contains($key, 'manual') ? 'manual' : $key);

        return self::catalog()->first(
            fn (Characteristic $characteristic) => str_contains(strtolower($characteristic->name), $needle)
                && str_contains(strtolower($characteristic->name), 'trans'),
        );
    }

    private static function matchFuel(string $key): ?Characteristic
    {
        return self::catalog()->first(
            fn (Characteristic $characteristic) => str_contains(strtolower($characteristic->name), $key),
        );
    }

    private static function matchDrive(string $key): ?Characteristic
    {
        return self::catalog()->first(function (Characteristic $characteristic) use ($key): bool {
            $name = strtolower($characteristic->name);

            return str_contains($name, $key)
                || ($key === '4wd' && str_contains($name, '4wd'))
                || ($key === 'awd' && str_contains($name, 'awd'));
        });
    }

    /** @return array{icon: ?string, icon_url: ?string} */
    private static function format(Characteristic $characteristic): array
    {
        return [
            'icon' => $characteristic->icon,
            'icon_url' => $characteristic->icon_path
                ? Storage::disk('public')->url($characteristic->icon_path)
                : null,
        ];
    }

    /** @return Collection<string, Characteristic> */
    private static function catalog(): Collection
    {
        return Characteristic::query()
            ->get(['id', 'name', 'icon', 'icon_path'])
            ->keyBy('name');
    }
}

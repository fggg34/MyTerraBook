<?php

namespace App\Console\Commands;

use App\Models\AvailabilityBlock;
use App\Models\Car;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Sabre\VObject\Reader;

class ImportExternalIcalendarCommand extends Command
{
    protected $signature = 'calendar:import-external {path? : Absolute path or URL to an .ics feed} {--car_id= : Optional car ID when importing one feed manually}';

    protected $description = 'Import external iCal feeds and sync them into availability blocks.';

    public function handle(): int
    {
        $path = $this->argument('path');
        $carId = $this->option('car_id');
        if ($path !== null && $path !== '') {
            if ($carId === null || $carId === '') {
                $this->warn('When providing a path or URL manually, pass --car_id=<id> to bind imported events to a specific car.');

                return self::SUCCESS;
            }

            $car = Car::query()->find((int) $carId);
            if ($car === null) {
                $this->error('Car not found for --car_id.');

                return self::FAILURE;
            }

            $imported = $this->importSourceForCar($car, (string) $path);
            $this->info("Imported {$imported} event".($imported === 1 ? '' : 's')." for car #{$car->id} from {$path}.");

            return self::SUCCESS;
        }

        $configuredPath = (string) data_get(Setting::getValue('ical.import', []), 'file_path', '');
        if ($configuredPath !== '') {
            if ($carId === null || $carId === '') {
                $this->warn('A default ical.import.file_path exists, but no --car_id was provided. Skipping default path import.');
            } else {
                $car = Car::query()->find((int) $carId);
                if ($car !== null) {
                    $imported = $this->importSourceForCar($car, $configuredPath);
                    $this->info("Imported {$imported} event".($imported === 1 ? '' : 's')." for car #{$car->id} from {$configuredPath}.");
                }
            }
        }

        $cars = Car::query()
            ->whereNotNull('ical_import_url')
            ->where('ical_import_url', '!=', '')
            ->get();

        if ($cars->isEmpty()) {
            $this->warn('No car iCal feeds configured (cars.ical_import_url).');
            return self::SUCCESS;
        }

        $totalImported = 0;
        foreach ($cars as $car) {
            $source = (string) $car->ical_import_url;
            $imported = $this->importSourceForCar($car, $source);
            $totalImported += $imported;
            $this->line("Car #{$car->id}: imported {$imported} event".($imported === 1 ? '' : 's').'.');
        }

        $this->info("Imported {$totalImported} VEVENT entr".($totalImported === 1 ? 'y' : 'ies').' in total.');

        return self::SUCCESS;
    }

    private function importSourceForCar(Car $car, string $source): int
    {
        $payload = $this->readIcsPayload($source);
        if ($payload === null || $payload === '') {
            $this->warn("Could not read iCal source for car #{$car->id}: {$source}");

            return 0;
        }

        $vcalendar = Reader::read($payload);
        $uids = [];
        $count = 0;

        foreach ($vcalendar->select('VEVENT') as $event) {
            $uid = trim((string) ($event->UID ?? ''));
            if ($uid === '') {
                continue;
            }

            $start = $this->parseEventDate((string) ($event->DTSTART ?? ''));
            $end = $this->parseEventDate((string) ($event->DTEND ?? ''));
            if ($start === null || $end === null || $start->greaterThanOrEqualTo($end)) {
                continue;
            }

            AvailabilityBlock::query()->updateOrCreate(
                [
                    'car_id' => $car->id,
                    'source' => 'ical_import',
                    'external_uid' => $uid,
                ],
                [
                    'starts_at' => $start,
                    'ends_at' => $end,
                    'units_blocked' => 1,
                    'external_calendar' => $source,
                    'notes' => (string) ($event->SUMMARY ?? 'Imported iCal event'),
                    'is_active' => true,
                ],
            );

            $uids[] = $uid;
            $count++;
        }

        AvailabilityBlock::query()
            ->where('car_id', $car->id)
            ->where('source', 'ical_import')
            ->where('external_calendar', $source)
            ->when($uids !== [], fn ($query) => $query->whereNotIn('external_uid', $uids))
            ->update(['is_active' => false]);

        return $count;
    }

    private function readIcsPayload(string $source): ?string
    {
        if (str_starts_with($source, 'http://') || str_starts_with($source, 'https://')) {
            $ctx = stream_context_create([
                'http' => [
                    'timeout' => 20,
                    'user_agent' => 'ImpactRent iCal Sync',
                ],
            ]);
            $data = @file_get_contents($source, false, $ctx);

            return $data === false ? null : $data;
        }

        if (! is_readable($source)) {
            return null;
        }

        $data = @file_get_contents($source);

        return $data === false ? null : $data;
    }

    private function parseEventDate(string $raw): ?Carbon
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        try {
            if (preg_match('/^\d{8}$/', $raw) === 1) {
                return Carbon::createFromFormat('Ymd', $raw, 'UTC')->startOfDay();
            }

            if (str_ends_with($raw, 'Z')) {
                return Carbon::createFromFormat('Ymd\THis\Z', $raw, 'UTC');
            }

            return Carbon::createFromFormat('Ymd\THis', $raw, 'UTC');
        } catch (\Throwable) {
            return null;
        }

    }
}

<?php

namespace App\Console\Commands;

use App\Models\Car;
use App\Models\Location;
use Illuminate\Console\Command;

class LinkLocationsToCarsCommand extends Command
{
    protected $signature = 'locations:link-to-cars
                            {--car= : Link all active locations to a specific car ID}
                            {--all : Link all active locations to every active car}';

    protected $description = 'Assign pickup/dropoff locations to vehicles so they appear in homepage search';

    public function handle(): int
    {
        $carId = $this->option('car');
        $all = (bool) $this->option('all');

        if (! $carId && ! $all) {
            $this->error('Specify --car=ID or --all');

            return self::FAILURE;
        }

        $locations = Location::query()->where('is_active', true)->pluck('id');

        if ($locations->isEmpty()) {
            $this->warn('No active locations found.');

            return self::SUCCESS;
        }

        $cars = $all
            ? Car::query()->where('is_active', true)->get()
            : Car::query()->whereKey($carId)->where('is_active', true)->get();

        if ($cars->isEmpty()) {
            $this->error('No matching active vehicles found.');

            return self::FAILURE;
        }

        foreach ($cars as $car) {
            foreach ($locations as $locationId) {
                if ($car->locations()->whereKey($locationId)->exists()) {
                    $car->locations()->updateExistingPivot($locationId, [
                        'allows_pickup' => true,
                        'allows_dropoff' => true,
                    ]);
                } else {
                    $car->locations()->attach($locationId, [
                        'allows_pickup' => true,
                        'allows_dropoff' => true,
                    ]);
                }
            }
            $this->line("Linked {$locations->count()} location(s) to car #{$car->id} ({$car->name})");
        }

        $this->info('Done. Verify: GET /api/search/suggestions?scope=location&role=pickup');

        return self::SUCCESS;
    }
}

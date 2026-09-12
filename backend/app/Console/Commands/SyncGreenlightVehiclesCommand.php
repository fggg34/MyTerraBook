<?php

namespace App\Console\Commands;

use App\Services\Partners\GreenlightSettings;
use App\Services\Partners\GreenlightVehicleSyncService;
use Illuminate\Console\Command;

class SyncGreenlightVehiclesCommand extends Command
{
    protected $signature = 'greenlight:sync-vehicles';

    protected $description = 'Pull Greenlight fleet and locations into MyTerra listings.';

    public function handle(GreenlightSettings $settings, GreenlightVehicleSyncService $sync): int
    {
        if (! $settings->enabled()) {
            $this->warn('Greenlight is disabled or missing an API key.');

            return self::SUCCESS;
        }

        $result = $sync->sync();
        $this->info("Synced {$result['locations']} locations and {$result['vehicles']} vehicles ({$result['deactivated']} deactivated).");

        return self::SUCCESS;
    }
}

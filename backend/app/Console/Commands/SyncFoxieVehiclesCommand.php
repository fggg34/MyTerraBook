<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\Partners\FoxieVehicleSyncService;
use Illuminate\Console\Command;

class SyncFoxieVehiclesCommand extends Command
{
    protected $signature = 'foxie:sync {host : Registered host id}';

    protected $description = 'Import Foxie Camper locations and vehicles for an existing host. Does not create a reservation.';

    public function handle(FoxieVehicleSyncService $sync): int
    {
        $host = User::query()->where('role', UserRole::Host)->where('partner_provider', 'foxie')->findOrFail($this->argument('host'));
        try {
            $result = $sync->sync($host);
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
        $this->info("Imported {$result['vehicles']} vehicles, {$result['locations']} locations; skipped {$result['skipped']} test entries.");

        return self::SUCCESS;
    }
}

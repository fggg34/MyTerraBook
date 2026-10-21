<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\Partners\CarenVehicleSyncService;
use Illuminate\Console\Command;

class SyncCarenVehiclesCommand extends Command
{
    protected $signature = 'caren:sync-vehicles {host : Registered host ID}';

    protected $description = 'Import a registered Caren host catalogue; new vehicles are inactive drafts.';

    public function handle(CarenVehicleSyncService $sync): int
    {
        $host = User::query()->where('role', UserRole::Host)->where('partner_provider', 'caren')->findOrFail($this->argument('host'));
        try {
            $result = $sync->sync($host);
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
        $this->info("Imported {$result['vehicles']} vehicles and {$result['locations']} locations; {$result['skipped']} test entries skipped.");

        return self::SUCCESS;
    }
}

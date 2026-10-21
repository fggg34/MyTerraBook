<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\Partners\CarenPartnerClient;
use App\Services\Partners\CarenVehicleSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use RuntimeException;

class ConnectCarenHostCommand extends Command
{
    protected $signature = 'caren:connect {email : Host contact email} {--name= : Partner display name} {--rental= : Caren rental ID}';

    protected $description = 'Register a Caren partner using hidden credential prompts and import its catalogue.';

    public function handle(CarenPartnerClient $client, CarenVehicleSyncService $sync): int
    {
        $host = User::query()->firstOrNew(['email' => $this->argument('email')]);
        if ($host->exists && ($host->role !== UserRole::Host || ! in_array($host->partner_provider, [null, 'caren'], true))) {
            throw new RuntimeException('This email belongs to another account or partner provider. Use a separate host email.');
        }
        if (! $host->exists) {
            $host->fill(['role' => UserRole::Host, 'password' => Str::random(64), 'country_code' => 'IS', 'locale' => 'en']);
        }
        $host->fill([
            'partner_provider' => 'caren', 'partner_api_base_url' => CarenPartnerClient::BASE_URL,
            'partner_api_enabled' => true,
            'partner_rental_id' => $this->option('rental') ?: $host->partner_rental_id,
            'partner_api_username' => $this->secret('Caren API username'),
            'partner_api_key' => $this->secret('Caren Vehicle API key'),
            'partner_api_password' => $this->secret('Caren API password'),
        ]);
        try {
            $catalogue = $client->catalogue($host);
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
        $host->name = $this->option('name') ?: $catalogue['rental']['Name'];
        $host->partner_rental_id = $catalogue['rental']['Id'];
        $host->save();
        try {
            // The catalogue was already fetched and validated above. Import it without
            // logging in again immediately after closing the verification session.
            $result = $sync->importCatalogue($host, $catalogue);
        } catch (\Throwable $exception) {
            $this->error('Host saved, but import failed: '.$exception->getMessage());

            return self::FAILURE;
        }
        $this->info("Host #{$host->id}: {$host->name}. Imported {$result['vehicles']} draft vehicles, {$result['locations']} locations; skipped {$result['skipped']} test entries.");

        return self::SUCCESS;
    }
}

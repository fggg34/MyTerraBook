<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\Partners\FoxiePartnerClient;
use App\Services\Partners\FoxieVehicleSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use RuntimeException;

class ConnectFoxieHostCommand extends Command
{
    protected $signature = 'foxie:connect {email : Host contact email} {--name=Foxie Camper : Partner display name}';

    protected $description = 'Register Foxie Camper and import its locations and vehicles. Reads FOXIE_API_KEY, or prompts for the key. Does not create a reservation.';

    public function handle(FoxiePartnerClient $client, FoxieVehicleSyncService $sync): int
    {
        $key = getenv('FOXIE_API_KEY');
        if (! is_string($key) || trim($key) === '') {
            $key = (string) $this->secret('Foxie API key');
        }
        $key = trim($key);
        if ($key === '') {
            $this->error('Foxie API key is required.');

            return self::FAILURE;
        }

        $host = User::query()->firstOrNew(['email' => $this->argument('email')]);
        if ($host->exists && ($host->role !== UserRole::Host || ! in_array($host->partner_provider, [null, 'foxie'], true))) {
            throw new RuntimeException('This email belongs to another account or partner provider. Use a separate host email.');
        }
        if (! $host->exists) {
            $host->fill([
                'role' => UserRole::Host,
                'password' => Str::random(64),
                'country_code' => 'IS',
                'locale' => 'en',
                'phone' => '+3548504441',
            ]);
        }
        $host->fill([
            'partner_provider' => 'foxie',
            'partner_api_base_url' => FoxiePartnerClient::BASE_URL,
            'partner_api_enabled' => true,
            'partner_api_key' => $key,
        ]);
        try {
            $catalogue = $client->catalogue($host);
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
        $host->name = $this->option('name') ?: 'Foxie Camper';
        $host->save();
        try {
            $result = $sync->importCatalogue($host, $catalogue);
        } catch (\Throwable $exception) {
            $this->error('Host saved, but import failed: '.$exception->getMessage());

            return self::FAILURE;
        }
        $this->info("Host #{$host->id}: {$host->name}. Imported {$result['vehicles']} vehicles, {$result['locations']} locations; skipped {$result['skipped']} test entries.");

        return self::SUCCESS;
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        $settings = DB::table('settings')->where('key', 'partners.greenlight')->value('value');
        $connection = is_string($settings) ? json_decode($settings, true) : [];
        $connection = is_array($connection) ? $connection : [];

        $host = DB::table('users')->where('partner_provider', 'greenlight')->first();
        if ($host === null) {
            $hostId = DB::table('users')->insertGetId([
                'name' => (string) ($connection['display_name'] ?? 'Greenlight car rental'),
                'email' => 'greenlight@partners.myterrabook.local',
                'password' => Hash::make(str()->random(64)),
                'role' => 'host',
                'locale' => 'en',
                'partner_provider' => 'greenlight',
                'partner_api_base_url' => (string) ($connection['base_url'] ?? 'https://greenlightcarrental.is/api/partner/v1'),
                'partner_api_key' => filled($connection['api_key'] ?? null)
                    ? Crypt::encryptString((string) $connection['api_key'])
                    : null,
                'partner_api_enabled' => (bool) ($connection['enabled'] ?? false),
                'company_logo_path' => $connection['logo_path'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $hostId = $host->id;
        }

        DB::table('cars')
            ->where('external_provider', 'greenlight')
            ->update(['user_id' => $hostId]);

        DB::table('locations')
            ->where('external_provider', 'greenlight')
            ->update(['host_user_id' => $hostId]);
    }

    public function down(): void
    {
        // The Greenlight partner and its listings remain intact if this migration is rolled back.
    }
};

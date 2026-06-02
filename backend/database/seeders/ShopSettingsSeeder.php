<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class ShopSettingsSeeder extends Seeder
{
    public function run(): void
    {
        Setting::query()->firstOrCreate(
            ['key' => 'shop.currency'],
            ['value' => ['code' => 'EUR', 'symbol' => '€']]
        );
        Setting::query()->firstOrCreate(
            ['key' => 'shop.default_tax'],
            ['value' => ['basis_points' => 1000]]
        );
        Setting::query()->firstOrCreate(
            ['key' => 'shop.extended_gratuity_period'],
            ['value' => ['hours' => 2]]
        );
        Setting::query()->firstOrCreate(
            ['key' => 'shop.payment_lock_minutes'],
            ['value' => ['minutes' => 20]]
        );
    }
}

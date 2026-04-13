<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'admin@terrabook.test'],
            [
                'name' => 'MyTerraBook Admin',
                'phone' => '+355000000001',
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
                'locale' => 'en',
            ]
        );

        User::query()->firstOrCreate(
            ['email' => 'customer@terrabook.test'],
            [
                'name' => 'MyTerraBook Customer',
                'phone' => '+355000000002',
                'password' => Hash::make('password'),
                'role' => UserRole::Customer,
                'locale' => 'en',
            ]
        );

        Setting::query()->firstOrCreate(
            ['key' => 'shop.currency'],
            ['value' => ['code' => 'EUR', 'symbol' => '€']]
        );
        Setting::query()->firstOrCreate(
            ['key' => 'shop.default_tax'],
            ['value' => ['basis_points' => 0]]
        );
    }
}

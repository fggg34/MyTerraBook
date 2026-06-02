<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
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

        User::query()->firstOrCreate(
            ['email' => 'ops@terrabook.test'],
            [
                'name' => 'Operations Manager',
                'phone' => '+355000000003',
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
                'locale' => 'en',
            ]
        );

        $existingCustomers = User::query()->where('role', UserRole::Customer)->count();
        $targetCustomers = 7;
        if ($existingCustomers < $targetCustomers) {
            User::factory()->customer()->count($targetCustomers - $existingCustomers)->create();
        }
    }
}

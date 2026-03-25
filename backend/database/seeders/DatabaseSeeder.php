<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Car;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Extra;
use App\Models\Location;
use App\Models\PricingRule;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::query()->create([
            'name' => 'MyTerraBook Admin',
            'email' => 'admin@terrabook.test',
            'phone' => '+355000000001',
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
        ]);

        $customer = User::query()->create([
            'name' => 'MyTerraBook Customer',
            'email' => 'customer@terrabook.test',
            'phone' => '+355000000002',
            'password' => Hash::make('password'),
            'role' => UserRole::Customer,
        ]);

        $economy = Category::query()->create([
            'name' => 'Economy',
            'slug' => 'economy',
            'description' => 'Budget friendly compact vehicles.',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $suv = Category::query()->create([
            'name' => 'SUV',
            'slug' => 'suv',
            'description' => 'Spacious SUVs for families.',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $airport = Location::query()->create([
            'name' => 'Airport',
            'slug' => 'airport',
            'city' => 'Tirana',
            'country' => 'AL',
            'allows_pickup' => true,
            'allows_dropoff' => true,
            'opening_hours' => ['mon' => ['open' => '08:00', 'close' => '22:00']],
            'closed_days' => [],
            'is_active' => true,
        ]);
        $downtown = Location::query()->create([
            'name' => 'Downtown',
            'slug' => 'downtown',
            'city' => 'Tirana',
            'country' => 'AL',
            'allows_pickup' => true,
            'allows_dropoff' => true,
            'opening_hours' => ['mon' => ['open' => '09:00', 'close' => '20:00']],
            'closed_days' => [],
            'is_active' => true,
        ]);

        $carOne = Car::query()->create([
            'category_id' => $economy->id,
            'name' => 'Toyota Yaris',
            'slug' => 'toyota-yaris',
            'transmission' => 'automatic',
            'fuel_type' => 'petrol',
            'seats' => 5,
            'bags' => 2,
            'features' => ['ac', 'bluetooth'],
            'availability_status' => 'available',
            'base_daily_price' => 45,
            'base_hourly_price' => 8,
            'min_rental_days' => 1,
            'is_active' => true,
        ]);
        Car::query()->create([
            'category_id' => $suv->id,
            'name' => 'Nissan X-Trail',
            'slug' => 'nissan-xtrail',
            'transmission' => 'automatic',
            'fuel_type' => 'diesel',
            'seats' => 7,
            'bags' => 4,
            'features' => ['ac', 'automatic', 'camera'],
            'availability_status' => 'available',
            'base_daily_price' => 95,
            'base_hourly_price' => 14,
            'min_rental_days' => 1,
            'is_active' => true,
        ]);

        Extra::query()->create([
            'name' => 'GPS',
            'slug' => 'gps',
            'price_type' => 'per_day',
            'unit_price' => 5,
            'is_mandatory' => false,
            'max_quantity' => 1,
            'is_active' => true,
        ]);
        Extra::query()->create([
            'name' => 'Child Seat',
            'slug' => 'child-seat',
            'price_type' => 'fixed',
            'unit_price' => 12,
            'is_mandatory' => false,
            'max_quantity' => 2,
            'is_active' => true,
        ]);

        PricingRule::query()->create([
            'name' => 'Summer Season',
            'rule_kind' => 'seasonal',
            'car_id' => $carOne->id,
            'location_id' => $airport->id,
            'date_from' => now()->startOfMonth(),
            'date_to' => now()->addMonths(2)->endOfMonth(),
            'time_unit' => 'day',
            'amount' => 1.15,
            'adjustment' => 'multiply',
            'priority' => 10,
            'is_active' => true,
        ]);

        Coupon::query()->create([
            'code' => 'WELCOME10',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'expires_at' => now()->addMonths(3),
            'usage_limit' => 100,
            'times_used' => 0,
            'min_order_amount' => 50,
            'is_active' => true,
        ]);
    }
}

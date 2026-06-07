<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Car;
use App\Models\MainCategory;
use App\Models\SubCategory;
use App\Models\Location;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStatsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_stats_requires_admin_token(): void
    {
        $this->seed();
        $customer = User::query()->where('email', 'customer@terrabook.test')->firstOrFail();

        $this->actingAs($customer, 'sanctum')
            ->getJson('/api/admin/stats')
            ->assertForbidden();
    }

    public function test_admin_stats_returns_counts(): void
    {
        $this->seed();
        $admin = User::query()->where('email', 'admin@terrabook.test')->firstOrFail();

        $main = MainCategory::query()->firstOrCreate(['slug' => 'car'], ['name' => 'Car', 'is_active' => true]);
        $category = SubCategory::query()->create(['main_category_id' => $main->id, 'name' => 'X', 'is_active' => true, 'is_search_filter' => true]);
        $car = Car::query()->create([
            'sub_category_id' => $category->id,
            'name' => 'Car',
            'units_available' => 2,
            'is_active' => true,
        ]);
        $loc = Location::query()->create(['name' => 'L', 'is_active' => true]);

        Order::query()->create([
            'reference' => 'ORD-A',
            'car_id' => $car->id,
            'pickup_location_id' => $loc->id,
            'dropoff_location_id' => $loc->id,
            'pickup_at' => now()->subDay(),
            'dropoff_at' => now()->addDay(),
            'order_status' => OrderStatus::Confirmed,
            'customer_name' => 'A',
            'customer_email' => 'a@example.com',
            'base_rental_cents' => 10000,
            'extras_cents' => 0,
            'fees_cents' => 0,
            'discount_cents' => 0,
            'tax_cents' => 0,
            'total_cents' => 10000,
            'currency' => 'EUR',
        ]);

        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/admin/stats');

        $response->assertOk()
            ->assertJsonPath('total_orders', 1)
            ->assertJsonPath('revenue', '100.00')
            ->assertJsonPath('active_rentals', 1);
    }
}

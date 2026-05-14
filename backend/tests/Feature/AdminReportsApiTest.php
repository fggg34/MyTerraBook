<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Car;
use App\Models\Category;
use App\Models\Location;
use App\Models\Order;
use App\Models\PriceType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReportsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_reports_requires_admin_token(): void
    {
        $this->seed();
        $customer = User::query()->where('email', 'customer@terrabook.test')->firstOrFail();

        $this->actingAs($customer, 'sanctum')
            ->getJson('/api/admin/reports')
            ->assertForbidden();
    }

    public function test_admin_reports_return_revenue_occupancy_and_country_breakdowns(): void
    {
        $this->seed();
        $admin = User::query()->where('email', 'admin@terrabook.test')->firstOrFail();

        $category = Category::query()->create(['name' => 'Reports', 'is_active' => true]);
        $carA = Car::query()->create([
            'category_id' => $category->id,
            'name' => 'SUV Alpha',
            'units_available' => 2,
            'is_active' => true,
        ]);
        $carB = Car::query()->create([
            'category_id' => $category->id,
            'name' => 'City Beta',
            'units_available' => 2,
            'is_active' => true,
        ]);
        $loc = Location::query()->create(['name' => 'HQ', 'is_active' => true]);
        $plan = PriceType::query()->create(['name' => 'Flexible', 'is_active' => true]);

        Order::query()->create([
            'reference' => 'ORD-REP-1',
            'car_id' => $carA->id,
            'price_type_id' => $plan->id,
            'pickup_location_id' => $loc->id,
            'dropoff_location_id' => $loc->id,
            'pickup_at' => now()->subDays(3),
            'dropoff_at' => now()->subDays(1),
            'order_status' => OrderStatus::Confirmed,
            'customer_name' => 'A',
            'customer_email' => 'a@example.test',
            'customer_country' => 'AL',
            'base_rental_cents' => 20000,
            'extras_cents' => 0,
            'fees_cents' => 0,
            'discount_cents' => 0,
            'tax_cents' => 0,
            'total_cents' => 20000,
            'currency' => 'EUR',
        ]);

        Order::query()->create([
            'reference' => 'ORD-REP-2',
            'car_id' => $carB->id,
            'price_type_id' => $plan->id,
            'pickup_location_id' => $loc->id,
            'dropoff_location_id' => $loc->id,
            'pickup_at' => now()->subDays(2),
            'dropoff_at' => now()->subDay(),
            'order_status' => OrderStatus::Confirmed,
            'customer_name' => 'B',
            'customer_email' => 'b@example.test',
            'customer_country' => 'IT',
            'base_rental_cents' => 12000,
            'extras_cents' => 0,
            'fees_cents' => 0,
            'discount_cents' => 0,
            'tax_cents' => 0,
            'total_cents' => 12000,
            'currency' => 'EUR',
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/admin/reports');

        $response->assertOk()
            ->assertJsonPath('revenue_summary.confirmed_orders', 2)
            ->assertJsonPath('revenue_summary.revenue_cents', 32000)
            ->assertJsonPath('occupancy_ranking.0.car_name', 'SUV Alpha')
            ->assertJsonPath('top_countries.0.country', 'AL')
            ->assertJsonPath('rate_plan_revenue.0.rate_plan_name', 'Flexible');
    }
}

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

class MeOrderIcalAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_download_ics_for_someone_elses_order(): void
    {
        $this->seed();
        $alice = User::query()->where('email', 'customer@terrabook.test')->firstOrFail();
        $bob = User::factory()->create(['email' => 'bob@example.test']);

        $main = MainCategory::query()->firstOrCreate(['slug' => 'car'], ['name' => 'Car', 'is_active' => true]);
        $category = SubCategory::query()->create(['main_category_id' => $main->id, 'name' => 'X', 'is_active' => true, 'is_search_filter' => true]);
        $car = Car::query()->create([
            'sub_category_id' => $category->id,
            'name' => 'Car',
            'units_available' => 1,
            'is_active' => true,
        ]);
        $loc = Location::query()->create(['name' => 'L', 'is_active' => true]);

        $order = Order::query()->create([
            'reference' => 'ORD-BOB',
            'user_id' => $bob->id,
            'car_id' => $car->id,
            'pickup_location_id' => $loc->id,
            'dropoff_location_id' => $loc->id,
            'pickup_at' => now()->addDay(),
            'dropoff_at' => now()->addDays(2),
            'order_status' => OrderStatus::Pending,
            'customer_name' => 'Bob',
            'customer_email' => 'bob@example.test',
            'base_rental_cents' => 5000,
            'extras_cents' => 0,
            'fees_cents' => 0,
            'discount_cents' => 0,
            'tax_cents' => 0,
            'total_cents' => 5000,
            'currency' => 'EUR',
        ]);

        $this->actingAs($alice, 'sanctum')
            ->get('/api/me/orders/'.$order->id.'/calendar.ics')
            ->assertForbidden();
    }
}

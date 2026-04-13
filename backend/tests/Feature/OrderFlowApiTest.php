<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Car;
use App\Models\Category;
use App\Models\DailyFare;
use App\Models\Location;
use App\Models\Order;
use App\Models\PriceType;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderFlowApiTest extends TestCase
{
    use RefreshDatabase;

    protected function seedCatalogForOrder(): array
    {
        Setting::putValue('shop.currency', ['code' => 'EUR']);
        Setting::putValue('shop.default_tax', ['basis_points' => 0]);

        $category = Category::query()->create(['name' => 'Eco', 'is_active' => true]);
        $car = Car::query()->create([
            'category_id' => $category->id,
            'name' => 'Test Vehicle',
            'units_available' => 1,
            'is_active' => true,
        ]);
        $pickup = Location::query()->create(['name' => 'P1', 'is_active' => true]);
        $dropoff = Location::query()->create(['name' => 'D1', 'is_active' => true]);
        $car->locations()->attach([
            $pickup->id => ['allows_pickup' => true, 'allows_dropoff' => true],
            $dropoff->id => ['allows_pickup' => true, 'allows_dropoff' => true],
        ]);
        $priceType = PriceType::query()->create(['name' => 'Basic', 'is_active' => true]);
        DailyFare::query()->create([
            'car_id' => $car->id,
            'price_type_id' => $priceType->id,
            'from_days' => 1,
            'to_days' => 30,
            'price_per_day_cents' => 5000,
        ]);

        return [$car, $pickup, $dropoff, $priceType];
    }

    public function test_quote_and_create_order(): void
    {
        [$car, $pickup, $dropoff, $priceType] = $this->seedCatalogForOrder();

        $quote = $this->postJson('/api/orders/quote', [
            'car_id' => $car->id,
            'price_type_id' => $priceType->id,
            'pickup_location_id' => $pickup->id,
            'dropoff_location_id' => $dropoff->id,
            'pickup_at' => now()->addDay()->toDateTimeString(),
            'dropoff_at' => now()->addDays(4)->toDateTimeString(),
        ]);

        $quote->assertOk()
            ->assertJsonPath('total_cents', 15000);

        $create = $this->postJson('/api/orders', [
            'car_id' => $car->id,
            'price_type_id' => $priceType->id,
            'pickup_location_id' => $pickup->id,
            'dropoff_location_id' => $dropoff->id,
            'pickup_at' => now()->addDay()->toDateTimeString(),
            'dropoff_at' => now()->addDays(4)->toDateTimeString(),
            'customer_name' => 'Alex',
            'customer_email' => 'alex@example.com',
        ]);

        $create->assertCreated()
            ->assertJsonPath('data.order_status', OrderStatus::Pending->value);

        $this->assertDatabaseCount('orders', 1);
    }

    public function test_capacity_blocks_overbooked_car(): void
    {
        [$car, $pickup, $dropoff, $priceType] = $this->seedCatalogForOrder();

        Order::query()->create([
            'reference' => 'ORD-TESTEXIST',
            'car_id' => $car->id,
            'pickup_location_id' => $pickup->id,
            'dropoff_location_id' => $dropoff->id,
            'pickup_at' => now()->addDay(),
            'dropoff_at' => now()->addDays(3),
            'order_status' => OrderStatus::Confirmed,
            'customer_name' => 'Prior',
            'customer_email' => 'prior@example.com',
            'base_rental_cents' => 15000,
            'extras_cents' => 0,
            'fees_cents' => 0,
            'discount_cents' => 0,
            'tax_cents' => 0,
            'total_cents' => 15000,
            'currency' => 'EUR',
        ]);

        $create = $this->postJson('/api/orders', [
            'car_id' => $car->id,
            'price_type_id' => $priceType->id,
            'pickup_location_id' => $pickup->id,
            'dropoff_location_id' => $dropoff->id,
            'pickup_at' => now()->addDay()->toDateTimeString(),
            'dropoff_at' => now()->addDays(2)->toDateTimeString(),
            'customer_name' => 'Alex',
            'customer_email' => 'alex@example.com',
        ]);

        $create->assertStatus(422);
    }

    public function test_admin_categories_api_requires_admin(): void
    {
        $this->seed();
        $user = User::factory()->create(['role' => UserRole::Customer]);
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/admin/categories');
        $response->assertForbidden();
    }
}

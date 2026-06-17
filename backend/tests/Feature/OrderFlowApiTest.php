<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Car;
use App\Models\MainCategory;
use App\Models\SubCategory;
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

        $main = MainCategory::query()->firstOrCreate(['slug' => 'car'], ['name' => 'Car', 'is_active' => true]);
        $category = SubCategory::query()->create(['main_category_id' => $main->id, 'name' => 'Eco', 'is_active' => true, 'is_search_filter' => true]);
        $car = Car::query()->create([
            'sub_category_id' => $category->id,
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
            ->assertJsonPath('data.order_status', OrderStatus::Confirmed->value);

        $this->assertDatabaseCount('orders', 1);
    }

    public function test_quote_with_rental_options_list_format(): void
    {
        [$car, $pickup, $dropoff, $priceType] = $this->seedCatalogForOrder();

        $option = \App\Models\RentalOption::query()->create([
            'name' => 'Wi-Fi hotspot',
            'cost_cents' => 2000,
            'is_daily_cost' => true,
            'has_quantity' => false,
            'is_mandatory' => false,
            'is_active' => true,
        ]);
        $car->rentalOptions()->attach($option->id);

        $pickupAt = now()->addDay()->toDateTimeString();
        $dropoffAt = now()->addDays(4)->toDateTimeString();

        $this->postJson('/api/orders/quote', [
            'car_id' => $car->id,
            'price_type_id' => $priceType->id,
            'pickup_location_id' => $pickup->id,
            'dropoff_location_id' => $dropoff->id,
            'pickup_at' => $pickupAt,
            'dropoff_at' => $dropoffAt,
            'rental_options' => [$option->id],
        ])
            ->assertOk()
            ->assertJsonPath('extras_cents', 6000)
            ->assertJsonPath('total_cents', 21000)
            ->assertJsonPath('extras_lines.0.name', 'Wi-Fi hotspot')
            ->assertJsonPath('extras_lines.0.amount', '60.00');
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

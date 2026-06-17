<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\AvailabilityBlock;
use App\Models\Car;
use App\Models\DailyFare;
use App\Models\MainCategory;
use App\Models\SubCategory;
use App\Models\Location;
use App\Models\Order;
use App\Models\PriceType;
use App\Models\Setting;
use App\Models\User;
use App\Services\OrderAvailabilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AvailabilityBlockingTest extends TestCase
{
    use RefreshDatabase;

    protected function makeCarWithLocations(int $units = 3): array
    {
        $main = MainCategory::query()->firstOrCreate(['slug' => 'car'], ['name' => 'Car', 'is_active' => true]);
        $category = SubCategory::query()->create(['main_category_id' => $main->id, 'name' => 'Fleet', 'is_active' => true, 'is_search_filter' => true]);
        $car = Car::query()->create([
            'sub_category_id' => $category->id,
            'name' => 'Capacity Test Car',
            'units_available' => $units,
            'is_active' => true,
        ]);
        $pickup = Location::query()->create(['name' => 'P1', 'is_active' => true]);
        $dropoff = Location::query()->create(['name' => 'D1', 'is_active' => true]);
        $car->locations()->attach([
            $pickup->id => ['allows_pickup' => true, 'allows_dropoff' => true],
            $dropoff->id => ['allows_pickup' => true, 'allows_dropoff' => true],
        ]);

        return [$car, $pickup, $dropoff];
    }

    public function test_has_capacity_accounts_for_confirmed_locks_and_blocks(): void
    {
        [$car, $pickup, $dropoff] = $this->makeCarWithLocations(3);
        $start = Carbon::parse('2026-05-10 10:00:00');
        $end = Carbon::parse('2026-05-12 10:00:00');

        Order::query()->create([
            'reference' => 'ORD-CONFIRM1',
            'car_id' => $car->id,
            'pickup_location_id' => $pickup->id,
            'dropoff_location_id' => $dropoff->id,
            'pickup_at' => $start,
            'dropoff_at' => $end,
            'order_status' => OrderStatus::Confirmed,
            'customer_name' => 'Confirmed',
            'customer_email' => 'confirmed@example.com',
            'base_rental_cents' => 10000,
            'extras_cents' => 0,
            'fees_cents' => 0,
            'discount_cents' => 0,
            'tax_cents' => 0,
            'total_cents' => 10000,
            'currency' => 'EUR',
        ]);

        Order::query()->create([
            'reference' => 'ORD-STANDBY1',
            'car_id' => $car->id,
            'pickup_location_id' => $pickup->id,
            'dropoff_location_id' => $dropoff->id,
            'pickup_at' => $start,
            'dropoff_at' => $end,
            'order_status' => OrderStatus::StandBy,
            'payment_lock_expires_at' => now()->addMinutes(30),
            'customer_name' => 'Lock',
            'customer_email' => 'lock@example.com',
            'base_rental_cents' => 10000,
            'extras_cents' => 0,
            'fees_cents' => 0,
            'discount_cents' => 0,
            'tax_cents' => 0,
            'total_cents' => 10000,
            'currency' => 'EUR',
        ]);

        AvailabilityBlock::query()->create([
            'car_id' => $car->id,
            'source' => 'manual_close',
            'starts_at' => $start,
            'ends_at' => $end,
            'units_blocked' => 1,
            'is_active' => true,
        ]);

        $svc = app(OrderAvailabilityService::class);
        $this->assertFalse($svc->hasCapacity($car->id, 3, $start, $end));
        $this->assertTrue($svc->hasCapacity($car->id, 4, $start, $end));
    }

    public function test_availability_calendar_returns_blocked_and_standby_locks(): void
    {
        [$car, $pickup, $dropoff] = $this->makeCarWithLocations(2);
        $start = Carbon::parse('2026-06-01 10:00:00');
        $end = Carbon::parse('2026-06-02 10:00:00');

        Order::query()->create([
            'reference' => 'ORD-CALENDAR1',
            'car_id' => $car->id,
            'pickup_location_id' => $pickup->id,
            'dropoff_location_id' => $dropoff->id,
            'pickup_at' => $start,
            'dropoff_at' => $end,
            'order_status' => OrderStatus::StandBy,
            'payment_lock_expires_at' => now()->addMinutes(30),
            'customer_name' => 'Calendar Lock',
            'customer_email' => 'calendar-lock@example.com',
            'base_rental_cents' => 10000,
            'extras_cents' => 0,
            'fees_cents' => 0,
            'discount_cents' => 0,
            'tax_cents' => 0,
            'total_cents' => 10000,
            'currency' => 'EUR',
        ]);

        AvailabilityBlock::query()->create([
            'car_id' => $car->id,
            'source' => 'ical_import',
            'starts_at' => $start->copy()->addDay(),
            'ends_at' => $end->copy()->addDay(),
            'units_blocked' => 1,
            'is_active' => true,
        ]);

        $response = $this->getJson("/api/cars/{$car->id}/availability-calendar");

        $response->assertOk()
            ->assertJsonPath('blocked.0.source', 'ical_import')
            ->assertJsonPath('blocked.1.source', 'standby_lock');
    }

    public function test_one_unit_month_block_rejects_mid_month_booking(): void
    {
        Setting::putValue('shop.currency', ['code' => 'EUR']);
        Setting::putValue('shop.default_tax', ['basis_points' => 0]);

        [$car, $pickup, $dropoff] = $this->makeCarWithLocations(1);
        $priceType = PriceType::query()->create(['name' => 'Basic', 'is_active' => true]);
        DailyFare::query()->create([
            'car_id' => $car->id,
            'price_type_id' => $priceType->id,
            'from_days' => 1,
            'to_days' => 30,
            'price_per_day_cents' => 5000,
        ]);

        AvailabilityBlock::query()->create([
            'car_id' => $car->id,
            'source' => 'manual',
            'starts_at' => Carbon::parse('2026-06-17 00:00:00'),
            'ends_at' => Carbon::parse('2026-07-17 00:00:00'),
            'units_blocked' => 1,
            'is_active' => true,
        ]);

        $payload = [
            'car_id' => $car->id,
            'price_type_id' => $priceType->id,
            'pickup_location_id' => $pickup->id,
            'dropoff_location_id' => $dropoff->id,
            'pickup_at' => '2026-06-17 09:00:00',
            'dropoff_at' => '2026-06-26 10:00:00',
        ];

        $this->postJson('/api/orders/quote', $payload)
            ->assertStatus(422)
            ->assertJsonPath('message', 'No availability for these dates.');

        $this->postJson('/api/orders', [
            ...$payload,
            'customer_name' => 'Guest',
            'customer_email' => 'guest@example.com',
        ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'No availability for these dates.');
    }

    public function test_host_cannot_block_more_units_than_fleet_size(): void
    {
        $main = MainCategory::query()->firstOrCreate(['slug' => 'car'], ['name' => 'Car', 'is_active' => true]);
        $category = SubCategory::query()->create(['main_category_id' => $main->id, 'name' => 'Camper', 'is_active' => true, 'is_search_filter' => true]);
        $host = User::factory()->host()->create();
        Sanctum::actingAs($host);

        $car = Car::query()->create([
            'user_id' => $host->id,
            'sub_category_id' => $category->id,
            'name' => 'Single camper',
            'units_available' => 1,
            'is_active' => true,
        ]);

        $this->postJson("/api/host/cars/{$car->id}/availability-blocks", [
            'starts_at' => '2026-06-17 00:00:00',
            'ends_at' => '2026-07-17 00:00:00',
            'units_blocked' => 2,
        ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Cannot block more than 1 unit(s) for this vehicle.');
    }
}

<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\AvailabilityBlock;
use App\Models\Car;
use App\Models\Category;
use App\Models\Location;
use App\Models\Order;
use App\Services\OrderAvailabilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilityBlockingTest extends TestCase
{
    use RefreshDatabase;

    protected function makeCarWithLocations(int $units = 3): array
    {
        $category = Category::query()->create(['name' => 'Fleet', 'is_active' => true]);
        $car = Car::query()->create([
            'category_id' => $category->id,
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
}

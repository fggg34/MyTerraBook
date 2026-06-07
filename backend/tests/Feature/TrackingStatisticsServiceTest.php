<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Car;
use App\Models\MainCategory;
use App\Models\SubCategory;
use App\Models\Location;
use App\Models\Order;
use App\Models\TrackingEvent;
use App\Services\Admin\TrackingStatisticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackingStatisticsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_tracking_statistics_works_without_tracking_campaigns_or_events(): void
    {
        $main = MainCategory::query()->firstOrCreate(['slug' => 'car'], ['name' => 'Car', 'is_active' => true]);
        $category = SubCategory::query()->create(['main_category_id' => $main->id, 'name' => 'Stats', 'is_active' => true, 'is_search_filter' => true]);
        $car = Car::query()->create([
            'sub_category_id' => $category->id,
            'name' => 'Compact',
            'units_available' => 2,
            'is_active' => true,
        ]);
        $location = Location::query()->create(['name' => 'HQ', 'is_active' => true]);

        Order::query()->create([
            'reference' => 'ORD-STAT-FB-1',
            'car_id' => $car->id,
            'pickup_location_id' => $location->id,
            'dropoff_location_id' => $location->id,
            'pickup_at' => now()->subDay(),
            'dropoff_at' => now()->addDay(),
            'order_status' => OrderStatus::Confirmed,
            'customer_name' => 'Fallback User',
            'customer_email' => 'fallback@example.test',
            'customer_country' => 'AL',
            'base_rental_cents' => 10000,
            'total_cents' => 10000,
            'currency' => 'EUR',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        $service = app(TrackingStatisticsService::class);
        $payload = $service->forPeriod(now()->subDays(7)->startOfDay(), now()->endOfDay());

        $this->assertNotEmpty($payload['most_demanded_days']);
        $this->assertSame(1, $payload['average_values']['total_visitors']);
        $this->assertSame(1, $payload['average_values']['total_bookings']);
        $this->assertNotEmpty($payload['best_referrers']);
        $this->assertContains('AL', $payload['countries']);
    }

    public function test_tracking_statistics_returns_requested_sections(): void
    {
        $main = MainCategory::query()->firstOrCreate(['slug' => 'car'], ['name' => 'Car', 'is_active' => true]);
        $category = SubCategory::query()->create(['main_category_id' => $main->id, 'name' => 'Stats', 'is_active' => true, 'is_search_filter' => true]);
        $car = Car::query()->create([
            'sub_category_id' => $category->id,
            'name' => 'Compact',
            'units_available' => 2,
            'is_active' => true,
        ]);
        $location = Location::query()->create(['name' => 'HQ', 'is_active' => true]);

        TrackingEvent::query()->create([
            'event_type' => 'request',
            'country' => 'AL',
            'referrer_host' => 'google.com',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);
        TrackingEvent::query()->create([
            'event_type' => 'page_view',
            'country' => 'AL',
            'referrer_host' => 'google.com',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        Order::query()->create([
            'reference' => 'ORD-STAT-1',
            'car_id' => $car->id,
            'pickup_location_id' => $location->id,
            'dropoff_location_id' => $location->id,
            'pickup_at' => now()->subDay(),
            'dropoff_at' => now()->addDay(),
            'order_status' => OrderStatus::Confirmed,
            'customer_name' => 'A',
            'customer_email' => 'a@example.test',
            'customer_country' => 'AL',
            'base_rental_cents' => 10000,
            'total_cents' => 10000,
            'currency' => 'EUR',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        $service = app(TrackingStatisticsService::class);
        $payload = $service->forPeriod(now()->subDays(7)->startOfDay(), now()->endOfDay());

        $this->assertNotEmpty($payload['most_demanded_days']);
        $this->assertSame(1, $payload['average_values']['total_visitors']);
        $this->assertSame(1, $payload['average_values']['total_bookings']);
        $this->assertNotEmpty($payload['best_referrers']);
        $this->assertNotEmpty($payload['conversion_rates']);
    }
}

<?php

namespace Tests\Feature;

use App\Enums\GuestHouseBookingStatus;
use App\Enums\GuestHouseStatus;
use App\Enums\GuestHouseType;
use App\Enums\OrderStatus;
use App\Models\Car;
use App\Models\MainCategory;
use App\Models\SubCategory;
use App\Models\GuestHouse;
use App\Models\GuestHouseBooking;
use App\Models\Location;
use App\Models\Order;
use App\Services\Admin\AdminDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_snapshot_returns_expected_counts_and_revenue(): void
    {
        $main = MainCategory::query()->firstOrCreate(['slug' => 'car'], ['name' => 'Car', 'is_active' => true]);
        $category = SubCategory::query()->create(['main_category_id' => $main->id, 'name' => 'Dashboard', 'is_active' => true, 'is_search_filter' => true]);
        $car = Car::query()->create([
            'sub_category_id' => $category->id,
            'name' => 'Dashboard Car',
            'units_available' => 2,
            'is_active' => true,
        ]);
        $location = Location::query()->create(['name' => 'HQ', 'is_active' => true]);

        Order::query()->create([
            'reference' => 'ORD-DASH-1',
            'car_id' => $car->id,
            'pickup_location_id' => $location->id,
            'dropoff_location_id' => $location->id,
            'pickup_at' => now()->subDay(),
            'dropoff_at' => now()->addDay(),
            'order_status' => OrderStatus::Confirmed,
            'customer_name' => 'Dash User',
            'customer_email' => 'dash@example.com',
            'customer_country' => 'AL',
            'base_rental_cents' => 10000,
            'extras_cents' => 0,
            'fees_cents' => 0,
            'discount_cents' => 0,
            'tax_cents' => 0,
            'total_cents' => 10000,
            'currency' => 'EUR',
        ]);

        Order::query()->create([
            'reference' => 'ORD-DASH-2',
            'car_id' => $car->id,
            'pickup_location_id' => $location->id,
            'dropoff_location_id' => $location->id,
            'pickup_at' => now()->addWeek(),
            'dropoff_at' => now()->addWeeks(2),
            'order_status' => OrderStatus::Pending,
            'customer_name' => 'Pending User',
            'customer_email' => 'pending@example.com',
            'base_rental_cents' => 5000,
            'extras_cents' => 0,
            'fees_cents' => 0,
            'discount_cents' => 0,
            'tax_cents' => 0,
            'total_cents' => 5000,
            'currency' => 'EUR',
        ]);

        $house = GuestHouse::query()->create([
            'name' => 'Dashboard House',
            'slug' => 'dashboard-house',
            'type' => GuestHouseType::Apartment,
            'status' => GuestHouseStatus::Active,
            'max_guests' => 4,
            'min_nights' => 1,
            'base_price_per_night' => 8000,
            'cleaning_fee' => 0,
        ]);

        GuestHouseBooking::query()->create([
            'guest_house_id' => $house->id,
            'booking_reference' => 'GH-DASH-1',
            'status' => GuestHouseBookingStatus::Confirmed,
            'guest_name' => 'Guest',
            'guest_email' => 'guest@example.com',
            'check_in' => now()->toDateString(),
            'check_out' => now()->addDays(2)->toDateString(),
            'nights' => 2,
            'guests_count' => 2,
            'base_total' => 16000,
            'cleaning_fee' => 0,
            'security_deposit' => 0,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 16000,
        ]);

        $snapshot = app(AdminDashboardService::class)->snapshot();

        $this->assertSame('260.00', $snapshot['revenue']['total_formatted']);
        $this->assertSame(1, $snapshot['operations']['active_rentals']);
        $this->assertSame(1, $snapshot['operations']['active_stays']);
        $this->assertSame(1, $snapshot['operations']['pending_car_orders']);
        $this->assertSame(2, $snapshot['volume']['total_car_orders']);
        $this->assertSame(1, $snapshot['volume']['total_gh_bookings']);
        $this->assertSame(1, $snapshot['catalog']['active_cars']);
        $this->assertSame(1, $snapshot['catalog']['active_guest_houses']);
        $this->assertNotEmpty($snapshot['top_countries']);
        $this->assertSame('AL', $snapshot['top_countries'][0]['country']);

        $chart = app(AdminDashboardService::class)->operationsOverviewChart(7);

        $this->assertCount(7, $chart['labels']);
        $this->assertCount(3, $chart['datasets']);
        $this->assertSame('Daily revenue (€)', $chart['datasets'][0]['label']);
        $this->assertSame(260.0, $chart['datasets'][0]['data'][6]);
        $this->assertSame('Active rentals', $chart['datasets'][1]['label']);
        $this->assertSame(1.0, $chart['datasets'][1]['data'][6]);
        $this->assertSame('Guest stays', $chart['datasets'][2]['label']);
        $this->assertSame(1.0, $chart['datasets'][2]['data'][6]);

        $this->assertTrue(app(AdminDashboardService::class)->operationsOverviewChartHasActivity($chart));
        $this->assertNotEmpty(app(AdminDashboardService::class)->operationsOverviewChartOptions($chart)['scales']);
    }

    public function test_operations_overview_chart_reports_empty_state_when_no_activity(): void
    {
        $chart = app(AdminDashboardService::class)->operationsOverviewChart(7);

        $this->assertFalse(app(AdminDashboardService::class)->operationsOverviewChartHasActivity($chart));
    }
}

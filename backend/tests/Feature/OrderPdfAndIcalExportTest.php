<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Car;
use App\Models\CarDamageMarker;
use App\Models\CarDistinctiveFeatureDefinition;
use App\Models\CarUnit;
use App\Models\CarUnitDistinctiveValue;
use App\Models\MainCategory;
use App\Models\SubCategory;
use App\Models\Location;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderPdfAndIcalExportTest extends TestCase
{
    use RefreshDatabase;

    protected function seedOrderFor(User $owner, OrderStatus $status): Order
    {
        $main = MainCategory::query()->firstOrCreate(['slug' => 'car'], ['name' => 'Car', 'is_active' => true]);
        $category = SubCategory::query()->create(['main_category_id' => $main->id, 'name' => 'X', 'is_active' => true, 'is_search_filter' => true]);
        $car = Car::query()->create([
            'sub_category_id' => $category->id,
            'name' => 'SUV',
            'units_available' => 1,
            'is_active' => true,
        ]);
        $loc = Location::query()->create(['name' => 'L', 'is_active' => true]);

        return Order::query()->create([
            'reference' => 'ORD-PDF-1',
            'user_id' => $owner->id,
            'car_id' => $car->id,
            'pickup_location_id' => $loc->id,
            'dropoff_location_id' => $loc->id,
            'pickup_at' => now()->addDay(),
            'dropoff_at' => now()->addDays(3),
            'order_status' => $status,
            'customer_name' => 'Pat',
            'customer_email' => 'pat@example.com',
            'base_rental_cents' => 10000,
            'extras_cents' => 0,
            'fees_cents' => 0,
            'discount_cents' => 0,
            'tax_cents' => 0,
            'total_cents' => 10000,
            'currency' => 'EUR',
        ]);
    }

    public function test_admin_can_download_confirmed_order_pdf(): void
    {
        $this->seed();
        $admin = User::query()->where('email', 'admin@terrabook.test')->firstOrFail();
        $customer = User::query()->where('email', 'customer@terrabook.test')->firstOrFail();
        $order = $this->seedOrderFor($customer, OrderStatus::Confirmed);

        $response = $this->actingAs($admin, 'sanctum')
            ->get('/api/admin/orders/'.$order->id.'/contract.pdf');

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('Content-Type'));
    }

    public function test_pdf_not_available_for_pending_order(): void
    {
        $this->seed();
        $admin = User::query()->where('email', 'admin@terrabook.test')->firstOrFail();
        $customer = User::query()->where('email', 'customer@terrabook.test')->firstOrFail();
        $order = $this->seedOrderFor($customer, OrderStatus::Pending);

        $this->actingAs($admin, 'sanctum')
            ->get('/api/admin/orders/'.$order->id.'/contract.pdf')
            ->assertNotFound();
    }

    public function test_customer_can_download_own_order_ics(): void
    {
        $this->seed();
        $customer = User::query()->where('email', 'customer@terrabook.test')->firstOrFail();
        $order = $this->seedOrderFor($customer, OrderStatus::Pending);

        $response = $this->actingAs($customer, 'sanctum')
            ->get('/api/me/orders/'.$order->id.'/calendar.ics');

        $response->assertOk();
        $this->assertStringContainsString('text/calendar', (string) $response->headers->get('Content-Type'));
        $this->assertStringContainsString('BEGIN:VCALENDAR', $response->getContent());
        $this->assertStringContainsString('VEVENT', $response->getContent());
    }

    public function test_admin_can_download_checkin_pdf_with_unit_and_damages(): void
    {
        $this->seed();
        $admin = User::query()->where('email', 'admin@terrabook.test')->firstOrFail();
        $customer = User::query()->where('email', 'customer@terrabook.test')->firstOrFail();
        $order = $this->seedOrderFor($customer, OrderStatus::Confirmed);

        $carUnit = CarUnit::query()->create([
            'car_id' => $order->car_id,
            'is_active' => true,
            'sort_order' => 0,
        ]);
        $feature = CarDistinctiveFeatureDefinition::query()->create([
            'car_id' => $order->car_id,
            'name' => 'License Plate',
            'sort_order' => 0,
        ]);
        CarUnitDistinctiveValue::query()->create([
            'car_unit_id' => $carUnit->id,
            'car_distinctive_feature_definition_id' => $feature->id,
            'value' => 'EN826SH',
        ]);
        CarDamageMarker::query()->create([
            'car_unit_id' => $carUnit->id,
            'position_x' => 40.5,
            'position_y' => 52.5,
            'description' => 'Rear door scratch',
            'marked_at' => now(),
        ]);

        $order->update(['car_unit_id' => $carUnit->id]);

        $response = $this->actingAs($admin, 'sanctum')
            ->get('/api/admin/orders/'.$order->id.'/checkin.pdf');

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('Content-Type'));
    }
}

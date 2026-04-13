<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Car;
use App\Models\Category;
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
        $category = Category::query()->create(['name' => 'X', 'is_active' => true]);
        $car = Car::query()->create([
            'category_id' => $category->id,
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
}

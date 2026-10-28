<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Filament\Pages\OrdersCalendar;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Models\Car;
use App\Models\CarUnit;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrdersCalendarPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_orders_calendar_shows_the_fleet_board_and_booking_details(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $car = Car::factory()->create(['name' => 'Highland Camper', 'units_available' => 1]);
        $unit = CarUnit::factory()->create(['car_id' => $car->id, 'sort_order' => 1]);
        $order = Order::factory()->create([
            'car_id' => $car->id,
            'car_unit_id' => $unit->id,
            'customer_name' => 'Anna Sigurdardottir',
            'customer_phone' => '+3545550101',
            'reference' => 'ORD-CALTEST01',
            'pickup_at' => now()->addDay()->setTime(10, 0),
            'dropoff_at' => now()->addDays(4)->setTime(10, 0),
            'order_status' => OrderStatus::Confirmed,
            'total_cents' => 15000,
            'currency' => 'EUR',
        ]);

        $this->get('/admin/impact-rent/orders-calendar')
            ->assertOk()
            ->assertSee('Calendar')
            ->assertSee('Highland Camper')
            ->assertSee('Unit 1')
            ->assertSee('ORD-CALTEST01')
            ->assertSee('Anna Sigurdardottir')
            ->assertDontSee('Could not load calendar assets');

        Livewire::test(OrdersCalendar::class)
            ->call('selectOrder', $order->id)
            ->assertSee('Open reservation')
            ->assertSee('+3545550101')
            ->assertSee('150.00 EUR');

        Livewire::test(EditOrder::class, ['record' => $order->getRouteKey()])
            ->assertSee('Car reservations')
            ->assertSee('ORD-CALTEST01')
            ->assertSee('Anna Sigurdardottir')
            ->assertSee('EUR 150');
    }
}

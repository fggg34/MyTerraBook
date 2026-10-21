<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrdersAdminListTest extends TestCase
{
    use RefreshDatabase;

    public function test_vehicle_rental_list_filters_customers_status_and_payment(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $paid = Order::factory()->create([
            'customer_name' => 'Ada Lovelace',
            'customer_email' => 'ada@example.com',
            'order_status' => OrderStatus::Confirmed,
            'total_cents' => 10000,
        ]);
        OrderPayment::factory()->create([
            'order_id' => $paid->id,
            'amount_cents' => 10000,
            'status' => 'completed',
        ]);

        $unpaid = Order::factory()->create([
            'customer_name' => 'Grace Hopper',
            'customer_email' => 'grace@example.com',
            'order_status' => OrderStatus::Pending,
            'total_cents' => 8000,
        ]);

        $this->get('/admin/impact-rent/orders')
            ->assertOk()
            ->assertSee('Vehicle Rental')
            ->assertSee('Booking status')
            ->assertSee('Pickup location')
            ->assertSee('Part paid');

        Livewire::test(ListOrders::class)
            ->filterTable('customer', ['value' => 'Ada'])
            ->assertCanSeeTableRecords([$paid])
            ->assertCanNotSeeTableRecords([$unpaid])
            ->resetTableFilters()
            ->filterTable('order_status', OrderStatus::Pending->value)
            ->assertCanSeeTableRecords([$unpaid])
            ->assertCanNotSeeTableRecords([$paid])
            ->resetTableFilters()
            ->filterTable('payment', 'paid')
            ->assertCanSeeTableRecords([$paid])
            ->assertCanNotSeeTableRecords([$unpaid]);
    }
}

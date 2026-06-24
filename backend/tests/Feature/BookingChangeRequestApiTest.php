<?php

namespace Tests\Feature;

use App\Enums\BookingChangeRequestStatus;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\BookingChangeRequest;
use App\Models\Car;
use App\Models\DailyFare;
use App\Models\Location;
use App\Models\MainCategory;
use App\Models\Order;
use App\Models\PriceType;
use App\Models\Setting;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingChangeRequestApiTest extends TestCase
{
    use RefreshDatabase;

    private function seedOrder(): Order
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

        $pickupAt = now()->addDays(10);
        $dropoffAt = now()->addDays(13);

        return Order::query()->create([
            'reference' => 'TB-TEST-001',
            'car_id' => $car->id,
            'price_type_id' => $priceType->id,
            'pickup_location_id' => $pickup->id,
            'dropoff_location_id' => $dropoff->id,
            'pickup_at' => $pickupAt,
            'dropoff_at' => $dropoffAt,
            'customer_name' => 'Alex Guest',
            'customer_email' => 'alex@example.com',
            'order_status' => OrderStatus::Confirmed,
            'base_rental_cents' => 15000,
            'extras_cents' => 0,
            'fees_cents' => 0,
            'discount_cents' => 0,
            'tax_cents' => 0,
            'total_cents' => 15000,
            'currency' => 'EUR',
        ]);
    }

    public function test_guest_can_create_modification_request(): void
    {
        $order = $this->seedOrder();

        $response = $this->postJson('/api/booking-change-requests', [
            'bookable_kind' => 'order',
            'reference' => $order->reference,
            'customer_email' => 'alex@example.com',
            'type' => 'modification',
            'customer_message' => 'Please extend by 2 days',
            'requested_changes' => [
                'dropoff_at' => $order->dropoff_at->copy()->addDays(2)->toDateTimeString(),
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.type', 'modification')
            ->assertJsonPath('data.status', BookingChangeRequestStatus::Pending->value);

        $this->assertDatabaseCount('booking_change_requests', 1);
    }

    public function test_admin_can_apply_modification_request(): void
    {
        $order = $this->seedOrder();
        $newDropoff = $order->dropoff_at->copy()->addDays(2);

        $changeRequest = BookingChangeRequest::query()->create([
            'bookable_type' => Order::class,
            'bookable_id' => $order->id,
            'type' => 'modification',
            'status' => BookingChangeRequestStatus::Pending,
            'customer_message' => 'Extend rental',
            'requested_changes' => [
                'dropoff_at' => $newDropoff->toDateTimeString(),
            ],
            'pricing_before' => ['total_cents' => 15000],
            'pricing_after' => null,
            'price_delta_cents' => 10000,
        ]);

        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/admin/booking-change-requests/{$changeRequest->id}/apply", [
                'admin_response' => 'Approved — dates updated.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', BookingChangeRequestStatus::Applied->value);

        $order->refresh();
        $this->assertTrue($order->dropoff_at->equalTo($newDropoff));
        $this->assertGreaterThan(15000, (int) $order->total_cents);
    }

    public function test_host_can_apply_modification_request_for_own_vehicle(): void
    {
        $host = User::factory()->host()->create();
        $otherHost = User::factory()->host()->create();

        Setting::putValue('shop.currency', ['code' => 'EUR']);
        Setting::putValue('shop.default_tax', ['basis_points' => 0]);

        $main = MainCategory::query()->firstOrCreate(['slug' => 'car'], ['name' => 'Car', 'is_active' => true]);
        $category = SubCategory::query()->create(['main_category_id' => $main->id, 'name' => 'Eco', 'is_active' => true, 'is_search_filter' => true]);
        $car = Car::query()->create([
            'user_id' => $host->id,
            'sub_category_id' => $category->id,
            'name' => 'Host Vehicle',
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

        $pickupAt = now()->addDays(10);
        $dropoffAt = now()->addDays(13);
        $newDropoff = $dropoffAt->copy()->addDays(2);

        $order = Order::query()->create([
            'reference' => 'TB-HOST-001',
            'car_id' => $car->id,
            'price_type_id' => $priceType->id,
            'pickup_location_id' => $pickup->id,
            'dropoff_location_id' => $dropoff->id,
            'pickup_at' => $pickupAt,
            'dropoff_at' => $dropoffAt,
            'customer_name' => 'Alex Guest',
            'customer_email' => 'alex@example.com',
            'order_status' => OrderStatus::Confirmed,
            'base_rental_cents' => 15000,
            'extras_cents' => 0,
            'fees_cents' => 0,
            'discount_cents' => 0,
            'tax_cents' => 0,
            'total_cents' => 15000,
            'currency' => 'EUR',
        ]);

        $changeRequest = BookingChangeRequest::query()->create([
            'bookable_type' => Order::class,
            'bookable_id' => $order->id,
            'type' => 'modification',
            'status' => BookingChangeRequestStatus::Pending,
            'customer_message' => 'Extend rental by 2 days',
            'requested_changes' => [
                'dropoff_at' => $newDropoff->toDateTimeString(),
            ],
            'pricing_before' => ['total_cents' => 15000],
            'price_delta_cents' => 10000,
        ]);

        $this->actingAs($host, 'sanctum')
            ->postJson("/api/host/booking-change-requests/{$changeRequest->id}/apply", [
                'admin_response' => 'Approved by host.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', BookingChangeRequestStatus::Applied->value);

        $this->actingAs($otherHost, 'sanctum')
            ->postJson("/api/host/booking-change-requests/{$changeRequest->id}/reject", [
                'admin_response' => 'Not your booking.',
            ])
            ->assertForbidden();
    }

    public function test_host_can_update_order_protection_and_extras(): void
    {
        $host = User::factory()->host()->create();

        Setting::putValue('shop.currency', ['code' => 'EUR']);
        Setting::putValue('shop.default_tax', ['basis_points' => 0]);

        $main = MainCategory::query()->firstOrCreate(['slug' => 'car'], ['name' => 'Car', 'is_active' => true]);
        $category = SubCategory::query()->create(['main_category_id' => $main->id, 'name' => 'Eco', 'is_active' => true, 'is_search_filter' => true]);
        $car = Car::query()->create([
            'user_id' => $host->id,
            'sub_category_id' => $category->id,
            'name' => 'Host Vehicle',
            'units_available' => 1,
            'is_active' => true,
        ]);
        $pickup = Location::query()->create(['name' => 'P1', 'is_active' => true]);
        $dropoff = Location::query()->create(['name' => 'D1', 'is_active' => true]);
        $car->locations()->attach([
            $pickup->id => ['allows_pickup' => true, 'allows_dropoff' => true],
            $dropoff->id => ['allows_pickup' => true, 'allows_dropoff' => true],
        ]);
        $basic = PriceType::query()->create(['name' => 'Basic', 'is_active' => true]);
        $plus = PriceType::query()->create(['name' => 'Plus', 'is_active' => true]);
        DailyFare::query()->create([
            'car_id' => $car->id,
            'price_type_id' => $basic->id,
            'from_days' => 1,
            'to_days' => 30,
            'price_per_day_cents' => 5000,
        ]);
        DailyFare::query()->create([
            'car_id' => $car->id,
            'price_type_id' => $plus->id,
            'from_days' => 1,
            'to_days' => 30,
            'price_per_day_cents' => 7000,
        ]);
        $option = \App\Models\RentalOption::query()->create([
            'name' => 'GPS',
            'cost_cents' => 1000,
            'is_daily_cost' => true,
            'is_active' => true,
        ]);
        $car->rentalOptions()->attach($option->id, ['cost_cents' => 1000, 'is_daily_cost' => true]);

        $order = Order::query()->create([
            'reference' => 'TB-HOST-OPT',
            'car_id' => $car->id,
            'price_type_id' => $basic->id,
            'pickup_location_id' => $pickup->id,
            'dropoff_location_id' => $dropoff->id,
            'pickup_at' => now()->addDays(10),
            'dropoff_at' => now()->addDays(13),
            'customer_name' => 'Alex Guest',
            'customer_email' => 'alex@example.com',
            'order_status' => OrderStatus::Confirmed,
            'base_rental_cents' => 15000,
            'extras_cents' => 0,
            'fees_cents' => 0,
            'discount_cents' => 0,
            'tax_cents' => 0,
            'total_cents' => 15000,
            'currency' => 'EUR',
        ]);

        $this->actingAs($host, 'sanctum')
            ->patchJson("/api/host/bookings/cars/{$order->id}", [
                'price_type_id' => $plus->id,
                'rental_options' => [$option->id],
            ])
            ->assertOk()
            ->assertJsonPath('data.price_type.id', $plus->id);

        $order->refresh();
        $this->assertSame($plus->id, (int) $order->price_type_id);
        $this->assertGreaterThan(15000, (int) $order->total_cents);
        $this->assertDatabaseHas('order_rental_options', [
            'order_id' => $order->id,
            'rental_option_id' => $option->id,
        ]);
    }
}

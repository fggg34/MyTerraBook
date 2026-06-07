<?php

namespace Tests\Feature;

use App\Enums\GuestHouseBookingStatus;
use App\Enums\GuestHouseCancellationPolicy;
use App\Enums\GuestHouseStatus;
use App\Enums\GuestHouseType;
use App\Enums\UserRole;
use App\Models\GuestHouse;
use App\Models\GuestHouseBooking;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ClientPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_list_own_orders(): void
    {
        $customer = User::factory()->customer()->create();
        $other = User::factory()->customer()->create();

        Order::factory()->create(['user_id' => $customer->id]);
        Order::factory()->create(['user_id' => $other->id]);

        Sanctum::actingAs($customer);

        $response = $this->getJson('/api/me/orders');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_customer_can_list_own_guest_house_bookings(): void
    {
        $customer = User::factory()->customer()->create();
        $house = $this->createGuestHouse();

        GuestHouseBooking::query()->create([
            'guest_house_id' => $house->id,
            'user_id' => $customer->id,
            'booking_reference' => 'GH-TEST-001',
            'status' => GuestHouseBookingStatus::Confirmed,
            'guest_name' => $customer->name,
            'guest_email' => $customer->email,
            'guest_phone' => $customer->phone,
            'check_in' => now()->addDays(14)->toDateString(),
            'check_out' => now()->addDays(17)->toDateString(),
            'nights' => 3,
            'guests_count' => 2,
            'base_total' => 30000,
            'total_amount' => 30000,
        ]);

        Sanctum::actingAs($customer);

        $this->getJson('/api/me/guest-house-bookings')
            ->assertOk()
            ->assertJsonPath('data.0.booking_reference', 'GH-TEST-001');
    }

    public function test_customer_cannot_access_host_routes(): void
    {
        Sanctum::actingAs(User::factory()->customer()->create());

        $this->getJson('/api/host/dashboard')->assertForbidden();
    }

    public function test_customer_can_cancel_guest_house_booking_within_window(): void
    {
        $customer = User::factory()->customer()->create();
        $house = $this->createGuestHouse([
            'cancellation_policy' => GuestHouseCancellationPolicy::Flexible,
        ]);

        GuestHouseBooking::query()->create([
            'guest_house_id' => $house->id,
            'user_id' => $customer->id,
            'booking_reference' => 'GH-CANCEL-01',
            'status' => GuestHouseBookingStatus::Confirmed,
            'guest_name' => $customer->name,
            'guest_email' => $customer->email,
            'check_in' => now()->addDays(10)->toDateString(),
            'check_out' => now()->addDays(12)->toDateString(),
            'nights' => 2,
            'guests_count' => 2,
            'base_total' => 20000,
            'total_amount' => 20000,
        ]);

        Sanctum::actingAs($customer);

        $this->postJson('/api/me/guest-house-bookings/GH-CANCEL-01/cancel', [
            'reason' => 'Cancelled by guest',
        ])
            ->assertOk()
            ->assertJsonPath('data.status', GuestHouseBookingStatus::Cancelled->value);

        $this->assertDatabaseHas('guest_house_bookings', [
            'booking_reference' => 'GH-CANCEL-01',
            'status' => GuestHouseBookingStatus::Cancelled->value,
        ]);
    }

    public function test_customer_can_fetch_unified_rental_history(): void
    {
        $customer = User::factory()->customer()->create();
        $house = $this->createGuestHouse();

        Order::factory()->create(['user_id' => $customer->id]);

        GuestHouseBooking::query()->create([
            'guest_house_id' => $house->id,
            'user_id' => $customer->id,
            'booking_reference' => 'GH-HIST-001',
            'status' => GuestHouseBookingStatus::Confirmed,
            'guest_name' => $customer->name,
            'guest_email' => $customer->email,
            'guest_phone' => $customer->phone,
            'check_in' => now()->addDays(5)->toDateString(),
            'check_out' => now()->addDays(8)->toDateString(),
            'nights' => 3,
            'guests_count' => 2,
            'base_total' => 30000,
            'total_amount' => 30000,
        ]);

        Sanctum::actingAs($customer);

        $response = $this->getJson('/api/me/history');

        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
        $this->assertSame(2, $response->json('meta.total'));
        $this->assertSame(1, $response->json('meta.guesthouse'));
    }

    public function test_customer_can_export_rental_history_csv(): void
    {
        $customer = User::factory()->customer()->create();
        Order::factory()->create(['user_id' => $customer->id]);

        Sanctum::actingAs($customer);

        $this->get('/api/me/history/export.csv')
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_customer_cannot_download_other_users_order_contract(): void
    {
        $customer = User::factory()->customer()->create();
        $other = User::factory()->customer()->create();
        $order = Order::factory()->create(['user_id' => $other->id]);

        Sanctum::actingAs($customer);

        $this->get('/api/me/orders/'.$order->id.'/contract.pdf')->assertForbidden();
    }

    public function test_customer_profile_update_requires_phone(): void
    {
        $customer = User::factory()->customer()->create([
            'phone' => '+354 555 1234',
        ]);

        Sanctum::actingAs($customer);

        $this->patchJson('/api/me/profile', [
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => '',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['phone']);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createGuestHouse(array $overrides = []): GuestHouse
    {
        return GuestHouse::query()->create(array_merge([
            'user_id' => User::factory()->host()->create()->id,
            'name' => 'Test Guesthouse',
            'slug' => 'test-guesthouse-'.uniqid(),
            'type' => GuestHouseType::Apartment,
            'status' => GuestHouseStatus::Active,
            'city' => 'Reykjavík',
            'max_guests' => 2,
            'bedrooms' => 1,
            'bathrooms' => 1,
            'beds' => 1,
            'min_nights' => 1,
            'base_price_per_night' => 10000,
            'cancellation_policy' => GuestHouseCancellationPolicy::Moderate,
        ], $overrides));
    }
}

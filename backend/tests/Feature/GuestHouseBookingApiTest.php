<?php

namespace Tests\Feature;

use App\Enums\GuestHouseBookingStatus;
use App\Enums\GuestHouseStatus;
use App\Enums\GuestHouseType;
use App\Enums\UserRole;
use App\Models\GuestHouse;
use App\Models\GuestHouseBooking;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GuestHouseBookingApiTest extends TestCase
{
    use RefreshDatabase;

    private function seedHouse(): GuestHouse
    {
        Setting::putValue('shop.currency', ['code' => 'EUR']);
        Setting::putValue('shop.default_tax', ['basis_points' => 0]);

        return GuestHouse::query()->create([
            'name' => 'API Test House',
            'slug' => 'api-test-house',
            'type' => GuestHouseType::Apartment,
            'status' => GuestHouseStatus::Active,
            'max_guests' => 4,
            'min_nights' => 1,
            'base_price_per_night' => 8000,
            'cleaning_fee' => 0,
        ]);
    }

    public function test_guest_can_get_quote(): void
    {
        $house = $this->seedHouse();

        $response = $this->postJson("/api/guest-houses/{$house->slug}/quote", [
            'check_in' => now()->addDays(3)->toDateString(),
            'check_out' => now()->addDays(5)->toDateString(),
            'guests_count' => 2,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.nights', 2)
            ->assertJsonPath('data.base_total', 16000);
    }

    public function test_guest_can_create_booking(): void
    {
        $house = $this->seedHouse();

        $response = $this->postJson('/api/guest-houses/bookings', [
            'guest_house_slug' => $house->slug,
            'check_in' => now()->addDays(3)->toDateString(),
            'check_out' => now()->addDays(5)->toDateString(),
            'guests_count' => 2,
            'guest_name' => 'Jane Doe',
            'guest_email' => 'jane@example.com',
            'guest_phone' => '+123456789',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['data' => ['booking_reference', 'status']]);

        $this->assertDatabaseHas('guest_house_bookings', [
            'guest_email' => 'jane@example.com',
            'status' => GuestHouseBookingStatus::Confirmed->value,
        ]);
    }

    public function test_double_booking_rejected(): void
    {
        $house = $this->seedHouse();
        $checkIn = now()->addDays(3)->toDateString();
        $checkOut = now()->addDays(5)->toDateString();

        GuestHouseBooking::query()->create([
            'guest_house_id' => $house->id,
            'booking_reference' => 'GH-TEST-00001',
            'status' => GuestHouseBookingStatus::Confirmed,
            'guest_name' => 'First',
            'guest_email' => 'first@example.com',
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'nights' => 2,
            'guests_count' => 2,
            'base_total' => 16000,
            'total_amount' => 16000,
        ]);

        $response = $this->postJson('/api/guest-houses/bookings', [
            'guest_house_slug' => $house->slug,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'guests_count' => 2,
            'guest_name' => 'Second',
            'guest_email' => 'second@example.com',
            'guest_phone' => '+123456789',
        ]);

        $response->assertStatus(409);
    }

    public function test_authenticated_customer_can_view_own_bookings(): void
    {
        $user = User::factory()->create(['role' => UserRole::Customer]);
        $house = $this->seedHouse();
        GuestHouseBooking::query()->create([
            'guest_house_id' => $house->id,
            'user_id' => $user->id,
            'booking_reference' => 'GH-MINE-00001',
            'status' => GuestHouseBookingStatus::Pending,
            'guest_name' => $user->name,
            'guest_email' => $user->email,
            'check_in' => now()->addDays(5),
            'check_out' => now()->addDays(7),
            'nights' => 2,
            'guests_count' => 2,
            'base_total' => 16000,
            'total_amount' => 16000,
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/me/guest-house-bookings')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_customer_cannot_view_other_customer_bookings(): void
    {
        $owner = User::factory()->create(['role' => UserRole::Customer]);
        $other = User::factory()->create(['role' => UserRole::Customer]);
        $house = $this->seedHouse();
        $booking = GuestHouseBooking::query()->create([
            'guest_house_id' => $house->id,
            'user_id' => $owner->id,
            'booking_reference' => 'GH-OTHER-00001',
            'status' => GuestHouseBookingStatus::Pending,
            'guest_name' => $owner->name,
            'guest_email' => $owner->email,
            'check_in' => now()->addDays(5),
            'check_out' => now()->addDays(7),
            'nights' => 2,
            'guests_count' => 2,
            'base_total' => 16000,
            'total_amount' => 16000,
        ]);

        Sanctum::actingAs($other);

        $this->getJson('/api/me/guest-house-bookings/'.$booking->booking_reference)
            ->assertNotFound();
    }

    public function test_admin_can_list_all_bookings(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $house = $this->seedHouse();
        GuestHouseBooking::query()->create([
            'guest_house_id' => $house->id,
            'booking_reference' => 'GH-ADM-00001',
            'status' => GuestHouseBookingStatus::Pending,
            'guest_name' => 'Guest',
            'guest_email' => 'g@example.com',
            'check_in' => now()->addDays(2),
            'check_out' => now()->addDays(4),
            'nights' => 2,
            'guests_count' => 2,
            'base_total' => 16000,
            'total_amount' => 16000,
        ]);

        Sanctum::actingAs($admin);

        $this->getJson('/api/admin/guest-house-bookings')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_admin_can_change_booking_status(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $house = $this->seedHouse();
        $booking = GuestHouseBooking::query()->create([
            'guest_house_id' => $house->id,
            'booking_reference' => 'GH-ADM-00002',
            'status' => GuestHouseBookingStatus::Pending,
            'guest_name' => 'Guest',
            'guest_email' => 'g@example.com',
            'check_in' => now()->addDays(2),
            'check_out' => now()->addDays(4),
            'nights' => 2,
            'guests_count' => 2,
            'base_total' => 16000,
            'total_amount' => 16000,
        ]);

        Sanctum::actingAs($admin);

        $this->patchJson("/api/admin/guest-house-bookings/{$booking->id}/status", [
            'status' => 'confirmed',
        ])
            ->assertOk()
            ->assertJsonPath('data.status', 'confirmed');
    }
}

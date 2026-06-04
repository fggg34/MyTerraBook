<?php

namespace Tests\Feature;

use App\Enums\GuestHouseAvailabilityBlockReason;
use App\Enums\GuestHouseBookingStatus;
use App\Enums\GuestHouseStatus;
use App\Enums\GuestHouseType;
use App\Models\GuestHouse;
use App\Models\GuestHouseAvailabilityBlock;
use App\Models\GuestHouseBooking;
use App\Models\Setting;
use App\Services\GuestHouseAvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestHouseAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    private function seedHouse(): GuestHouse
    {
        Setting::putValue('shop.currency', ['code' => 'EUR']);
        Setting::putValue('shop.default_tax', ['basis_points' => 0]);

        return GuestHouse::query()->create([
            'name' => 'Avail House',
            'slug' => 'avail-house',
            'type' => GuestHouseType::Room,
            'status' => GuestHouseStatus::Active,
            'max_guests' => 2,
            'min_nights' => 1,
            'base_price_per_night' => 5000,
        ]);
    }

    public function test_blocked_dates_returned_correctly(): void
    {
        $house = $this->seedHouse();
        GuestHouseAvailabilityBlock::query()->create([
            'guest_house_id' => $house->id,
            'blocked_from' => now()->addDays(10),
            'blocked_to' => now()->addDays(12),
            'reason' => GuestHouseAvailabilityBlockReason::Maintenance,
        ]);

        $from = now()->addDays(9)->toDateString();
        $to = now()->addDays(14)->toDateString();

        $response = $this->getJson("/api/guest-houses/{$house->slug}/availability?from={$from}&to={$to}");

        $response->assertOk();
        $blocked = $response->json('data.blocked_dates');
        $this->assertNotEmpty($blocked);
    }

    public function test_confirmed_booking_blocks_dates(): void
    {
        $house = $this->seedHouse();
        $checkIn = now()->addDays(5)->toDateString();
        $checkOut = now()->addDays(8)->toDateString();

        GuestHouseBooking::query()->create([
            'guest_house_id' => $house->id,
            'booking_reference' => 'GH-BLK-00001',
            'status' => GuestHouseBookingStatus::Confirmed,
            'guest_name' => 'Guest',
            'guest_email' => 'g@example.com',
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'nights' => 3,
            'guests_count' => 2,
            'base_total' => 15000,
            'total_amount' => 15000,
        ]);

        $service = app(GuestHouseAvailabilityService::class);
        $this->assertFalse($service->isAvailable($house, $checkIn, $checkOut));
    }

    public function test_cancelled_booking_frees_dates(): void
    {
        $house = $this->seedHouse();
        $checkIn = now()->addDays(5)->toDateString();
        $checkOut = now()->addDays(8)->toDateString();

        GuestHouseBooking::query()->create([
            'guest_house_id' => $house->id,
            'booking_reference' => 'GH-BLK-00002',
            'status' => GuestHouseBookingStatus::Cancelled,
            'guest_name' => 'Guest',
            'guest_email' => 'g@example.com',
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'nights' => 3,
            'guests_count' => 2,
            'base_total' => 15000,
            'total_amount' => 15000,
        ]);

        $service = app(GuestHouseAvailabilityService::class);
        $this->assertTrue($service->isAvailable($house, $checkIn, $checkOut));
    }
}

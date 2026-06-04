<?php

namespace Tests\Feature;

use App\Enums\GuestHouseAvailabilityBlockReason;
use App\Enums\GuestHouseBookingStatus;
use App\Enums\GuestHouseStatus;
use App\Enums\GuestHouseType;
use App\Models\Coupon;
use App\Models\GuestHouse;
use App\Models\GuestHouseAvailabilityBlock;
use App\Models\GuestHouseBooking;
use App\Models\GuestHouseSeasonalPrice;
use App\Models\Setting;
use App\Services\GuestHouseQuoteService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class GuestHouseQuoteServiceTest extends TestCase
{
    use RefreshDatabase;

    private function seedHouse(): GuestHouse
    {
        Setting::putValue('shop.currency', ['code' => 'EUR']);
        Setting::putValue('shop.default_tax', ['basis_points' => 0]);

        return GuestHouse::query()->create([
            'name' => 'Test Cottage',
            'slug' => 'test-cottage',
            'type' => GuestHouseType::Cottage,
            'status' => GuestHouseStatus::Active,
            'max_guests' => 4,
            'min_nights' => 2,
            'base_price_per_night' => 10000,
            'cleaning_fee' => 2000,
        ]);
    }

    public function test_quotes_correct_base_total_for_standard_nights(): void
    {
        $house = $this->seedHouse();
        $checkIn = now()->addDays(5)->toDateString();
        $checkOut = now()->addDays(8)->toDateString();

        $quote = app(GuestHouseQuoteService::class)->quote($house, $checkIn, $checkOut, 2);

        $this->assertSame(3, $quote['nights']);
        $this->assertSame(30000, $quote['base_total']);
        $this->assertSame(32000, $quote['total_amount']);
    }

    public function test_applies_seasonal_pricing_when_date_range_overlaps(): void
    {
        $house = $this->seedHouse();
        GuestHouseSeasonalPrice::query()->create([
            'guest_house_id' => $house->id,
            'name' => 'Peak',
            'date_from' => now()->addDays(5),
            'date_to' => now()->addDays(10),
            'price_per_night' => 15000,
        ]);

        $checkIn = now()->addDays(5)->toDateString();
        $checkOut = now()->addDays(8)->toDateString();

        $quote = app(GuestHouseQuoteService::class)->quote($house, $checkIn, $checkOut, 2);

        $this->assertSame(45000, $quote['base_total']);
    }

    public function test_rejects_booking_shorter_than_min_nights(): void
    {
        $house = $this->seedHouse();
        $this->expectException(InvalidArgumentException::class);
        app(GuestHouseQuoteService::class)->quote(
            $house,
            now()->addDays(5)->toDateString(),
            now()->addDays(6)->toDateString(),
            2,
        );
    }

    public function test_rejects_booking_on_blocked_dates(): void
    {
        $house = $this->seedHouse();
        GuestHouseAvailabilityBlock::query()->create([
            'guest_house_id' => $house->id,
            'blocked_from' => now()->addDays(6),
            'blocked_to' => now()->addDays(7),
            'reason' => GuestHouseAvailabilityBlockReason::Maintenance,
        ]);

        $this->expectException(InvalidArgumentException::class);
        app(GuestHouseQuoteService::class)->quote(
            $house,
            now()->addDays(5)->toDateString(),
            now()->addDays(9)->toDateString(),
            2,
        );
    }

    public function test_applies_coupon_discount_correctly(): void
    {
        $house = $this->seedHouse();
        Coupon::query()->create([
            'code' => 'STAY10',
            'type' => 'permanent',
            'discount_type' => 'fixed',
            'discount_fixed_cents' => 5000,
            'is_active' => true,
        ]);

        $quote = app(GuestHouseQuoteService::class)->quote(
            $house,
            now()->addDays(5)->toDateString(),
            now()->addDays(8)->toDateString(),
            2,
            'STAY10',
        );

        $this->assertSame(5000, $quote['discount_amount']);
        $this->assertSame(27000, $quote['total_amount']);
    }
}

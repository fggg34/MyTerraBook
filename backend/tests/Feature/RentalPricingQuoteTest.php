<?php

namespace Tests\Feature;

use App\Models\BookingRestriction;
use App\Models\Car;
use App\Models\Category;
use App\Models\DailyFare;
use App\Models\Location;
use App\Models\LocationFee;
use App\Models\PriceType;
use App\Models\Setting;
use App\Services\RentalQuoteService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RentalPricingQuoteTest extends TestCase
{
    use RefreshDatabase;

    protected function makeCatalog(): array
    {
        Setting::putValue('shop.currency', ['code' => 'EUR']);
        Setting::putValue('shop.default_tax', ['basis_points' => 0]);

        $category = Category::query()->create(['name' => 'Eco', 'is_active' => true]);
        $car = Car::query()->create([
            'category_id' => $category->id,
            'name' => 'Test Vehicle',
            'units_available' => 1,
            'is_active' => true,
        ]);
        $pickup = Location::query()->create(['name' => 'P1', 'is_active' => true]);
        $dropoff = Location::query()->create(['name' => 'D2', 'is_active' => true]);
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
            'price_per_day_cents' => 10000,
        ]);

        return [$car, $pickup, $dropoff, $priceType];
    }

    public function test_location_one_way_fee_added_after_discountable_subtotal(): void
    {
        [$car, $pickup, $dropoff, $priceType] = $this->makeCatalog();

        LocationFee::query()->create([
            'pickup_location_id' => $pickup->id,
            'dropoff_location_id' => $dropoff->id,
            'cost_cents' => 2500,
            'multiply_by_days' => false,
            'tax_rate_id' => null,
            'apply_inverted' => false,
            'day_overrides' => null,
            'is_one_way_fee' => true,
            'is_active' => true,
        ]);

        $svc = app(RentalQuoteService::class);
        $pickupAt = Carbon::parse('2026-05-10 10:00:00');
        $dropoffAt = Carbon::parse('2026-05-13 10:00:00');

        $quote = $svc->quote(
            $car,
            $priceType->id,
            $pickupAt,
            $dropoffAt,
            $pickup->id,
            $dropoff->id,
            [],
            null,
        );

        $this->assertSame(3, $quote['rental_days']);
        $this->assertSame(30000, $quote['base_rental_cents']);
        $this->assertSame(2500, $quote['fees_cents']);
        $this->assertSame(32500, $quote['total_cents']);
    }

    public function test_booking_restriction_rejects_short_rental(): void
    {
        [$car, $pickup, $dropoff, $priceType] = $this->makeCatalog();

        BookingRestriction::query()->create([
            'name' => 'Peak',
            'date_from' => '2026-05-01',
            'date_to' => '2026-05-31',
            'min_rental_days' => 5,
            'max_rental_days' => null,
            'cta_weekdays' => null,
            'ctd_weekdays' => null,
            'forced_pickup_weekdays' => null,
            'min_length_multiplier' => null,
            'is_active' => true,
        ]);

        $svc = app(RentalQuoteService::class);
        $pickupAt = Carbon::parse('2026-05-10 10:00:00');
        $dropoffAt = Carbon::parse('2026-05-12 10:00:00');

        $this->expectException(\InvalidArgumentException::class);
        $svc->quote(
            $car,
            $priceType->id,
            $pickupAt,
            $dropoffAt,
            $pickup->id,
            $dropoff->id,
            [],
            null,
        );
    }
}

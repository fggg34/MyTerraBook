<?php

namespace Tests\Feature;

use App\Models\BookingRestriction;
use App\Models\Car;
use App\Models\MainCategory;
use App\Models\SubCategory;
use App\Models\DailyFare;
use App\Models\ExtraHourFare;
use App\Models\HourlyFare;
use App\Models\Location;
use App\Models\LocationFee;
use App\Models\PriceType;
use App\Models\RentalOption;
use App\Models\Setting;
use App\Models\TaxRate;
use App\Models\User;
use App\Models\OutOfHoursFee;
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

        $main = MainCategory::query()->firstOrCreate(['slug' => 'car'], ['name' => 'Car', 'is_active' => true]);
        $category = SubCategory::query()->create(['main_category_id' => $main->id, 'name' => 'Eco', 'is_active' => true, 'is_search_filter' => true]);
        $car = Car::query()->create([
            'sub_category_id' => $category->id,
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

    public function test_hourly_fare_is_used_for_short_rental(): void
    {
        [$car, $pickup, $dropoff, $priceType] = $this->makeCatalog();
        HourlyFare::query()->create([
            'car_id' => $car->id,
            'price_type_id' => $priceType->id,
            'min_minutes' => 60,
            'max_minutes' => 300,
            'total_price_cents' => 3600,
        ]);

        $svc = app(RentalQuoteService::class);
        $quote = $svc->quote(
            $car,
            $priceType->id,
            Carbon::parse('2026-05-10 10:00:00'),
            Carbon::parse('2026-05-10 12:00:00'),
            $pickup->id,
            $dropoff->id,
            [],
            null,
        );

        $this->assertSame('hourly', $quote['pricing_mode']);
        $this->assertSame(3600, $quote['base_rental_cents']);
        $this->assertSame(3600, $quote['total_cents']);
    }

    public function test_short_rental_falls_back_to_daily_when_hourly_missing(): void
    {
        [$car, $pickup, $dropoff, $priceType] = $this->makeCatalog();

        $svc = app(RentalQuoteService::class);
        $quote = $svc->quote(
            $car,
            $priceType->id,
            Carbon::parse('2026-05-10 10:00:00'),
            Carbon::parse('2026-05-10 12:00:00'),
            $pickup->id,
            $dropoff->id,
            [],
            null,
        );

        $this->assertSame('hourly_fallback_to_daily', $quote['pricing_mode']);
        $this->assertSame(10000, $quote['base_rental_cents']);
        $this->assertSame(10000, $quote['total_cents']);
    }

    public function test_extra_hours_charge_is_applied_after_gratuity_period(): void
    {
        [$car, $pickup, $dropoff, $priceType] = $this->makeCatalog();
        Setting::putValue('shop.extended_gratuity_period', ['hours' => 2]);
        ExtraHourFare::query()->create([
            'car_id' => $car->id,
            'price_type_id' => $priceType->id,
            'charge_per_extra_hour_cents' => 700,
        ]);

        $svc = app(RentalQuoteService::class);
        $quote = $svc->quote(
            $car,
            $priceType->id,
            Carbon::parse('2026-05-10 10:00:00'),
            Carbon::parse('2026-05-12 13:30:00'),
            $pickup->id,
            $dropoff->id,
            [],
            null,
        );

        $this->assertSame('daily_plus_extra_hours', $quote['pricing_mode']);
        $this->assertSame(2, $quote['billable_days']);
        $this->assertSame(2, $quote['extra_hours_charged']);
        $this->assertSame(21400, $quote['base_rental_cents']);
    }

    public function test_inverted_location_fee_is_applied(): void
    {
        [$car, $pickup, $dropoff, $priceType] = $this->makeCatalog();
        LocationFee::query()->create([
            'pickup_location_id' => $dropoff->id,
            'dropoff_location_id' => $pickup->id,
            'cost_cents' => 1200,
            'multiply_by_days' => false,
            'tax_rate_id' => null,
            'apply_inverted' => true,
            'day_overrides' => null,
            'is_one_way_fee' => false,
            'is_active' => true,
        ]);

        $svc = app(RentalQuoteService::class);
        $quote = $svc->quote(
            $car,
            $priceType->id,
            Carbon::parse('2026-05-10 10:00:00'),
            Carbon::parse('2026-05-11 10:00:00'),
            $pickup->id,
            $dropoff->id,
            [],
            null,
        );

        $this->assertSame(1200, $quote['fees_cents']);
    }

    public function test_line_level_taxes_are_computed_with_rate_specific_taxes(): void
    {
        [$car, $pickup, $dropoff, $priceType] = $this->makeCatalog();

        $priceTax = TaxRate::query()->create(['name' => 'Price VAT', 'basis_points' => 1000]); // 10%
        $optionTax = TaxRate::query()->create(['name' => 'Option VAT', 'basis_points' => 2000]); // 20%
        $feeTax = TaxRate::query()->create(['name' => 'Fee VAT', 'basis_points' => 500]); // 5%
        $priceType->update(['tax_rate_id' => $priceTax->id]);

        $option = RentalOption::query()->create([
            'name' => 'GPS',
            'cost_cents' => 1000,
            'is_daily_cost' => false,
            'tax_rate_id' => $optionTax->id,
            'has_quantity' => false,
            'is_mandatory' => false,
            'is_active' => true,
        ]);
        $car->rentalOptions()->attach($option->id);

        LocationFee::query()->create([
            'pickup_location_id' => $pickup->id,
            'dropoff_location_id' => $dropoff->id,
            'cost_cents' => 2000,
            'multiply_by_days' => false,
            'tax_rate_id' => $feeTax->id,
            'apply_inverted' => false,
            'day_overrides' => null,
            'is_one_way_fee' => false,
            'is_active' => true,
        ]);

        $svc = app(RentalQuoteService::class);
        $quote = $svc->quote(
            $car,
            $priceType->id,
            Carbon::parse('2026-05-10 10:00:00'),
            Carbon::parse('2026-05-11 10:00:00'),
            $pickup->id,
            $dropoff->id,
            [(string) $option->id => 1],
            null,
        );

        // Base 10000*10% + option 1000*20% + fee 2000*5% = 1000 + 200 + 100 = 1300
        $this->assertSame(1300, $quote['tax_cents']);
        $this->assertSame(14300, $quote['total_cents']);
    }

    public function test_rental_options_list_format_is_accepted(): void
    {
        [$car, $pickup, $dropoff, $priceType] = $this->makeCatalog();

        $option = RentalOption::query()->create([
            'name' => 'Camping chairs',
            'cost_cents' => 1500,
            'is_daily_cost' => false,
            'has_quantity' => false,
            'is_mandatory' => false,
            'is_active' => true,
        ]);
        $car->rentalOptions()->attach($option->id);

        $svc = app(RentalQuoteService::class);
        $quote = $svc->quote(
            $car,
            $priceType->id,
            Carbon::parse('2026-05-10 10:00:00'),
            Carbon::parse('2026-05-11 10:00:00'),
            $pickup->id,
            $dropoff->id,
            [$option->id],
            null,
        );

        $this->assertSame(1500, $quote['extras_cents']);
        $this->assertSame(11500, $quote['total_cents']);
    }

    public function test_host_owned_car_uses_only_host_scoped_fees(): void
    {
        [$platformCar, $pickup, $dropoff, $priceType] = $this->makeCatalog();

        $host = User::factory()->host()->create();
        $hostCar = Car::query()->create([
            'user_id' => $host->id,
            'sub_category_id' => $platformCar->sub_category_id,
            'name' => 'Host Vehicle',
            'units_available' => 1,
            'is_active' => true,
        ]);
        $hostCar->locations()->attach([
            $pickup->id => ['allows_pickup' => true, 'allows_dropoff' => true],
            $dropoff->id => ['allows_pickup' => true, 'allows_dropoff' => true],
        ]);
        DailyFare::query()->create([
            'car_id' => $hostCar->id,
            'price_type_id' => $priceType->id,
            'from_days' => 1,
            'to_days' => 30,
            'price_per_day_cents' => 10000,
        ]);

        LocationFee::query()->create([
            'pickup_location_id' => $pickup->id,
            'dropoff_location_id' => $dropoff->id,
            'cost_cents' => 5000,
            'is_active' => true,
        ]);

        LocationFee::query()->create([
            'car_id' => $hostCar->id,
            'pickup_location_id' => $pickup->id,
            'dropoff_location_id' => $dropoff->id,
            'cost_cents' => 1500,
            'is_active' => true,
        ]);

        OutOfHoursFee::query()->create([
            'name' => 'Global OOH',
            'time_from' => '20:00:00',
            'time_to' => '08:00:00',
            'applies_to' => 'pickup',
            'cost_cents' => 3500,
            'pickup_cost_cents' => 3500,
            'dropoff_cost_cents' => 0,
            'is_active' => true,
        ]);

        OutOfHoursFee::query()->create([
            'name' => 'Host OOH',
            'time_from' => '20:00:00',
            'time_to' => '08:00:00',
            'applies_to' => 'pickup',
            'cost_cents' => 2000,
            'pickup_cost_cents' => 2000,
            'dropoff_cost_cents' => 0,
            'vehicle_ids' => [$hostCar->id],
            'is_active' => true,
        ]);

        $svc = app(RentalQuoteService::class);
        $inHoursPickup = Carbon::parse('2026-05-10 10:00:00');
        $dropoffAt = Carbon::parse('2026-05-11 10:00:00');

        $hostQuote = $svc->quote(
            $hostCar,
            $priceType->id,
            $inHoursPickup,
            $dropoffAt,
            $pickup->id,
            $dropoff->id,
            [],
            null,
        );

        $platformQuote = $svc->quote(
            $platformCar,
            $priceType->id,
            $inHoursPickup,
            $dropoffAt,
            $pickup->id,
            $dropoff->id,
            [],
            null,
        );

        $this->assertSame(1500, $hostQuote['fees_cents']);
        $this->assertSame(5000, $platformQuote['fees_cents']);

        $oohPickupAt = Carbon::parse('2026-05-10 21:00:00');
        $hostOohQuote = $svc->quote(
            $hostCar,
            $priceType->id,
            $oohPickupAt,
            $dropoffAt,
            $pickup->id,
            $dropoff->id,
            [],
            null,
        );
        $platformOohQuote = $svc->quote(
            $platformCar,
            $priceType->id,
            $oohPickupAt,
            $dropoffAt,
            $pickup->id,
            $dropoff->id,
            [],
            null,
        );

        $this->assertSame(2000, collect($hostOohQuote['fees_lines'])->where('kind', 'out_of_hours_pickup')->sum('amount_cents'));
        $this->assertSame(3500, collect($platformOohQuote['fees_lines'])->where('kind', 'out_of_hours_pickup')->sum('amount_cents'));
    }
}

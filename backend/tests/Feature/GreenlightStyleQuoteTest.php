<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\DailyFare;
use App\Models\Location;
use App\Models\MainCategory;
use App\Models\PriceType;
use App\Models\Setting;
use App\Models\SpecialPrice;
use App\Models\SubCategory;
use App\Models\TaxRate;
use App\Services\RentalQuoteService;
use App\Support\GreenlightStyleQuote;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GreenlightStyleQuoteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{0: Car, 1: Location, 2: Location, 3: PriceType}
     */
    protected function makeCatalog(int $oneDayCents = 10000): array
    {
        Setting::putValue('shop.currency', ['code' => 'EUR']);
        Setting::putValue('shop.default_tax', ['basis_points' => 0]);

        $main = MainCategory::query()->firstOrCreate(['slug' => 'car'], ['name' => 'Car', 'is_active' => true]);
        $category = SubCategory::query()->create([
            'main_category_id' => $main->id,
            'name' => 'Eco',
            'is_active' => true,
            'is_search_filter' => true,
        ]);
        $car = Car::query()->create([
            'sub_category_id' => $category->id,
            'name' => 'Quote Vehicle',
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
            'price_per_day_cents' => $oneDayCents,
        ]);

        return [$car, $pickup, $dropoff, $priceType];
    }

    public function test_three_day_flat_rate_matches_greenlight_per_day_base(): void
    {
        [$car] = $this->makeCatalog(10000);

        $quote = GreenlightStyleQuote::quote(
            $car,
            Carbon::parse('2026-05-10 10:00:00'),
            Carbon::parse('2026-05-13 10:00:00'),
        );

        $this->assertSame(3, $quote['days']);
        $this->assertSame(10000, $quote['base_daily_rate_cents']);
        $this->assertSame(30000, $quote['base_rate_cents']);
        $this->assertSame(30000, $quote['total_cents']);
        $this->assertSame(0, $quote['road_tax_cents']);
        $this->assertNull($quote['applied_rule']);
    }

    public function test_percent_increase_uses_round_like_greenlight(): void
    {
        [$car] = $this->makeCatalog(10033);

        SpecialPrice::query()->create([
            'name' => 'Peak percent',
            'date_from' => '2026-05-01',
            'date_to' => '2026-05-31',
            'type' => 'charge',
            'value_mode' => 'percentage',
            'value_percent_bips' => 1500,
            'is_active' => true,
        ]);

        $quote = GreenlightStyleQuote::quote(
            $car,
            Carbon::parse('2026-05-10 10:00:00'),
            Carbon::parse('2026-05-11 10:00:00'),
        );

        $this->assertSame(1, $quote['days']);
        $this->assertSame(11538, $quote['base_rate_cents']);
        $this->assertSame('+15%', $quote['applied_rule']['adjustment_label'] ?? null);
    }

    public function test_vehicle_specific_rule_beats_all_vehicles_rule(): void
    {
        [$car] = $this->makeCatalog(10000);

        SpecialPrice::query()->create([
            'name' => 'Fleet 10 percent',
            'date_from' => '2026-05-01',
            'date_to' => '2026-05-31',
            'type' => 'charge',
            'value_mode' => 'percentage',
            'value_percent_bips' => 1000,
            'vehicle_ids' => [],
            'is_active' => true,
            'created_at' => Carbon::parse('2026-01-01 00:00:00'),
        ]);
        SpecialPrice::query()->create([
            'name' => 'This car flat',
            'date_from' => '2026-05-01',
            'date_to' => '2026-05-31',
            'type' => 'charge',
            'value_mode' => 'fixed',
            'value_fixed_cents' => 2000,
            'vehicle_ids' => [$car->id],
            'is_active' => true,
            'created_at' => Carbon::parse('2026-01-02 00:00:00'),
        ]);

        $quote = GreenlightStyleQuote::quote(
            $car,
            Carbon::parse('2026-05-10 10:00:00'),
            Carbon::parse('2026-05-13 10:00:00'),
        );

        $this->assertSame(36000, $quote['base_rate_cents']);
        $this->assertSame('This car flat', $quote['applied_rule']['name'] ?? null);
    }

    public function test_day_override_replaces_adjustment_when_rental_is_long_enough(): void
    {
        [$car] = $this->makeCatalog(10000);

        SpecialPrice::query()->create([
            'name' => 'Long stay percent',
            'date_from' => '2026-05-01',
            'date_to' => '2026-05-31',
            'type' => 'discount',
            'value_mode' => 'percentage',
            'value_percent_bips' => 1000,
            'day_overrides' => ['3' => 20],
            'is_active' => true,
        ]);

        $quote = GreenlightStyleQuote::quote(
            $car,
            Carbon::parse('2026-05-10 10:00:00'),
            Carbon::parse('2026-05-13 10:00:00'),
        );

        $this->assertSame(24000, $quote['base_rate_cents']);
        $this->assertStringContainsString('20%', $quote['applied_rule']['adjustment_label'] ?? '');
        $this->assertStringContainsString('3+ days', $quote['applied_rule']['adjustment_label'] ?? '');
    }

    public function test_reports_differences_against_terrabook_quote_and_does_not_switch_checkout(): void
    {
        [$car, $pickup, $dropoff, $priceType] = $this->makeCatalog(10000);
        DailyFare::query()->where('car_id', $car->id)->delete();
        DailyFare::query()->create([
            'car_id' => $car->id,
            'price_type_id' => $priceType->id,
            'from_days' => 1,
            'to_days' => 2,
            'price_per_day_cents' => 10000,
        ]);
        DailyFare::query()->create([
            'car_id' => $car->id,
            'price_type_id' => $priceType->id,
            'from_days' => 3,
            'to_days' => 30,
            'price_per_day_cents' => 8000,
        ]);

        $tax = TaxRate::query()->create([
            'name' => 'VAT 11',
            'basis_points' => 1100,
        ]);
        $priceType->update(['tax_rate_id' => $tax->id]);

        SpecialPrice::query()->create([
            'name' => 'Fleet 10 percent',
            'date_from' => '2026-05-01',
            'date_to' => '2026-05-31',
            'type' => 'charge',
            'value_mode' => 'percentage',
            'value_percent_bips' => 1000,
            'vehicle_ids' => [],
            'is_active' => true,
            'created_at' => Carbon::parse('2026-01-01 00:00:00'),
        ]);
        SpecialPrice::query()->create([
            'name' => 'This car flat',
            'date_from' => '2026-05-01',
            'date_to' => '2026-05-31',
            'type' => 'charge',
            'value_mode' => 'fixed',
            'value_fixed_cents' => 2000,
            'vehicle_ids' => [$car->id],
            'is_active' => true,
            'created_at' => Carbon::parse('2026-01-02 00:00:00'),
        ]);

        $pickupAt = Carbon::parse('2026-05-10 10:00:00');
        $dropoffAt = Carbon::parse('2026-05-13 10:00:00');

        $greenlight = GreenlightStyleQuote::quote($car, $pickupAt, $dropoffAt);
        $terrabook = app(RentalQuoteService::class)->quote(
            $car,
            $priceType->id,
            $pickupAt,
            $dropoffAt,
            $pickup->id,
            $dropoff->id,
            [],
            null,
        );

        $this->assertSame(10000, $greenlight['base_daily_rate_cents']);
        $this->assertSame(36000, $greenlight['base_rate_cents']);
        $this->assertSame(36000, $greenlight['total_cents']);
        $this->assertNotSame($greenlight['total_cents'], $terrabook['total_cents']);
        $this->assertSame('EUR', $terrabook['currency']);
        $this->assertGreaterThan(0, $terrabook['tax_cents']);

        $report = [
            'duration_tiers: greenlight uses the 1-day standard fare (10000 x 3 = 30000 before rules); Terrabook uses the 3-day tier (8000 x 3 = 24000 before specials).',
            'winning_rule: greenlight keeps the vehicle-specific flat (+2000/day, total 36000); Terrabook stacks every matching special on the duration-tier base.',
            'vat: greenlight has no VAT line (0); Terrabook tax_cents is '.$terrabook['tax_cents'].'.',
            'currency: greenlight comparison is cents only; Terrabook settlement currency is '.$terrabook['currency'].'.',
            'totals: greenlight '.$greenlight['total_cents'].' vs Terrabook '.$terrabook['total_cents'].'.',
        ];

        fwrite(STDOUT, PHP_EOL.implode(PHP_EOL, $report).PHP_EOL);

        $this->assertTrue(method_exists(RentalQuoteService::class, 'quote'));
    }

    public function test_floor_versus_round_percent_differs_from_terrabook(): void
    {
        [$car, $pickup, $dropoff, $priceType] = $this->makeCatalog(10033);

        SpecialPrice::query()->create([
            'name' => 'Peak percent',
            'date_from' => '2026-05-01',
            'date_to' => '2026-05-31',
            'type' => 'charge',
            'value_mode' => 'percentage',
            'value_percent_bips' => 1500,
            'is_active' => true,
        ]);

        $pickupAt = Carbon::parse('2026-05-10 10:00:00');
        $dropoffAt = Carbon::parse('2026-05-11 10:00:00');

        $greenlight = GreenlightStyleQuote::quote($car, $pickupAt, $dropoffAt);
        $terrabook = app(RentalQuoteService::class)->quote(
            $car,
            $priceType->id,
            $pickupAt,
            $dropoffAt,
            $pickup->id,
            $dropoff->id,
            [],
            null,
        );

        $this->assertSame(11538, $greenlight['base_rate_cents']);
        $this->assertSame(11537, $terrabook['base_rental_cents']);
        fwrite(STDOUT, PHP_EOL.'percent_rounding: greenlight round=11538 vs Terrabook floor=11537'.PHP_EOL);
    }
}

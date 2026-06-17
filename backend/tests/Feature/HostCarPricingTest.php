<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\DailyFare;
use App\Models\MainCategory;
use App\Models\PriceType;
use App\Models\RentalOption;
use App\Models\SubCategory;
use App\Models\User;
use App\Services\RentalQuoteService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class HostCarPricingTest extends TestCase
{
    use RefreshDatabase;

    private function hostWithCar(): array
    {
        $main = MainCategory::query()->firstOrCreate(['slug' => 'car'], ['name' => 'Car', 'is_active' => true]);
        $category = SubCategory::query()->create([
            'main_category_id' => $main->id,
            'name' => 'SUV',
            'is_active' => true,
            'is_search_filter' => true,
        ]);
        $basic = PriceType::query()->create(['name' => 'Basic', 'slug' => 'basic', 'is_active' => true]);
        $plus = PriceType::query()->create(['name' => 'Plus', 'slug' => 'plus', 'is_active' => true]);
        $max = PriceType::query()->create(['name' => 'Max', 'slug' => 'max', 'is_active' => true]);
        $host = User::factory()->host()->create();
        $car = Car::query()->create([
            'user_id' => $host->id,
            'name' => 'Pricing Test Car',
            'sub_category_id' => $category->id,
            'listing_status' => \App\Enums\ListingApprovalStatus::Draft,
            'units_available' => 1,
            'seats' => 5,
            'bags' => 2,
        ]);
        $car->carUnits()->create(['is_active' => true, 'sort_order' => 0]);

        Sanctum::actingAs($host);

        return [$car, $basic, $plus, $max];
    }

    public function test_host_can_create_and_update_base_daily_fare_in_euros(): void
    {
        [$car, $basic] = $this->hostWithCar();

        $create = $this->postJson("/api/host/cars/{$car->id}/daily-fares", [
            'price_type_id' => $basic->id,
            'from_days' => 1,
            'to_days' => 365,
            'price_per_day_euros' => 89.50,
        ])->assertCreated();

        $fareId = $create->json('data.id');
        $this->assertSame(8950, $create->json('data.price_per_day_cents'));

        $this->getJson("/api/host/cars/{$car->id}/daily-fares")
            ->assertOk()
            ->assertJsonPath('data.0.price_per_day_cents', 8950);

        $this->patchJson("/api/host/cars/{$car->id}/daily-fares/{$fareId}", [
            'price_per_day_euros' => 95,
        ])->assertOk()
            ->assertJsonPath('data.price_per_day_cents', 9500);
    }

    public function test_duration_tier_applies_in_quote(): void
    {
        [$car, $basic] = $this->hostWithCar();
        $pickup = \App\Models\Location::query()->create(['name' => 'P1', 'slug' => 'p1', 'is_active' => true]);
        $dropoff = \App\Models\Location::query()->create(['name' => 'D1', 'slug' => 'd1', 'is_active' => true]);
        $car->locations()->attach([
            $pickup->id => ['allows_pickup' => true, 'allows_dropoff' => true],
            $dropoff->id => ['allows_pickup' => true, 'allows_dropoff' => true],
        ]);

        $this->postJson("/api/host/cars/{$car->id}/daily-fares", [
            'price_type_id' => $basic->id,
            'from_days' => 1,
            'to_days' => 365,
            'price_per_day_euros' => 100,
        ])->assertCreated();

        $this->postJson("/api/host/cars/{$car->id}/daily-fares", [
            'price_type_id' => $basic->id,
            'from_days' => 7,
            'to_days' => 14,
            'price_per_day_euros' => 80,
        ])->assertCreated();

        $svc = app(RentalQuoteService::class);
        $quote = $svc->quote(
            $car->fresh(),
            $basic->id,
            Carbon::parse('2026-06-01 10:00'),
            Carbon::parse('2026-06-11 10:00'),
            $pickup->id,
            $dropoff->id,
            [],
            null,
        );

        $this->assertSame(10, $quote['rental_days']);
        $this->assertSame(8000, $quote['base_rental_cents'] / 10);
    }

    public function test_protection_tier_daily_fares_apply_in_quote(): void
    {
        [$car, $basic, $plus] = $this->hostWithCar();
        $pickup = \App\Models\Location::query()->create(['name' => 'P2', 'slug' => 'p2', 'is_active' => true]);
        $dropoff = \App\Models\Location::query()->create(['name' => 'D2', 'slug' => 'd2', 'is_active' => true]);
        $car->locations()->attach([
            $pickup->id => ['allows_pickup' => true, 'allows_dropoff' => true],
            $dropoff->id => ['allows_pickup' => true, 'allows_dropoff' => true],
        ]);

        $this->postJson("/api/host/cars/{$car->id}/daily-fares", [
            'price_type_id' => $basic->id,
            'from_days' => 1,
            'to_days' => 365,
            'price_per_day_euros' => 100,
        ])->assertCreated();

        $this->postJson("/api/host/cars/{$car->id}/daily-fares", [
            'price_type_id' => $plus->id,
            'from_days' => 1,
            'to_days' => 365,
            'price_per_day_euros' => 115,
        ])->assertCreated();

        $svc = app(RentalQuoteService::class);
        $quote = $svc->quote(
            $car->fresh(),
            $plus->id,
            Carbon::parse('2026-06-01 10:00'),
            Carbon::parse('2026-06-04 10:00'),
            $pickup->id,
            $dropoff->id,
            [],
            null,
        );

        $this->assertSame(3, $quote['rental_days']);
        $this->assertSame(34500, $quote['base_rental_cents']);
    }

    public function test_submit_requires_base_daily_fare_one_to_three_sixty_five(): void
    {
        [$car, $basic] = $this->hostWithCar();
        $pickup = \App\Models\Location::query()->create(['name' => 'Airport', 'slug' => 'airport-base-fare', 'is_active' => true]);
        $dropoff = \App\Models\Location::query()->create(['name' => 'City', 'slug' => 'city-base-fare', 'is_active' => true]);
        $car->locations()->attach([
            $pickup->id => ['allows_pickup' => true, 'allows_dropoff' => true],
            $dropoff->id => ['allows_pickup' => true, 'allows_dropoff' => true],
        ]);
        $car->update([
            'listing_status' => \App\Enums\ListingApprovalStatus::Draft,
            'transmission' => 'automatic',
            'fuel_type' => 'petrol',
            'drive_type' => 'fwd',
            'bags' => 2,
            'pickup_time_from' => '09:00',
            'pickup_time_to' => '17:00',
            'dropoff_time_from' => '09:00',
            'dropoff_time_to' => '17:00',
        ]);

        DailyFare::query()->create([
            'car_id' => $car->id,
            'price_type_id' => $basic->id,
            'from_days' => 7,
            'to_days' => 14,
            'price_per_day_cents' => 8000,
        ]);

        $this->postJson("/api/host/cars/{$car->id}/submit")
            ->assertUnprocessable()
            ->assertJsonPath(
                'message',
                'A base daily rental rate (1–365 days) is required before submitting for review.',
            );
    }

    public function test_host_can_patch_hourly_extra_hour_special_location_and_ooh_fees(): void
    {
        [$car, $basic] = $this->hostWithCar();

        $pickup = \App\Models\Location::query()->create(['name' => 'Airport', 'slug' => 'airport', 'is_active' => true]);
        $dropoff = \App\Models\Location::query()->create(['name' => 'City', 'slug' => 'city', 'is_active' => true]);
        $car->locations()->attach([
            $pickup->id => ['allows_pickup' => true, 'allows_dropoff' => true],
            $dropoff->id => ['allows_pickup' => true, 'allows_dropoff' => true],
        ]);

        $hourly = $this->postJson("/api/host/cars/{$car->id}/hourly-fares", [
            'price_type_id' => $basic->id,
            'min_minutes' => 60,
            'max_minutes' => 240,
            'total_price_euros' => 50,
        ])->assertCreated()->json('data.id');

        $this->patchJson("/api/host/cars/{$car->id}/hourly-fares/{$hourly}", [
            'total_price_euros' => 60,
        ])->assertOk()->assertJsonPath('data.total_price_cents', 6000);

        $extra = $this->postJson("/api/host/cars/{$car->id}/extra-hour-fares", [
            'price_type_id' => $basic->id,
            'charge_per_extra_hour_euros' => 10,
        ])->assertCreated()->json('data.id');

        $this->patchJson("/api/host/cars/{$car->id}/extra-hour-fares/{$extra}", [
            'charge_per_extra_hour_euros' => 15,
        ])->assertOk()->assertJsonPath('data.charge_per_extra_hour_cents', 1500);

        $special = $this->postJson("/api/host/cars/{$car->id}/special-prices", [
            'name' => 'Summer',
            'date_from' => '2026-07-01',
            'date_to' => '2026-08-31',
            'type' => 'charge',
            'value_mode' => 'percentage',
            'value_percent_bips' => 1000,
            'value_fixed_cents' => null,
        ])->assertCreated()->json('data.id');

        $this->patchJson("/api/host/cars/{$car->id}/special-prices/{$special}", [
            'name' => 'Peak summer',
            'value_percent_bips' => 1500,
        ])->assertOk()->assertJsonPath('data.name', 'Peak summer');

        $locationFee = $this->postJson("/api/host/cars/{$car->id}/location-fees", [
            'pickup_location_id' => $pickup->id,
            'dropoff_location_id' => $dropoff->id,
            'cost_euros' => 25,
            'multiply_by_days' => false,
            'is_one_way_fee' => false,
        ])->assertCreated()->json('data.id');

        $this->patchJson("/api/host/cars/{$car->id}/location-fees/{$locationFee}", [
            'cost_euros' => 30,
            'multiply_by_days' => true,
        ])->assertOk()
            ->assertJsonPath('data.cost_cents', 3000)
            ->assertJsonPath('data.multiply_by_days', true);

        $ooh = $this->postJson("/api/host/cars/{$car->id}/out-of-hours-fees", [
            'name' => 'Late pickup',
            'time_from' => '20:00',
            'time_to' => '08:00',
            'applies_to' => 'pickup',
            'pickup_cost_euros' => 35,
            'dropoff_cost_euros' => 0,
        ])->assertCreated()->json('data.id');

        $this->patchJson("/api/host/cars/{$car->id}/out-of-hours-fees/{$ooh}", [
            'pickup_cost_euros' => 40,
        ])->assertOk()->assertJsonPath('data.pickup_cost_cents', 4000);
    }

    public function test_host_car_quote_uses_host_account_currency(): void
    {
        [$car, $basic] = $this->hostWithCar();
        $car->host->update(['currency' => 'ISK']);
        $pickup = \App\Models\Location::query()->create(['name' => 'P3', 'slug' => 'p3', 'is_active' => true]);
        $dropoff = \App\Models\Location::query()->create(['name' => 'D3', 'slug' => 'd3', 'is_active' => true]);
        $car->locations()->attach([
            $pickup->id => ['allows_pickup' => true, 'allows_dropoff' => true],
            $dropoff->id => ['allows_pickup' => true, 'allows_dropoff' => true],
        ]);

        $this->postJson("/api/host/cars/{$car->id}/daily-fares", [
            'price_type_id' => $basic->id,
            'from_days' => 1,
            'to_days' => 365,
            'price_per_day_euros' => 100,
        ])->assertCreated();

        $svc = app(\App\Services\RentalQuoteService::class);
        $quote = $svc->quote(
            $car->fresh(),
            $basic->id,
            Carbon::parse('2026-06-01 10:00'),
            Carbon::parse('2026-06-04 10:00'),
            $pickup->id,
            $dropoff->id,
            [],
            null,
        );

        $this->assertSame('ISK', $quote['currency']);
    }

    public function test_host_can_sync_rental_option_with_custom_price(): void
    {
        [$car] = $this->hostWithCar();
        $option = RentalOption::factory()->create([
            'name' => 'Wi-Fi Hotspot',
            'cost_cents' => 150000,
            'is_daily_cost' => true,
        ]);

        $this->patchJson("/api/host/cars/{$car->id}/relations", [
            'rental_options' => [
                ['id' => $option->id, 'cost_euros' => 12.50],
            ],
        ])->assertOk()
            ->assertJsonPath('data.rental_options.0.id', $option->id)
            ->assertJsonPath('data.rental_options.0.cost_cents', 1250);

        $this->assertDatabaseHas('car_rental_option', [
            'car_id' => $car->id,
            'rental_option_id' => $option->id,
            'cost_cents' => 1250,
        ]);
    }

    public function test_host_can_sync_rental_option_with_flat_pricing_override(): void
    {
        [$car] = $this->hostWithCar();
        $option = RentalOption::factory()->create([
            'name' => 'Camping chairs',
            'cost_cents' => 350000,
            'is_daily_cost' => true,
        ]);

        $this->patchJson("/api/host/cars/{$car->id}/relations", [
            'rental_options' => [
                ['id' => $option->id, 'cost_euros' => 35, 'is_daily_cost' => false],
            ],
        ])->assertOk()
            ->assertJsonPath('data.rental_options.0.id', $option->id)
            ->assertJsonPath('data.rental_options.0.cost_cents', 3500)
            ->assertJsonPath('data.rental_options.0.is_daily_cost', false);

        $this->assertDatabaseHas('car_rental_option', [
            'car_id' => $car->id,
            'rental_option_id' => $option->id,
            'cost_cents' => 3500,
            'is_daily_cost' => false,
        ]);
    }

    public function test_quote_uses_host_rental_option_pivot_price(): void
    {
        [$car, $basic] = $this->hostWithCar();
        $pickup = \App\Models\Location::query()->create(['name' => 'P4', 'slug' => 'p4', 'is_active' => true]);
        $dropoff = \App\Models\Location::query()->create(['name' => 'D4', 'slug' => 'd4', 'is_active' => true]);
        $car->locations()->attach([
            $pickup->id => ['allows_pickup' => true, 'allows_dropoff' => true],
            $dropoff->id => ['allows_pickup' => true, 'allows_dropoff' => true],
        ]);

        $option = RentalOption::factory()->create([
            'name' => 'Child Seat',
            'cost_cents' => 90000,
            'is_daily_cost' => true,
        ]);
        $car->rentalOptions()->sync([
            $option->id => ['cost_cents' => 500],
        ]);

        $this->postJson("/api/host/cars/{$car->id}/daily-fares", [
            'price_type_id' => $basic->id,
            'from_days' => 1,
            'to_days' => 365,
            'price_per_day_euros' => 80,
        ])->assertCreated();

        $svc = app(RentalQuoteService::class);
        $quote = $svc->quote(
            $car->fresh(),
            $basic->id,
            Carbon::parse('2026-06-01 10:00'),
            Carbon::parse('2026-06-03 10:00'),
            $pickup->id,
            $dropoff->id,
            [(string) $option->id => 1],
            null,
        );

        $this->assertSame(1000, $quote['extras_cents']);
        $this->assertSame(500, $quote['extras_lines'][0]['unit_price_cents']);
    }

    public function test_quote_falls_back_to_catalog_default_when_pivot_price_null(): void
    {
        [$car, $basic] = $this->hostWithCar();
        $pickup = \App\Models\Location::query()->create(['name' => 'P5', 'slug' => 'p5', 'is_active' => true]);
        $dropoff = \App\Models\Location::query()->create(['name' => 'D5', 'slug' => 'd5', 'is_active' => true]);
        $car->locations()->attach([
            $pickup->id => ['allows_pickup' => true, 'allows_dropoff' => true],
            $dropoff->id => ['allows_pickup' => true, 'allows_dropoff' => true],
        ]);

        $option = RentalOption::factory()->create([
            'name' => 'GPS Device',
            'cost_cents' => 130000,
            'is_daily_cost' => false,
        ]);
        $car->rentalOptions()->attach($option->id);

        $this->postJson("/api/host/cars/{$car->id}/daily-fares", [
            'price_type_id' => $basic->id,
            'from_days' => 1,
            'to_days' => 365,
            'price_per_day_euros' => 80,
        ])->assertCreated();

        $svc = app(RentalQuoteService::class);
        $quote = $svc->quote(
            $car->fresh(),
            $basic->id,
            Carbon::parse('2026-06-01 10:00'),
            Carbon::parse('2026-06-02 10:00'),
            $pickup->id,
            $dropoff->id,
            [(string) $option->id => 1],
            null,
        );

        $this->assertSame(130000, $quote['extras_cents']);
        $this->assertSame(130000, $quote['extras_lines'][0]['unit_price_cents']);
    }
}

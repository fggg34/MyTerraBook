<?php

namespace Tests\Feature;

use App\Enums\ListingApprovalStatus;
use App\Models\Car;
use App\Models\Location;
use App\Models\MainCategory;
use App\Models\PriceType;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListingPricingTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: Car, 1: PriceType, 2: Location, 3: Location} */
    private function publicCarWithTieredPricing(): array
    {
        $main = MainCategory::query()->firstOrCreate(['slug' => 'campervan'], ['name' => 'Campervan', 'is_active' => true]);
        $category = SubCategory::query()->create([
            'main_category_id' => $main->id,
            'name' => '4x4 Camper',
            'is_active' => true,
            'is_search_filter' => true,
        ]);
        $basic = PriceType::query()->create(['name' => 'Basic', 'slug' => 'basic', 'is_active' => true]);
        $host = User::factory()->host()->create();
        $pickup = Location::query()->create(['name' => 'Reykjavik', 'slug' => 'reykjavik', 'is_active' => true]);
        $dropoff = Location::query()->create(['name' => 'Keflavik', 'slug' => 'keflavik', 'is_active' => true]);

        $car = Car::query()->create([
            'user_id' => $host->id,
            'name' => 'Thor Dacia Duster Roof Tent 4x4',
            'sub_category_id' => $category->id,
            'listing_status' => ListingApprovalStatus::Approved,
            'is_active' => true,
            'units_available' => 1,
            'seats' => 5,
            'bags' => 2,
            'pickup_time_from' => '09:00',
            'pickup_time_to' => '17:00',
            'dropoff_time_from' => '10:00',
            'dropoff_time_to' => '17:00',
        ]);
        $car->carUnits()->create(['is_active' => true, 'sort_order' => 0]);
        $car->locations()->attach([
            $pickup->id => ['allows_pickup' => true, 'allows_dropoff' => true],
            $dropoff->id => ['allows_pickup' => true, 'allows_dropoff' => true],
        ]);

        $tiers = [
            ['from_days' => 1, 'to_days' => 365, 'price_per_day_cents' => 350000],
            ['from_days' => 3, 'to_days' => 7, 'price_per_day_cents' => 300000],
            ['from_days' => 7, 'to_days' => 14, 'price_per_day_cents' => 250000],
            ['from_days' => 14, 'to_days' => 21, 'price_per_day_cents' => 200000],
        ];

        foreach ($tiers as $tier) {
            $car->dailyFares()->create([
                'price_type_id' => $basic->id,
                ...$tier,
            ]);
        }

        return [$car->fresh(), $basic, $pickup, $dropoff];
    }

    public function test_car_detail_from_price_uses_base_fare_not_cheapest_tier(): void
    {
        [$car] = $this->publicCarWithTieredPricing();

        $this->getJson("/api/cars/{$car->id}")
            ->assertOk()
            ->assertJsonPath('data.price_types.0.from_price_per_day_cents', 350000);
    }

    public function test_quote_uses_base_rate_for_one_and_two_day_rentals(): void
    {
        [$car, $basic, $pickup, $dropoff] = $this->publicCarWithTieredPricing();

        $oneDay = $this->postJson('/api/orders/quote', [
            'car_id' => $car->id,
            'price_type_id' => $basic->id,
            'pickup_location_id' => $pickup->id,
            'dropoff_location_id' => $dropoff->id,
            'pickup_at' => '2026-06-17 09:00:00',
            'dropoff_at' => '2026-06-18 09:00:00',
        ])->assertOk();

        $this->assertSame(350000, $oneDay->json('base_rental_cents'));

        $twoDay = $this->postJson('/api/orders/quote', [
            'car_id' => $car->id,
            'price_type_id' => $basic->id,
            'pickup_location_id' => $pickup->id,
            'dropoff_location_id' => $dropoff->id,
            'pickup_at' => '2026-06-17 09:00:00',
            'dropoff_at' => '2026-06-19 09:00:00',
        ])->assertOk();

        $this->assertSame(700000, $twoDay->json('base_rental_cents'));
    }

    public function test_quote_uses_duration_tier_for_five_day_rental(): void
    {
        [$car, $basic, $pickup, $dropoff] = $this->publicCarWithTieredPricing();

        $response = $this->postJson('/api/orders/quote', [
            'car_id' => $car->id,
            'price_type_id' => $basic->id,
            'pickup_location_id' => $pickup->id,
            'dropoff_location_id' => $dropoff->id,
            'pickup_at' => '2026-06-17 09:00:00',
            'dropoff_at' => '2026-06-22 09:00:00',
        ])->assertOk();

        $this->assertSame(5, $response->json('rental_days'));
        $this->assertSame(1500000, $response->json('base_rental_cents'));
    }
}

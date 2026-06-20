<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\DailyFare;
use App\Models\Location;
use App\Models\LocationFee;
use App\Models\MainCategory;
use App\Models\OutOfHoursFee;
use App\Models\PriceType;
use App\Models\RentalOption;
use App\Models\SpecialPrice;
use App\Models\SubCategory;
use App\Services\CarDuplicationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarDuplicationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_copies_pricing_and_creates_multiple_copies(): void
    {
        $main = MainCategory::query()->firstOrCreate(['slug' => 'car'], ['name' => 'Car', 'is_active' => true]);
        $category = SubCategory::query()->create([
            'main_category_id' => $main->id,
            'name' => 'SUV',
            'is_active' => true,
            'is_search_filter' => true,
        ]);
        $priceType = PriceType::query()->create(['name' => 'Basic', 'slug' => 'basic', 'is_active' => true]);
        $option = RentalOption::factory()->create();

        $car = Car::query()->create([
            'name' => 'Source Car',
            'sub_category_id' => $category->id,
            'units_available' => 1,
            'seats' => 5,
            'bags' => 2,
        ]);

        DailyFare::query()->create([
            'car_id' => $car->id,
            'price_type_id' => $priceType->id,
            'from_days' => 1,
            'to_days' => 365,
            'price_per_day_cents' => 8900,
        ]);

        $pickup = Location::query()->create(['name' => 'Pickup', 'slug' => 'pickup', 'is_active' => true]);
        $dropoff = Location::query()->create(['name' => 'Dropoff', 'slug' => 'dropoff', 'is_active' => true]);

        LocationFee::query()->create([
            'car_id' => $car->id,
            'pickup_location_id' => $pickup->id,
            'dropoff_location_id' => $dropoff->id,
            'cost_cents' => 1500,
            'is_active' => true,
        ]);

        $car->rentalOptions()->attach($option->id, [
            'cost_cents' => 500,
            'is_daily_cost' => true,
        ]);

        OutOfHoursFee::query()->create([
            'name' => 'Late pickup',
            'time_from' => '18:00',
            'time_to' => '23:59',
            'applies_to' => 'pickup',
            'cost_cents' => 2000,
            'pickup_cost_cents' => 2000,
            'dropoff_cost_cents' => 0,
            'vehicle_ids' => [$car->id],
            'is_active' => true,
        ]);

        SpecialPrice::query()->create([
            'name' => 'Summer',
            'date_from' => '2026-06-01',
            'date_to' => '2026-08-31',
            'type' => 'charge',
            'value_mode' => 'fixed',
            'value_fixed_cents' => 1000,
            'vehicle_ids' => [$car->id],
            'is_active' => true,
        ]);

        $copies = app(CarDuplicationService::class)->duplicate($car, 2);

        $this->assertCount(2, $copies);
        $this->assertSame('Source Car (copy)', $copies[0]->name);
        $this->assertSame('Source Car (copy 2)', $copies[1]->name);

        foreach ($copies as $copy) {
            $this->assertDatabaseHas('daily_fares', [
                'car_id' => $copy->id,
                'price_type_id' => $priceType->id,
                'price_per_day_cents' => 8900,
            ]);
            $this->assertDatabaseHas('location_fees', [
                'car_id' => $copy->id,
                'cost_cents' => 1500,
            ]);
            $this->assertDatabaseHas('car_rental_option', [
                'car_id' => $copy->id,
                'rental_option_id' => $option->id,
                'cost_cents' => 500,
                'is_daily_cost' => true,
            ]);
            $this->assertDatabaseHas('out_of_hours_fees', [
                'name' => 'Late pickup',
                'cost_cents' => 2000,
            ]);
            $this->assertDatabaseHas('special_prices', [
                'name' => 'Summer',
                'value_fixed_cents' => 1000,
            ]);

            $ooh = OutOfHoursFee::query()
                ->where('name', 'Late pickup')
                ->whereJsonContains('vehicle_ids', $copy->id)
                ->first();
            $this->assertNotNull($ooh);
            $this->assertSame([$copy->id], $ooh->vehicle_ids);

            $special = SpecialPrice::query()
                ->where('name', 'Summer')
                ->forVehicle($copy->id)
                ->first();
            $this->assertNotNull($special);
            $this->assertSame([$copy->id], $special->vehicle_ids);
        }
    }
}

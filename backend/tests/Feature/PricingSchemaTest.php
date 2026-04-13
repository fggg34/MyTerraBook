<?php

namespace Tests\Feature;

use App\Models\BookingRestriction;
use App\Models\Car;
use App\Models\CarDamageMarker;
use App\Models\CarDistinctiveFeatureDefinition;
use App\Models\CarUnit;
use App\Models\CarUnitDistinctiveValue;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\DailyFare;
use App\Models\ExtraHourFare;
use App\Models\HourlyFare;
use App\Models\Location;
use App\Models\LocationFee;
use App\Models\OutOfHoursFee;
use App\Models\PriceType;
use App\Models\SpecialPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PricingSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_pricing_and_unit_tables_wire_correctly(): void
    {
        $category = Category::query()->create([
            'name' => 'SUV',
            'is_active' => true,
        ]);
        $car = Car::query()->create([
            'category_id' => $category->id,
            'name' => 'Test Car',
            'units_available' => 2,
            'is_active' => true,
        ]);
        $priceType = PriceType::query()->create([
            'name' => 'Standard',
            'is_active' => true,
        ]);

        DailyFare::query()->create([
            'car_id' => $car->id,
            'price_type_id' => $priceType->id,
            'from_days' => 1,
            'to_days' => 7,
            'price_per_day_cents' => 5000,
        ]);
        HourlyFare::query()->create([
            'car_id' => $car->id,
            'price_type_id' => $priceType->id,
            'min_minutes' => 60,
            'max_minutes' => 180,
            'total_price_cents' => 2500,
        ]);
        ExtraHourFare::query()->create([
            'car_id' => $car->id,
            'price_type_id' => $priceType->id,
            'charge_per_extra_hour_cents' => 800,
        ]);

        SpecialPrice::query()->create([
            'name' => 'Summer',
            'type' => 'discount',
            'value_mode' => 'percentage',
            'value_percent_bips' => 1000,
            'weekdays' => [0, 6],
            'is_active' => true,
        ]);

        $pickup = Location::query()->create(['name' => 'A', 'is_active' => true]);
        $dropoff = Location::query()->create(['name' => 'B', 'is_active' => true]);
        LocationFee::query()->create([
            'pickup_location_id' => $pickup->id,
            'dropoff_location_id' => $dropoff->id,
            'cost_cents' => 1500,
            'multiply_by_days' => false,
            'is_active' => true,
        ]);

        OutOfHoursFee::query()->create([
            'time_from' => '20:00:00',
            'time_to' => '08:00:00',
            'applies_to' => 'both',
            'cost_cents' => 2000,
            'is_active' => true,
        ]);

        BookingRestriction::query()->create([
            'name' => 'Peak',
            'date_from' => '2026-06-01',
            'date_to' => '2026-08-31',
            'min_rental_days' => 3,
            'is_active' => true,
        ]);

        Coupon::query()->create([
            'code' => 'SAVE10',
            'type' => 'permanent',
            'discount_type' => 'percentage',
            'discount_percent_bips' => 1000,
            'is_active' => true,
        ]);

        $def = CarDistinctiveFeatureDefinition::query()->create([
            'car_id' => $car->id,
            'name' => 'license_plate',
            'sort_order' => 0,
        ]);
        $unit = CarUnit::query()->create([
            'car_id' => $car->id,
            'is_active' => true,
        ]);
        CarUnitDistinctiveValue::query()->create([
            'car_unit_id' => $unit->id,
            'car_distinctive_feature_definition_id' => $def->id,
            'value' => 'AB-123-CD',
        ]);
        CarDamageMarker::query()->create([
            'car_unit_id' => $unit->id,
            'position_x' => 40.5,
            'position_y' => 55.0,
            'description' => 'Scratch rear door',
        ]);

        $this->assertCount(1, $car->fresh()->dailyFares);
        $this->assertCount(1, $car->fresh()->hourlyFares);
        $this->assertCount(1, $car->fresh()->extraHourFares);
        $this->assertSame('AB-123-CD', $unit->fresh()->distinctiveValues->first()->value);
        $this->assertCount(1, $unit->fresh()->damageMarkers);
    }
}

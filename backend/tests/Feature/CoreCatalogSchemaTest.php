<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\Category;
use App\Models\Characteristic;
use App\Models\Location;
use App\Models\LocationClosingDay;
use App\Models\LocationSchedule;
use App\Models\LocationScheduleBreak;
use App\Models\PriceType;
use App\Models\RentalOption;
use App\Models\TaxRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoreCatalogSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_migrations_allow_full_catalog_graph(): void
    {
        $vat = TaxRate::query()->create([
            'name' => 'VAT 20%',
            'basis_points' => 2000,
        ]);

        $category = Category::query()->create([
            'name' => 'Economy',
            'description' => 'Compact cars',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $char = Characteristic::query()->create([
            'name' => 'Automatic',
            'is_search_filter' => true,
        ]);

        $location = Location::query()->create([
            'name' => 'Airport',
            'is_active' => true,
        ]);

        $schedule = LocationSchedule::query()->create([
            'location_id' => $location->id,
            'weekday' => 1,
            'opening_time' => '08:00:00',
            'closing_time' => '20:00:00',
            'is_closed' => false,
        ]);

        LocationScheduleBreak::query()->create([
            'location_schedule_id' => $schedule->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        LocationClosingDay::query()->create([
            'location_id' => $location->id,
            'specific_date' => '2026-12-25',
            'recurring_weekday' => null,
        ]);

        $priceType = PriceType::query()->create([
            'name' => 'Basic',
            'tax_rate_id' => $vat->id,
            'is_active' => true,
        ]);

        $option = RentalOption::query()->create([
            'name' => 'GPS',
            'cost_cents' => 500,
            'is_daily_cost' => true,
            'has_quantity' => false,
            'is_mandatory' => false,
            'is_active' => true,
        ]);

        $car = Car::query()->create([
            'category_id' => $category->id,
            'name' => 'Toyota Yaris',
            'units_available' => 3,
            'is_active' => true,
        ]);

        $car->locations()->attach($location->id, [
            'allows_pickup' => true,
            'allows_dropoff' => true,
        ]);
        $car->characteristics()->attach($char->id);
        $car->rentalOptions()->attach($option->id);

        $this->assertDatabaseHas('tax_rates', ['id' => $vat->id]);
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
        $this->assertDatabaseHas('cars', ['id' => $car->id]);
        $this->assertTrue($car->locations()->whereKey($location->id)->exists());
        $this->assertTrue($car->characteristics()->whereKey($char->id)->exists());
        $this->assertTrue($car->rentalOptions()->whereKey($option->id)->exists());
        $this->assertSame($vat->id, $priceType->fresh()->tax_rate_id);
    }
}

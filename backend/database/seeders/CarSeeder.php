<?php

namespace Database\Seeders;

use App\Models\AvailabilityBlock;
use App\Models\Car;
use App\Models\CarDistinctiveFeatureDefinition;
use App\Models\CarUnit;
use App\Models\CarUnitDistinctiveValue;
use App\Models\Category;
use App\Models\Characteristic;
use App\Models\DailyFare;
use App\Models\ExtraHourFare;
use App\Models\Location;
use App\Models\PriceType;
use App\Models\RentalOption;
use Illuminate\Database\Seeder;

class CarSeeder extends Seeder
{
    /** @var list<array{0: string, 1: string, 2: int, 3: string, 4: string, 5: string, 6: int}> */
    private array $catalog = [
        ['Toyota', 'Corolla', 2022, 'Economy', 'automatic', 'hybrid', 3500],
        ['Volkswagen', 'Golf', 2021, 'Compact', 'manual', 'petrol', 3200],
        ['BMW', '3 Series', 2023, 'Luxury', 'automatic', 'diesel', 8500],
        ['Mercedes-Benz', 'C-Class', 2022, 'Luxury', 'automatic', 'diesel', 9000],
        ['Audi', 'A4', 2021, 'Mid-size', 'automatic', 'petrol', 7200],
        ['Ford', 'Focus', 2020, 'Compact', 'manual', 'petrol', 2800],
        ['Hyundai', 'Tucson', 2023, 'SUV', 'automatic', 'petrol', 5500],
        ['Renault', 'Clio', 2022, 'Economy', 'manual', 'petrol', 2500],
        ['Peugeot', '3008', 2022, 'SUV', 'automatic', 'diesel', 5800],
        ['Fiat', '500', 2021, 'Compact', 'manual', 'petrol', 2200],
        ['Tesla', 'Model 3', 2023, 'Electric', 'automatic', 'electric', 9500],
        ['Nissan', 'Qashqai', 2022, 'SUV', 'automatic', 'petrol', 5200],
        ['Honda', 'CR-V', 2021, 'SUV', 'automatic', 'hybrid', 6000],
        ['Kia', 'Sportage', 2023, 'SUV', 'automatic', 'diesel', 5400],
        ['Volvo', 'XC60', 2022, 'Luxury', 'automatic', 'hybrid', 8800],
        ['Skoda', 'Octavia', 2021, 'Mid-size', 'manual', 'diesel', 3800],
        ['Seat', 'Leon', 2022, 'Compact', 'automatic', 'petrol', 3400],
        ['Dacia', 'Duster', 2020, 'SUV', 'manual', 'diesel', 3000],
    ];

    public function run(): void
    {
        $categories = Category::query()->get()->keyBy('name');
        $locations = Location::query()->where('is_active', true)->get();
        $characteristics = Characteristic::query()->get();
        $rentalOptions = RentalOption::query()->where('is_active', true)->get();
        $standardPriceType = PriceType::query()->where('name', 'Standard Rate')->firstOrFail();
        $premiumPriceType = PriceType::query()->where('name', 'Premium Rate')->firstOrFail();

        foreach ($this->catalog as [$make, $model, $year, $categoryName, $transmission, $fuel, $dailyRateCents]) {
            $name = "{$make} {$model} {$year}";
            $category = $categories->get($categoryName) ?? $categories->first();

            $car = Car::query()->firstOrCreate(
                ['name' => $name],
                [
                    'category_id' => $category->id,
                    'description' => fake()->paragraph(2),
                    'transmission' => $transmission,
                    'fuel_type' => $fuel,
                    'units_available' => fake()->numberBetween(1, 3),
                    'is_active' => true,
                ]
            );

            $locationPivot = $locations->mapWithKeys(fn (Location $loc) => [
                $loc->id => ['allows_pickup' => true, 'allows_dropoff' => true],
            ])->all();
            $car->locations()->syncWithoutDetaching($locationPivot);

            $carCharacteristics = $characteristics->random(min(4, $characteristics->count()))->pluck('id');
            $car->characteristics()->syncWithoutDetaching($carCharacteristics);

            $carOptions = $rentalOptions->random(min(3, $rentalOptions->count()))->pluck('id');
            $car->rentalOptions()->syncWithoutDetaching($carOptions);

            DailyFare::query()->firstOrCreate(
                [
                    'car_id' => $car->id,
                    'price_type_id' => $standardPriceType->id,
                    'from_days' => 1,
                    'to_days' => 6,
                ],
                ['price_per_day_cents' => $dailyRateCents]
            );

            DailyFare::query()->firstOrCreate(
                [
                    'car_id' => $car->id,
                    'price_type_id' => $standardPriceType->id,
                    'from_days' => 7,
                    'to_days' => 30,
                ],
                ['price_per_day_cents' => (int) round($dailyRateCents * 0.85)]
            );

            DailyFare::query()->firstOrCreate(
                [
                    'car_id' => $car->id,
                    'price_type_id' => $premiumPriceType->id,
                    'from_days' => 1,
                    'to_days' => 30,
                ],
                ['price_per_day_cents' => (int) round($dailyRateCents * 1.2)]
            );

            ExtraHourFare::query()->firstOrCreate(
                ['car_id' => $car->id, 'price_type_id' => $standardPriceType->id],
                ['charge_per_extra_hour_cents' => (int) round($dailyRateCents / 8)]
            );

            $plateDef = CarDistinctiveFeatureDefinition::query()->firstOrCreate(
                ['car_id' => $car->id, 'name' => 'License Plate'],
                ['sort_order' => 0]
            );

            $colorDef = CarDistinctiveFeatureDefinition::query()->firstOrCreate(
                ['car_id' => $car->id, 'name' => 'Color'],
                ['sort_order' => 1]
            );

            for ($i = 0; $i < $car->units_available; $i++) {
                $unit = CarUnit::query()->firstOrCreate(
                    ['car_id' => $car->id, 'sort_order' => $i],
                    ['is_active' => true]
                );

                CarUnitDistinctiveValue::query()->firstOrCreate(
                    ['car_unit_id' => $unit->id, 'car_distinctive_feature_definition_id' => $plateDef->id],
                    ['value' => strtoupper(fake()->bothify('??-###-??'))]
                );

                CarUnitDistinctiveValue::query()->firstOrCreate(
                    ['car_unit_id' => $unit->id, 'car_distinctive_feature_definition_id' => $colorDef->id],
                    ['value' => fake()->randomElement(['White', 'Black', 'Silver', 'Blue', 'Red', 'Grey'])]
                );
            }
        }

        $sampleCars = Car::query()->inRandomOrder()->limit(3)->get();
        foreach ($sampleCars as $car) {
            AvailabilityBlock::query()->firstOrCreate(
                [
                    'car_id' => $car->id,
                    'source' => 'maintenance',
                    'starts_at' => now()->addDays(20)->startOfDay(),
                    'ends_at' => now()->addDays(22)->endOfDay(),
                ],
                [
                    'units_blocked' => 1,
                    'notes' => 'Scheduled service',
                    'is_active' => true,
                ]
            );
        }
    }
}

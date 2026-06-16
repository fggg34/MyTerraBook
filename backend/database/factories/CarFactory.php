<?php

namespace Database\Factories;

use App\Models\Car;
use App\Models\SubCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Car> */
class CarFactory extends Factory
{
    protected $model = Car::class;

    /** @var list<string> */
    private static array $catalog = [
        ['Toyota', 'Corolla', 2022, 'automatic', 'hybrid'],
        ['Volkswagen', 'Golf', 2021, 'manual', 'petrol'],
        ['BMW', '3 Series', 2023, 'automatic', 'diesel'],
        ['Mercedes-Benz', 'C-Class', 2022, 'automatic', 'diesel'],
        ['Audi', 'A4', 2021, 'automatic', 'petrol'],
        ['Ford', 'Focus', 2020, 'manual', 'petrol'],
        ['Hyundai', 'Tucson', 2023, 'automatic', 'petrol'],
        ['Renault', 'Clio', 2022, 'manual', 'petrol'],
        ['Peugeot', '3008', 2022, 'automatic', 'diesel'],
        ['Fiat', '500', 2021, 'manual', 'petrol'],
        ['Tesla', 'Model 3', 2023, 'automatic', 'electric'],
        ['Nissan', 'Qashqai', 2022, 'automatic', 'petrol'],
        ['Honda', 'CR-V', 2021, 'automatic', 'hybrid'],
        ['Kia', 'Sportage', 2023, 'automatic', 'diesel'],
        ['Volvo', 'XC60', 2022, 'automatic', 'hybrid'],
        ['Skoda', 'Octavia', 2021, 'manual', 'diesel'],
        ['Seat', 'Leon', 2022, 'automatic', 'petrol'],
        ['Dacia', 'Duster', 2020, 'manual', 'diesel'],
        ['Jeep', 'Renegade', 2022, 'automatic', 'petrol'],
        ['Mini', 'Cooper', 2021, 'automatic', 'petrol'],
    ];

    private static int $catalogIndex = 0;

    public function definition(): array
    {
        $entry = self::$catalog[self::$catalogIndex % count(self::$catalog)];
        self::$catalogIndex++;

        [$make, $model, $year, $transmission, $fuel] = $entry;
        $name = "{$make} {$model} {$year}";

        return [
            'sub_category_id' => SubCategory::factory(),
            'name' => $name,
            'description' => fake()->paragraph(3),
            'transmission' => $transmission,
            'fuel_type' => $fuel,
            'drive_type' => fake()->randomElement(['fwd', 'rwd', 'awd', '4wd']),
            'seats' => 5,
            'bags' => 2,
            'main_image_path' => null,
            'details_image_paths' => null,
            'units_available' => fake()->numberBetween(1, 4),
            'ical_import_url' => null,
            'is_active' => true,
        ];
    }
}

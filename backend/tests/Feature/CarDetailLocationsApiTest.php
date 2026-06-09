<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\Location;
use App\Models\MainCategory;
use App\Models\SubCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarDetailLocationsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_car_detail_includes_pickup_and_dropoff_locations(): void
    {
        $pickup = Location::query()->create(['name' => 'Airport', 'is_active' => true]);
        $dropOnly = Location::query()->create(['name' => 'City Center', 'is_active' => true]);

        $main = MainCategory::query()->firstOrCreate(['slug' => 'car'], ['name' => 'Car', 'is_active' => true]);
        $category = SubCategory::query()->create(['main_category_id' => $main->id, 'name' => 'SUV', 'is_active' => true, 'is_search_filter' => true]);
        $car = Car::query()->create([
            'name' => 'Explorer',
            'slug' => 'explorer',
            'sub_category_id' => $category->id,
            'is_active' => true,
        ]);

        $car->locations()->attach($pickup->id, ['allows_pickup' => true, 'allows_dropoff' => true]);
        $car->locations()->attach($dropOnly->id, ['allows_pickup' => false, 'allows_dropoff' => true]);

        $response = $this->getJson("/api/cars/{$car->id}");

        $response->assertOk()
            ->assertJsonPath('data.pickup_locations.0.name', 'Airport')
            ->assertJsonPath('data.dropoff_locations.0.name', 'Airport')
            ->assertJsonCount(2, 'data.dropoff_locations');

        $dropoffNames = collect($response->json('data.dropoff_locations'))->pluck('name')->all();
        $this->assertContains('City Center', $dropoffNames);
        $this->assertContains('Airport', $dropoffNames);

        $pickupNames = collect($response->json('data.pickup_locations'))->pluck('name')->all();
        $this->assertContains('Airport', $pickupNames);
        $this->assertNotContains('City Center', $pickupNames);
    }
}

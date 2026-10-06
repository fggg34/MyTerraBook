<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\MainCategory;
use App\Models\SubCategory;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestinationSearchTest extends TestCase
{
    use RefreshDatabase;

    private function depot(string $country, bool $active = true): array
    {
        $location = Location::query()->create([
            'name' => $country.' depot '.uniqid(),
            'country_code' => $country,
            'is_active' => true,
        ]);
        $main = MainCategory::query()->firstOrCreate(['slug' => 'campervan'], ['name' => 'Campervan', 'is_active' => true]);
        $category = SubCategory::query()->firstOrCreate(['name' => 'Destination test'], ['main_category_id' => $main->id, 'is_active' => true]);
        $car = Car::query()->create(['name' => 'Vehicle '.uniqid(), 'sub_category_id' => $category->id, 'is_active' => $active]);
        $car->locations()->attach($location->id, ['allows_pickup' => true, 'allows_dropoff' => true]);

        return [$location, $car];
    }

    public function test_destinations_only_include_public_pickup_inventory(): void
    {
        $this->depot('IS');
        $this->depot('NZ');
        $this->depot('DE', false);
        Location::query()->create(['name' => 'Unlinked', 'country_code' => 'FR', 'is_active' => true]);

        $response = $this->getJson('/api/destinations')->assertOk()->assertJsonCount(2, 'data');
        $this->assertEqualsCanonicalizing(['IS', 'NZ'], array_column($response->json('data'), 'code'));
    }

    public function test_country_filters_cars_and_suggestions_without_falling_back(): void
    {
        [$iceland] = $this->depot('IS');
        [$nz, $car] = $this->depot('NZ');
        $this->getJson('/api/cars?country_code=NZ')->assertOk()
            ->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $car->id);
        $this->getJson('/api/search/suggestions?scope=location&country_code=NZ')->assertOk()
            ->assertJsonCount(1, 'data')->assertJsonPath('data.0.value', (string) $nz->id);
        $this->getJson('/api/cars?country_code=FR')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/cars?country_code=NZ&pickup_location_id='.$iceland->id)
            ->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_returns_require_a_shared_public_vehicle(): void
    {
        [$pickup, $car] = $this->depot('IS');
        [$other] = $this->depot('NZ');
        $return = Location::query()->create(['name' => 'One way', 'country_code' => 'IS', 'is_active' => true]);
        $car->locations()->attach($return->id, ['allows_pickup' => false, 'allows_dropoff' => true]);
        $response = $this->getJson('/api/search/suggestions?scope=location&role=dropoff&pickup_location_id='.$pickup->id)->assertOk();
        $ids = array_column($response->json('data'), 'value');
        $this->assertContains((string) $return->id, $ids);
        $this->assertNotContains((string) $other->id, $ids);
    }

    public function test_invalid_country_is_rejected(): void
    {
        $this->getJson('/api/cars?country_code=invalid')->assertUnprocessable();
        $this->getJson('/api/search/suggestions?scope=location&country_code=XX')->assertUnprocessable();
    }

    public function test_existing_locations_keep_iceland_default(): void
    {
        $location = Location::query()->create(['name' => 'Legacy depot', 'is_active' => true]);
        $this->assertSame('IS', $location->fresh()->country_code);
    }
}

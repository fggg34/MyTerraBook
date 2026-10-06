<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\MainCategory;
use App\Models\Setting;
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

    public function test_searching_iceland_returns_depots_that_do_not_say_iceland(): void
    {
        [$location] = $this->depot('IS');
        $location->update(['name' => 'Keflavik Airport']);

        $this->getJson('/api/search/suggestions?scope=location&q=Iceland')
            ->assertOk()
            ->assertJsonFragment(['label' => 'Keflavik Airport']);
    }

    public function test_greenlight_cars_make_their_depots_iceland_destinations(): void
    {
        Setting::putValue('partners.greenlight', [
            'display_name' => 'Greenlight car rental',
        ]);
        $this->depot('IS');
        $location = Location::query()->create([
            'name' => 'Keflavik Airport',
            'country_code' => 'IS',
            'is_active' => true,
        ]);
        $main = MainCategory::query()->firstOrCreate(['slug' => 'car'], ['name' => 'Car', 'is_active' => true]);
        $category = SubCategory::query()->firstOrCreate(
            ['name' => 'Greenlight cars'],
            ['main_category_id' => $main->id, 'is_active' => true],
        );
        $car = Car::query()->create([
            'name' => 'Greenlight car',
            'sub_category_id' => $category->id,
            'is_active' => true,
            'external_provider' => 'greenlight',
            'external_vehicle_id' => 'veh_kef',
        ]);
        $car->locations()->attach($location->id, ['allows_pickup' => true, 'allows_dropoff' => true]);

        $iceland = collect($this->getJson('/api/destinations?main_category=campervan')->assertOk()->json('data'))
            ->firstWhere('code', 'IS');

        $this->assertSame('Keflavik Airport', $iceland['locations'][0]['name']);
        $this->assertSame('Greenlight car rental', $iceland['locations'][0]['partner_name']);
    }

    public function test_greenlight_locations_are_destinations_inside_their_country(): void
    {
        Setting::putValue('partners.greenlight', [
            'display_name' => 'Greenlight car rental',
        ]);
        $this->depot('IS');
        $location = Location::query()->create([
            'name' => 'Keflavik Airport',
            'country_code' => 'IS',
            'is_active' => true,
            'external_provider' => 'greenlight',
            'external_id' => 'loc_kef',
        ]);
        $main = MainCategory::query()->firstOrCreate(['slug' => 'car'], ['name' => 'Car', 'is_active' => true]);
        $category = SubCategory::query()->firstOrCreate(
            ['name' => 'Greenlight cars'],
            ['main_category_id' => $main->id, 'is_active' => true],
        );
        $car = Car::query()->create([
            'name' => 'Greenlight car',
            'sub_category_id' => $category->id,
            'is_active' => true,
            'external_provider' => 'greenlight',
            'external_vehicle_id' => 'veh_kef',
        ]);
        $car->locations()->attach($location->id, ['allows_pickup' => true, 'allows_dropoff' => true]);

        $iceland = collect($this->getJson('/api/destinations?main_category=campervan')->assertOk()->json('data'))
            ->firstWhere('code', 'IS');

        $this->assertNotNull($iceland);
        $this->assertCount(1, $iceland['locations']);
        $this->assertSame('Keflavik Airport', $iceland['locations'][0]['name']);
        $this->assertSame('Greenlight car rental', $iceland['locations'][0]['partner_name']);
        $this->assertSame('car', $iceland['locations'][0]['vehicle_type']);
    }

    public function test_greenlight_depots_are_labeled_with_the_partner_section(): void
    {
        Setting::putValue('partners.greenlight', [
            'display_name' => 'Greenlight car rental',
        ]);
        $location = Location::query()->create([
            'name' => 'Keflavik Airport',
            'country_code' => 'IS',
            'is_active' => true,
            'external_provider' => 'greenlight',
            'external_id' => 'loc_kef',
        ]);
        $main = MainCategory::query()->firstOrCreate(['slug' => 'car'], ['name' => 'Car', 'is_active' => true]);
        $category = SubCategory::query()->firstOrCreate(
            ['name' => 'Partner location test'],
            ['main_category_id' => $main->id, 'is_active' => true],
        );
        $car = Car::query()->create([
            'name' => 'Greenlight car',
            'sub_category_id' => $category->id,
            'is_active' => true,
            'external_provider' => 'greenlight',
            'external_vehicle_id' => 'veh_kef',
        ]);
        $car->locations()->attach($location->id, ['allows_pickup' => true, 'allows_dropoff' => true]);

        $this->getJson('/api/search/suggestions?scope=location&country_code=IS')
            ->assertOk()
            ->assertJsonPath('data.0.label', 'Keflavik Airport')
            ->assertJsonPath('data.0.partner_name', 'Greenlight car rental');
    }

    public function test_existing_locations_keep_iceland_default(): void
    {
        $location = Location::query()->create(['name' => 'Legacy depot', 'is_active' => true]);
        $this->assertSame('IS', $location->fresh()->country_code);
    }
}

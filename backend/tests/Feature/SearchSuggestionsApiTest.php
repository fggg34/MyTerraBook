<?php

namespace Tests\Feature;

use App\Enums\GuestHouseStatus;
use App\Enums\GuestHouseType;
use App\Models\Car;
use App\Models\MainCategory;
use App\Models\SubCategory;
use App\Models\GuestHouse;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchSuggestionsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_location_suggestions_match_name_and_address(): void
    {
        $pickup = Location::query()->create(['name' => 'Keflavík Airport', 'address' => '235 Keflavík', 'is_active' => true]);
        Location::query()->create(['name' => 'Akureyri Downtown', 'address' => 'Akureyri centre', 'is_active' => true]);

        $main = MainCategory::query()->firstOrCreate(['slug' => 'car'], ['name' => 'Car', 'is_active' => true]);
        $category = SubCategory::query()->create(['main_category_id' => $main->id, 'name' => 'Van', 'is_active' => true, 'is_search_filter' => true]);
        $car = Car::query()->create([
            'name' => 'Camper',
            'slug' => 'camper',
            'sub_category_id' => $category->id,
            'is_active' => true,
        ]);
        $car->locations()->attach($pickup->id, ['allows_pickup' => true, 'allows_dropoff' => true]);

        $response = $this->getJson('/api/search/suggestions?scope=location&q=keflav&role=pickup');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.label', 'Keflavík Airport')
            ->assertJsonPath('data.0.type', 'location')
            ->assertJsonPath('data.0.value', (string) $pickup->id);
    }

    public function test_dropoff_suggestions_respect_pickup_combinations(): void
    {
        $pickup = Location::query()->create(['name' => 'Pickup Hub', 'is_active' => true]);
        $allowed = Location::query()->create(['name' => 'Allowed Dropoff', 'is_active' => true]);
        $blocked = Location::query()->create(['name' => 'Blocked Dropoff', 'is_active' => true]);

        $pickup->dropoffCombinations()->sync([$allowed->id]);

        $main = MainCategory::query()->firstOrCreate(['slug' => 'car'], ['name' => 'Car', 'is_active' => true]);
        $category = SubCategory::query()->create(['main_category_id' => $main->id, 'name' => 'Economy', 'is_active' => true, 'is_search_filter' => true]);
        $car = Car::query()->create([
            'name' => 'Sedan',
            'slug' => 'sedan',
            'sub_category_id' => $category->id,
            'is_active' => true,
        ]);
        $car->locations()->attach($pickup->id, ['allows_pickup' => true, 'allows_dropoff' => true]);
        $car->locations()->attach($allowed->id, ['allows_pickup' => false, 'allows_dropoff' => true]);
        $car->locations()->attach($blocked->id, ['allows_pickup' => false, 'allows_dropoff' => true]);

        $response = $this->getJson('/api/search/suggestions?scope=location&role=dropoff&pickup_location_id='.$pickup->id);

        $response->assertOk();
        $labels = collect($response->json('data'))->pluck('label')->all();
        $this->assertContains('Allowed Dropoff', $labels);
        $this->assertNotContains('Blocked Dropoff', $labels);
    }

    public function test_guesthouse_suggestions_return_cities_and_listings(): void
    {
        GuestHouse::query()->create([
            'name' => 'Northern Lights Villa',
            'slug' => 'northern-lights-villa',
            'type' => GuestHouseType::Villa,
            'status' => GuestHouseStatus::Active,
            'city' => 'Reykjavik',
            'max_guests' => 4,
            'base_price_per_night' => 10000,
        ]);
        GuestHouse::query()->create([
            'name' => 'Harbour View',
            'slug' => 'harbour-view',
            'type' => GuestHouseType::Apartment,
            'status' => GuestHouseStatus::Active,
            'city' => 'Akureyri',
            'max_guests' => 2,
            'base_price_per_night' => 8000,
        ]);

        $response = $this->getJson('/api/search/suggestions?scope=guesthouse&q=rey');

        $response->assertOk();
        $types = collect($response->json('data'))->pluck('type')->all();
        $this->assertContains('city', $types);
        $this->assertContains('guesthouse', $types);
    }

    public function test_invalid_scope_returns_validation_error(): void
    {
        $this->getJson('/api/search/suggestions?scope=unknown')
            ->assertStatus(422);
    }
}

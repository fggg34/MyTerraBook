<?php

namespace Tests\Feature;

use App\Enums\ListingApprovalStatus;
use App\Models\Car;
use App\Models\Location;
use App\Models\MainCategory;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class HostCatalogPublicFallbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_main_categories_available_without_auth(): void
    {
        $this->getJson('/api/main-categories')
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'car')
            ->assertJsonPath('data.1.slug', 'campervan');
    }

    public function test_host_catalog_returns_active_locations(): void
    {
        $active = Location::query()->create(['name' => 'Airport Kef', 'slug' => 'airport-kef', 'is_active' => true]);
        Location::query()->create(['name' => 'Hidden', 'slug' => 'hidden', 'is_active' => false]);

        Sanctum::actingAs(User::factory()->host()->create());

        $this->getJson('/api/host/catalog/locations')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $active->id)
            ->assertJsonPath('data.0.name', 'Airport Kef');
    }

    public function test_public_sub_categories_include_main_category_id(): void
    {
        $car = MainCategory::query()->where('slug', 'car')->firstOrFail();

        SubCategory::query()->create([
            'main_category_id' => $car->id,
            'name' => 'Test SUV',
            'slug' => 'test-suv',
            'is_active' => true,
            'is_search_filter' => true,
            'sort_order' => 50,
        ]);

        $this->getJson('/api/sub-categories')
            ->assertOk()
            ->assertJsonFragment([
                'name' => 'Test SUV',
                'main_category_id' => $car->id,
            ]);
    }

    public function test_host_can_create_custom_location(): void
    {
        $host = User::factory()->host()->create();
        Sanctum::actingAs($host);

        $this->postJson('/api/host/catalog/locations', [
            'name' => 'My driveway',
            'address' => 'Route 1',
        ])->assertCreated()
            ->assertJsonPath('data.name', 'My driveway');

        $this->getJson('/api/host/catalog/locations')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'My driveway');

        $this->assertDatabaseHas('locations', [
            'name' => 'My driveway',
            'host_user_id' => $host->id,
            'country_code' => 'IS',
            'is_active' => true,
        ]);
    }

    public function test_host_operating_country_is_used_for_locations_and_destinations(): void
    {
        $host = User::factory()->host()->create(['country_code' => 'AL']);
        Sanctum::actingAs($host);

        $created = $this->postJson('/api/host/catalog/locations', [
            'name' => 'Tirana depot',
        ])->assertCreated();

        $locationId = $created->json('data.id');
        $this->assertDatabaseHas('locations', [
            'id' => $locationId,
            'country_code' => 'AL',
            'host_user_id' => $host->id,
        ]);

        $this->getJson('/api/destinations')->assertOk()->assertJsonCount(0, 'data');

        $main = MainCategory::query()->firstOrCreate(['slug' => 'campervan'], ['name' => 'Campervan', 'is_active' => true]);
        $category = SubCategory::query()->create([
            'main_category_id' => $main->id,
            'name' => 'Host vans',
            'is_active' => true,
        ]);
        $car = Car::query()->create([
            'user_id' => $host->id,
            'sub_category_id' => $category->id,
            'name' => 'Albania van',
            'is_active' => true,
            'listing_status' => ListingApprovalStatus::Approved,
        ]);
        $car->locations()->attach($locationId, ['allows_pickup' => true, 'allows_dropoff' => true]);

        $this->getJson('/api/destinations?main_category=campervan')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', 'AL')
            ->assertJsonPath('data.0.name', 'Albania');
    }
}

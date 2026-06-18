<?php

namespace Tests\Feature;

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
            'is_active' => true,
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\MainCategory;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MainCategoryRecoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_ensure_by_slug_restores_soft_deleted_core_category(): void
    {
        $car = MainCategory::query()->where('slug', 'car')->firstOrFail();
        $car->delete();

        $restored = MainCategory::ensureBySlug('car', [
            'name' => 'Car',
            'description' => 'Passenger cars and 4×4s.',
            'sort_order' => 1,
        ]);

        $this->assertFalse($restored->trashed());
        $this->assertTrue($restored->is_active);
        $this->assertSame('car', $restored->slug);
        $this->assertDatabaseHas('main_categories', [
            'id' => $car->id,
            'slug' => 'car',
            'deleted_at' => null,
        ]);
    }

    public function test_unique_slug_from_name_avoids_soft_deleted_slug_collision(): void
    {
        MainCategory::query()->where('slug', 'car')->firstOrFail()->delete();

        $this->assertSame('car-1', MainCategory::uniqueSlugFromName('car'));
    }

    public function test_host_main_categories_empty_when_core_categories_soft_deleted(): void
    {
        $host = User::factory()->host()->create();

        MainCategory::query()->whereIn('slug', MainCategory::CORE_SLUGS)->get()->each->delete();

        Sanctum::actingAs($host);

        $this->getJson('/api/host/catalog/main-categories')
            ->assertOk()
            ->assertJsonPath('data', []);
    }

    public function test_host_catalog_returns_categories_after_core_restore(): void
    {
        $host = User::factory()->host()->create();

        $car = MainCategory::query()->where('slug', 'car')->firstOrFail();
        $sub = SubCategory::query()->create([
            'main_category_id' => $car->id,
            'name' => 'SUV',
            'slug' => 'suv-test',
            'is_active' => true,
            'is_search_filter' => true,
            'sort_order' => 99,
        ]);
        $car->delete();

        MainCategory::ensureBySlug('car', ['name' => 'Car', 'sort_order' => 1]);

        Sanctum::actingAs($host);

        $this->getJson('/api/host/catalog/main-categories')
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'car');

        $this->getJson('/api/host/catalog/categories')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'SUV');
    }

    public function test_core_main_categories_are_marked_as_core(): void
    {
        $car = MainCategory::query()->where('slug', 'car')->firstOrFail();

        $this->assertTrue($car->isCore());
    }
}

<?php

namespace Tests\Feature;

use App\Models\MainCategory;
use App\Models\SubCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}

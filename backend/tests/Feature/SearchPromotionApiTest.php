<?php

namespace Tests\Feature;

use App\Models\SearchPromotion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchPromotionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_promotions_filter_by_context(): void
    {
        SearchPromotion::query()->create([
            'title' => 'Camper promo',
            'layout' => SearchPromotion::LAYOUT_CARD,
            'context' => 'campervan',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        SearchPromotion::query()->create([
            'title' => 'Car promo',
            'layout' => SearchPromotion::LAYOUT_CARD,
            'context' => 'car',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        SearchPromotion::query()->create([
            'title' => 'Global promo',
            'layout' => SearchPromotion::LAYOUT_LANDSCAPE,
            'context' => SearchPromotion::CONTEXT_ALL,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $response = $this->getJson('/api/search-promotions?context=campervan');

        $response->assertOk();
        $titles = collect($response->json('data'))->pluck('title')->all();
        $this->assertContains('Camper promo', $titles);
        $this->assertContains('Global promo', $titles);
        $this->assertNotContains('Car promo', $titles);
    }
}

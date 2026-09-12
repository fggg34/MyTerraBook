<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Services\SitePreviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitePreviewApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.site_preview_force_open' => false]);
    }

    public function test_guest_sees_the_storefront_when_coming_soon_is_off(): void
    {
        Setting::putValue('system.coming_soon', ['enabled' => false]);

        $this->getJson('/api/site-preview')
            ->assertOk()
            ->assertJson([
                'preview_unlocked' => true,
                'coming_soon' => false,
            ]);
    }

    public function test_string_false_does_not_enable_coming_soon(): void
    {
        Setting::putValue('system.coming_soon', ['enabled' => 'false']);

        $this->assertFalse(app(SitePreviewService::class)->isComingSoonEnabled());

        $this->getJson('/api/site-preview')
            ->assertOk()
            ->assertJson([
                'preview_unlocked' => true,
                'coming_soon' => false,
            ]);
    }

    public function test_guest_is_locked_when_coming_soon_is_on(): void
    {
        Setting::putValue('system.coming_soon', ['enabled' => true]);

        $this->getJson('/api/site-preview')
            ->assertOk()
            ->assertJson([
                'preview_unlocked' => false,
                'coming_soon' => true,
            ]);
    }

    public function test_admin_can_preview_while_coming_soon_is_on(): void
    {
        Setting::putValue('system.coming_soon', ['enabled' => true]);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->getJson('/api/site-preview')
            ->assertOk()
            ->assertJson([
                'preview_unlocked' => true,
                'coming_soon' => true,
            ]);
    }
}

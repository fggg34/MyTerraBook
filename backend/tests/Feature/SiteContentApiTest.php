<?php

namespace Tests\Feature;

use App\Data\SiteContentDefaults;
use App\Models\SiteContentPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteContentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_site_content_index_returns_all_page_keys(): void
    {
        $this->seed(\Database\Seeders\SiteContentSeeder::class);

        $response = $this->getJson('/api/site-content');

        $response->assertOk();
        $data = $response->json('data');

        $this->assertIsArray($data);
        $this->assertArrayHasKey('global', $data);
        $this->assertArrayHasKey('home', $data);
        $this->assertArrayHasKey('about', $data);
        $this->assertArrayHasKey('checkout', $data);
        $this->assertArrayHasKey('host-panel', $data);
    }

    public function test_site_content_show_returns_single_page(): void
    {
        SiteContentPage::query()->create([
            'page_key' => 'auth-login',
            'label' => 'Login',
            'content' => ['title' => 'Welcome back'],
            'is_published' => true,
            'sort_order' => 17,
        ]);

        $response = $this->getJson('/api/site-content/auth-login');

        $response->assertOk();
        $response->assertJsonPath('data.page_key', 'auth-login');
        $response->assertJsonPath('data.content.title', 'Welcome back');
    }

    public function test_homepage_endpoint_uses_site_content(): void
    {
        SiteContentPage::query()->create([
            'page_key' => 'global',
            'label' => 'Global',
            'content' => SiteContentDefaults::forPage('global'),
            'is_published' => true,
            'sort_order' => 0,
        ]);

        SiteContentPage::query()->create([
            'page_key' => 'home',
            'label' => 'Home',
            'content' => array_replace_recursive(SiteContentDefaults::forPage('home'), [
                'hero' => ['heading' => 'CMS Hero Heading'],
            ]),
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $response = $this->getJson('/api/homepage');

        $response->assertOk();
        $response->assertJsonPath('hero.heading', 'CMS Hero Heading');
    }

    public function test_faq_items_are_at_root_not_nested(): void
    {
        SiteContentPage::query()->create([
            'page_key' => 'faq',
            'label' => 'FAQ',
            'content' => [
                'items' => [
                    ['num' => '99', 'question' => 'Test?', 'answer' => 'Yes.'],
                ],
            ],
            'is_published' => true,
            'sort_order' => 3,
        ]);

        $response = $this->getJson('/api/site-content/faq');

        $response->assertOk();
        $response->assertJsonPath('data.content.items.0.question', 'Test?');
        $this->assertArrayNotHasKey('items', $response->json('data.content.items'));
    }

    public function test_contact_details_are_flat_at_page_root(): void
    {
        SiteContentPage::query()->create([
            'page_key' => 'contact',
            'label' => 'Contact',
            'content' => [
                'phone' => '+354 111 2222',
                'email' => 'hello@example.is',
            ],
            'is_published' => true,
            'sort_order' => 4,
        ]);

        $response = $this->getJson('/api/site-content/contact');

        $response->assertOk();
        $response->assertJsonPath('data.content.phone', '+354 111 2222');
        $response->assertJsonPath('data.content.email', 'hello@example.is');
    }

    public function test_global_branding_logo_resolves_from_public_storage(): void
    {
        $path = 'site-content/global/test-logo.svg';
        \Illuminate\Support\Facades\Storage::disk('public')->put($path, '<svg xmlns="http://www.w3.org/2000/svg"/>');

        SiteContentPage::query()->create([
            'page_key' => 'global',
            'label' => 'Global',
            'content' => [
                'branding' => [
                    'logoMode' => 'image',
                    'logoImage' => $path,
                ],
            ],
            'is_published' => true,
            'sort_order' => 0,
        ]);

        $response = $this->getJson('/api/site-content/global');

        $response->assertOk();
        $logoUrl = $response->json('data.content.branding.logoImage');
        $this->assertIsString($logoUrl);
        $this->assertStringContainsString('/storage/site-content/global/test-logo.svg', $logoUrl);
    }

    public function test_global_branding_logo_promotes_legacy_private_uploads(): void
    {
        $path = 'site-content/global/legacy-logo.svg';
        \Illuminate\Support\Facades\Storage::disk('local')->put($path, '<svg xmlns="http://www.w3.org/2000/svg"/>');

        SiteContentPage::query()->create([
            'page_key' => 'global',
            'label' => 'Global',
            'content' => [
                'branding' => [
                    'logoMode' => 'image',
                    'logoImage' => $path,
                ],
            ],
            'is_published' => true,
            'sort_order' => 0,
        ]);

        $response = $this->getJson('/api/site-content/global');

        $response->assertOk();
        $this->assertTrue(\Illuminate\Support\Facades\Storage::disk('public')->exists($path));
        $this->assertFalse(\Illuminate\Support\Facades\Storage::disk('local')->exists($path));
        $this->assertStringContainsString('/storage/'.$path, (string) $response->json('data.content.branding.logoImage'));
    }

    public function test_site_pages_endpoint_maps_about_content(): void
    {
        SiteContentPage::query()->create([
            'page_key' => 'about',
            'label' => 'About',
            'content' => SiteContentDefaults::forPage('about'),
            'is_published' => true,
            'sort_order' => 2,
        ]);

        $response = $this->getJson('/api/site-pages/about');

        $response->assertOk();
        $response->assertJsonPath('data.slug', 'about');
        $this->assertNotEmpty($response->json('data.title'));
    }
}

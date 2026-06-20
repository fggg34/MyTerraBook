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
        $this->assertArrayHasKey('become-a-host', $data);
        $this->assertArrayHasKey('campsite-map', $data);
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

    public function test_site_content_defaults_include_seo_sections(): void
    {
        $this->seed(\Database\Seeders\SiteContentSeeder::class);

        $response = $this->getJson('/api/site-content');

        $response->assertOk();
        $data = $response->json('data');

        $this->assertArrayHasKey('seo', $data['global']);
        $this->assertSame('MyTerraBook', $data['global']['seo']['siteName']);
        $this->assertArrayHasKey('seo', $data['home']);
        $this->assertArrayHasKey('title', $data['home']['seo']);
        $this->assertArrayHasKey('description', $data['home']['seo']);
        $this->assertSame('index', $data['about']['seo']['robots']);
        $this->assertSame('Campsite Map of Iceland', $data['campsite-map']['header']['title']);
        $this->assertSame('noindex', $data['auth-login']['seo']['robots']);
    }

    public function test_homepage_reviews_section_returns_demo_when_google_not_connected(): void
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
            'content' => SiteContentDefaults::forPage('home'),
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $response = $this->getJson('/api/homepage');

        $response->assertOk();
        $response->assertJsonPath('reviewsSection.isDemo', true);
        $response->assertJsonPath('reviewsSection.source', 'demo');
        $this->assertNotEmpty($response->json('reviewsSection.reviews'));
    }

    public function test_homepage_reviews_section_falls_back_to_demo_when_google_enabled_but_unconfigured(): void
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
                'reviewsSection' => [
                    'googleEnabled' => true,
                    'googlePlaceId' => 'ChIJinvalid',
                ],
            ]),
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $response = $this->getJson('/api/homepage');

        $response->assertOk();
        $response->assertJsonPath('reviewsSection.isDemo', true);
        $response->assertJsonPath('reviewsSection.source', 'demo');
        $this->assertNotEmpty($response->json('reviewsSection.reviews'));
    }

    public function test_homepage_rent_and_how_section_images_resolve_from_storage(): void
    {
        $path = 'site-content/home/custom-card.jpg';
        \Illuminate\Support\Facades\Storage::disk('public')->put($path, 'fake-jpg');

        SiteContentPage::query()->create([
            'page_key' => 'global',
            'label' => 'Global',
            'content' => SiteContentDefaults::forPage('global'),
            'is_published' => true,
            'sort_order' => 0,
        ]);

        $defaults = SiteContentDefaults::forPage('home');
        $content = array_replace_recursive($defaults, [
            'rentSection' => [
                'cards' => [
                    array_replace($defaults['rentSection']['cards'][0], ['image' => $path]),
                ],
            ],
            'howSection' => [
                'steps' => [
                    array_replace($defaults['howSection']['steps'][0], ['image' => $path]),
                ],
            ],
        ]);

        SiteContentPage::query()->create([
            'page_key' => 'home',
            'label' => 'Home',
            'content' => $content,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $homeResponse = $this->getJson('/api/site-content');
        $homeResponse->assertOk();
        $rentImage = $homeResponse->json('data.home.rentSection.cards.0.image');
        $howImage = $homeResponse->json('data.home.howSection.steps.0.image');
        $this->assertStringContainsString('/storage/'.$path, (string) $rentImage);
        $this->assertStringContainsString('/storage/'.$path, (string) $howImage);

        $homepageResponse = $this->getJson('/api/homepage');
        $homepageResponse->assertOk();
        $this->assertStringContainsString('/storage/'.$path, (string) $homepageResponse->json('rentSection.cards.0.image'));
        $this->assertStringContainsString('/storage/'.$path, (string) $homepageResponse->json('howSection.steps.0.image'));
    }

    public function test_homepage_rent_section_returns_live_listing_stats(): void
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
            'content' => SiteContentDefaults::forPage('home'),
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $camperMain = \App\Models\MainCategory::query()->firstOrCreate(
            ['slug' => 'campervan'],
            ['name' => 'Campervan', 'is_active' => true],
        );
        $camperSub = \App\Models\SubCategory::query()->create([
            'main_category_id' => $camperMain->id,
            'name' => 'Camper',
            'is_active' => true,
            'is_search_filter' => true,
        ]);
        $camper = \App\Models\Car::query()->create([
            'name' => 'Ring Road Camper',
            'slug' => 'ring-road-camper',
            'sub_category_id' => $camperSub->id,
            'is_active' => true,
        ]);
        \App\Models\DailyFare::query()->create([
            'car_id' => $camper->id,
            'price_type_id' => \App\Models\PriceType::factory()->create()->id,
            'from_days' => 1,
            'to_days' => 30,
            'price_per_day_cents' => 8900,
        ]);

        \App\Models\GuestHouse::query()->create([
            'name' => 'Harbour Stay',
            'slug' => 'harbour-stay',
            'type' => \App\Enums\GuestHouseType::Apartment,
            'status' => \App\Enums\GuestHouseStatus::Active,
            'city' => 'Reykjavik',
            'max_guests' => 2,
            'base_price_per_night' => 11000,
        ]);

        $response = $this->getJson('/api/homepage');

        $response->assertOk();
        $response->assertJsonPath('rentSection.cards.0.listingStats.count', 1);
        $response->assertJsonPath('rentSection.cards.0.listingStats.minPriceCents', 8900);
        $response->assertJsonPath('rentSection.cards.0.listingStats.priceUnit', 'day');
        $response->assertJsonPath('rentSection.cards.2.listingStats.count', 1);
        $response->assertJsonPath('rentSection.cards.2.listingStats.minPriceCents', 11000);
        $response->assertJsonPath('rentSection.cards.2.listingStats.priceUnit', 'night');
        $response->assertJsonMissingPath('rentSection.cards.0.listingCount');

        foreach ($response->json('rentSection.cards') as $card) {
            $minPriceCents = $card['listingStats']['minPriceCents'] ?? 0;
            if (($card['listingStats']['count'] ?? 0) > 0) {
                $this->assertGreaterThanOrEqual(1000, $minPriceCents);
            }
        }
    }

    public function test_become_a_host_page_images_resolve_from_storage(): void
    {
        $path = 'site-content/become-a-host/tab.jpg';
        \Illuminate\Support\Facades\Storage::disk('public')->put($path, 'fake-jpg');

        SiteContentPage::query()->create([
            'page_key' => 'become-a-host',
            'label' => 'Become a host',
            'content' => array_replace_recursive(SiteContentDefaults::forPage('become-a-host'), [
                'howTabs' => [
                    array_replace(SiteContentDefaults::forPage('become-a-host')['howTabs'][0], ['image' => $path]),
                ],
                'features' => [
                    array_replace(SiteContentDefaults::forPage('become-a-host')['features'][0], ['image' => $path]),
                ],
                'cta' => [
                    'patternImage' => $path,
                ],
            ]),
            'is_published' => true,
            'sort_order' => 8,
        ]);

        $response = $this->getJson('/api/site-content');

        $response->assertOk();
        $this->assertStringContainsString('/storage/'.$path, (string) $response->json('data.become-a-host.howTabs.0.image'));
        $this->assertStringContainsString('/storage/'.$path, (string) $response->json('data.become-a-host.features.0.image'));
        $this->assertStringContainsString('/storage/'.$path, (string) $response->json('data.become-a-host.cta.patternImage'));
    }
}

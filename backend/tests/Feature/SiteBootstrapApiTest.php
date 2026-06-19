<?php

namespace Tests\Feature;

use App\Data\SiteContentDefaults;
use App\Enums\BlogPostStatus;
use App\Models\BlogPost;
use App\Models\SiteContentPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteBootstrapApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_bootstrap_endpoint_returns_site_pages_and_blog_posts(): void
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

        SiteContentPage::query()->create([
            'page_key' => 'about',
            'label' => 'About',
            'content' => array_replace_recursive(SiteContentDefaults::forPage('about'), [
                'hero' => ['title' => 'About MyTerraBook Live'],
            ]),
            'is_published' => true,
            'sort_order' => 2,
        ]);

        BlogPost::query()->create([
            'slug' => 'ring-road-guide',
            'title' => 'Ring Road Guide',
            'status' => BlogPostStatus::Published,
            'published_at' => now()->subDay(),
            'body' => '<p>Drive safely.</p>',
        ]);

        $response = $this->getJson('/api/bootstrap');

        $response->assertOk();
        $response->assertJsonPath('sitePages.about.title', 'About MyTerraBook Live');
        $response->assertJsonPath('blogPosts.0.slug', 'ring-road-guide');
        $response->assertJsonPath('blogPosts.0.body', '<p>Drive safely.</p>');
    }
}

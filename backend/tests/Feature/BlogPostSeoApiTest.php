<?php

namespace Tests\Feature;

use App\Enums\BlogPostStatus;
use App\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogPostSeoApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_post_show_includes_seo_fields(): void
    {
        BlogPost::query()->create([
            'slug' => 'ring-road-guide',
            'title' => 'Driving the Ring Road',
            'meta_title' => 'Ring Road Guide',
            'meta_description' => 'A complete Ring Road itinerary for Iceland.',
            'excerpt' => 'Fallback excerpt',
            'body' => '<p>Body</p>',
            'status' => BlogPostStatus::Published,
            'published_at' => now()->subDay(),
        ]);

        $response = $this->getJson('/api/blog-posts/ring-road-guide');

        $response->assertOk();
        $response->assertJsonPath('data.meta_title', 'Ring Road Guide');
        $response->assertJsonPath('data.meta_description', 'A complete Ring Road itinerary for Iceland.');
    }
}

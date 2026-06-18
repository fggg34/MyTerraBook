<?php

namespace Tests\Feature;

use App\Enums\BlogPostStatus;
use App\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class BlogPostCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_blog_post_clears_site_content_cache(): void
    {
        Cache::put('site_content.all', ['cached' => true], 3600);
        Cache::put('site_content.homepage', ['cached' => true], 3600);

        BlogPost::query()->create([
            'slug' => 'new-post',
            'title' => 'New post',
            'excerpt' => 'Excerpt',
            'body' => '<p>Body</p>',
            'status' => BlogPostStatus::Published,
            'published_at' => now(),
        ]);

        $this->assertFalse(Cache::has('site_content.all'));
        $this->assertFalse(Cache::has('site_content.homepage'));
    }

    public function test_publishing_blog_post_clears_site_content_cache(): void
    {
        $post = BlogPost::query()->create([
            'slug' => 'draft-post',
            'title' => 'Draft post',
            'excerpt' => 'Excerpt',
            'body' => '<p>Body</p>',
            'status' => BlogPostStatus::Draft,
        ]);

        Cache::put('site_content.all', ['cached' => true], 3600);
        Cache::put('site_content.homepage', ['cached' => true], 3600);

        $post->update([
            'status' => BlogPostStatus::Published,
            'published_at' => now(),
        ]);

        $this->assertFalse(Cache::has('site_content.all'));
        $this->assertFalse(Cache::has('site_content.homepage'));
    }

    public function test_updating_unrelated_blog_fields_does_not_clear_cache(): void
    {
        $post = BlogPost::query()->create([
            'slug' => 'published-post',
            'title' => 'Published post',
            'excerpt' => 'Excerpt',
            'body' => '<p>Body</p>',
            'status' => BlogPostStatus::Published,
            'published_at' => now(),
        ]);

        Cache::put('site_content.all', ['cached' => true], 3600);
        Cache::put('site_content.homepage', ['cached' => true], 3600);

        $post->update([
            'meta_title' => 'Updated SEO title',
            'meta_description' => 'Updated SEO description',
        ]);

        $this->assertTrue(Cache::has('site_content.all'));
        $this->assertTrue(Cache::has('site_content.homepage'));
    }

    public function test_deleting_blog_post_clears_site_content_cache(): void
    {
        $post = BlogPost::query()->create([
            'slug' => 'delete-me',
            'title' => 'Delete me',
            'excerpt' => 'Excerpt',
            'body' => '<p>Body</p>',
            'status' => BlogPostStatus::Published,
            'published_at' => now(),
        ]);

        Cache::put('site_content.all', ['cached' => true], 3600);
        Cache::put('site_content.homepage', ['cached' => true], 3600);

        $post->delete();

        $this->assertFalse(Cache::has('site_content.all'));
        $this->assertFalse(Cache::has('site_content.homepage'));
    }
}

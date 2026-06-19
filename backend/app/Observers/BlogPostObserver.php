<?php

namespace App\Observers;

use App\Models\BlogPost;
use App\Services\SiteContentService;

class BlogPostObserver
{
    public function __construct(
        private readonly SiteContentService $siteContent,
    ) {}

    public function created(BlogPost $post): void
    {
        $this->siteContent->clearCache();
    }

    public function updated(BlogPost $post): void
    {
        if ($this->affectsHomepageBootstrap($post)) {
            $this->siteContent->clearCache();
        }
    }

    public function deleted(BlogPost $post): void
    {
        $this->siteContent->clearCache();
    }

    private function affectsHomepageBootstrap(BlogPost $post): bool
    {
        return $post->wasChanged([
            'status',
            'published_at',
            'sort_order',
            'is_featured',
            'slug',
            'title',
            'excerpt',
            'body',
            'featured_image',
            'image_alt',
            'kicker',
            'read_time',
            'aurora',
        ]);
    }
}

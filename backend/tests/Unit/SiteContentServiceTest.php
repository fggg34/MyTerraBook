<?php

namespace Tests\Unit;

use App\Services\SiteContentService;
use Tests\TestCase;

class SiteContentServiceTest extends TestCase
{
    public function test_merge_preserves_existing_upload_when_incoming_is_empty_array(): void
    {
        $service = new SiteContentService;

        $existing = [
            'branding' => [
                'logoMode' => 'image',
                'logoImage' => 'site-content/global/logo.svg',
            ],
        ];

        $incoming = [
            'branding' => [
                'logoMode' => 'image',
                'prefix' => 'Updated',
                'logoImage' => [],
            ],
        ];

        $merged = $service->mergeSavedPageContent($existing, $incoming);
        $normalized = $service->normalizePageContent('global', $merged);

        $this->assertSame('Updated', $normalized['branding']['prefix']);
        $this->assertSame('site-content/global/logo.svg', $normalized['branding']['logoImage']);
    }

    public function test_normalize_upload_fields_converts_empty_array_and_string_to_null(): void
    {
        $service = new SiteContentService;

        $normalized = $service->normalizePageContent('global', [
            'branding' => [
                'logoImage' => [],
                'favicon' => '',
            ],
        ]);

        $this->assertNull($normalized['branding']['logoImage']);
        $this->assertNull($normalized['branding']['favicon']);
    }

    public function test_normalize_about_page_flattens_nested_body_and_uploads(): void
    {
        $service = new SiteContentService;

        $normalized = $service->normalizePageContent('about', [
            'body' => ['body' => '<p>Nested body</p>'],
            'offerings' => [
                ['label' => 'Campervans', 'image' => ['site-content/about/card.jpg']],
            ],
            'storyBlocks' => [
                ['text' => 'Chapter one', 'image' => ['site-content/about/chapter.jpg']],
            ],
        ]);

        $this->assertSame('<p>Nested body</p>', $normalized['body']);
        $this->assertSame('site-content/about/card.jpg', $normalized['offerings'][0]['image']);
        $this->assertSame('site-content/about/chapter.jpg', $normalized['storyBlocks'][0]['image']);
    }

    public function test_prepare_form_upload_state_wraps_storage_paths_for_file_upload(): void
    {
        $service = new SiteContentService;

        $prepared = $service->prepareFormUploadState([
            'rentSection' => [
                'cards' => [
                    ['image' => 'site-content/home/card.jpg'],
                ],
            ],
            'hero' => [
                'backgroundImage' => '/images/homepage/hero.jpg',
            ],
        ]);

        $this->assertSame(['site-content/home/card.jpg'], $prepared['rentSection']['cards'][0]['image']);
        $this->assertSame('/images/homepage/hero.jpg', $prepared['hero']['backgroundImage']);
    }
}

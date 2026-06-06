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
}

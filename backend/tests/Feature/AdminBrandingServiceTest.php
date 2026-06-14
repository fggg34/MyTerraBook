<?php

namespace Tests\Feature;

use App\Models\SiteContentPage;
use App\Services\Admin\AdminBrandingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminBrandingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_text_logo_html_contains_branding_parts(): void
    {
        SiteContentPage::query()->create([
            'page_key' => 'global',
            'label' => 'Global',
            'content' => [
                'branding' => [
                    'logoMode' => 'text',
                    'prefix' => 'My',
                    'accent' => 'Terra',
                    'suffix' => 'Book',
                ],
            ],
            'is_published' => true,
            'sort_order' => 0,
        ]);

        $html = app(AdminBrandingService::class)->logoHtml()->toHtml();

        $this->assertStringContainsString('My', $html);
        $this->assertStringContainsString('Terra', $html);
        $this->assertStringContainsString('Book', $html);
        $this->assertStringContainsString('tb-admin-brand-logo__mark', $html);
    }

    public function test_image_logo_html_uses_resolved_public_storage_url(): void
    {
        $path = 'site-content/global/admin-logo.svg';
        Storage::disk('public')->put($path, '<svg xmlns="http://www.w3.org/2000/svg"/>');

        SiteContentPage::query()->create([
            'page_key' => 'global',
            'label' => 'Global',
            'content' => [
                'branding' => [
                    'logoMode' => 'image',
                    'logoImage' => $path,
                    'prefix' => 'My',
                    'accent' => 'Terra',
                    'suffix' => 'Book',
                ],
            ],
            'is_published' => true,
            'sort_order' => 0,
        ]);

        $html = app(AdminBrandingService::class)->logoHtml()->toHtml();

        $this->assertStringContainsString('<img', $html);
        $this->assertStringContainsString('/storage/site-content/global/admin-logo.svg', $html);
        $this->assertStringContainsString('tb-admin-brand-logo__image', $html);
    }

    public function test_favicon_url_returns_resolved_branding_favicon(): void
    {
        $path = 'site-content/global/favicon.ico';
        Storage::disk('public')->put($path, 'fake');

        SiteContentPage::query()->create([
            'page_key' => 'global',
            'label' => 'Global',
            'content' => [
                'branding' => [
                    'logoMode' => 'text',
                    'favicon' => $path,
                ],
            ],
            'is_published' => true,
            'sort_order' => 0,
        ]);

        $faviconUrl = app(AdminBrandingService::class)->faviconUrl();

        $this->assertIsString($faviconUrl);
        $this->assertStringContainsString('/storage/site-content/global/favicon.ico', $faviconUrl);
    }
}

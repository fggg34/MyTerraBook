<?php

namespace Tests\Feature;

use App\Models\SiteContentPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FaviconRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_favicon_route_returns_no_content_when_unset(): void
    {
        $this->get('/favicon.ico')
            ->assertNoContent();
    }

    public function test_favicon_route_serves_uploaded_favicon_file(): void
    {
        $path = 'site-content/global/favicon.ico';
        Storage::disk('public')->put($path, 'fake-icon');

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

        $this->get('/favicon.ico')
            ->assertOk()
            ->assertHeader('Cache-Control', 'max-age=3600, public');
    }
}

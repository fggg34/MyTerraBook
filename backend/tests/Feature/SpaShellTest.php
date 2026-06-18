<?php

namespace Tests\Feature;

use App\Data\SiteContentDefaults;
use App\Models\SiteContentPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class SpaShellTest extends TestCase
{
    use RefreshDatabase;

    private string $indexPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->indexPath = storage_path('framework/testing/spa-index.html');
        File::ensureDirectoryExists(dirname($this->indexPath));
        File::put($this->indexPath, <<<'HTML'
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <link rel="icon" href="/backend/favicon.ico" />
    <title>MyTerraBook</title>
    <!-- MYTERRABOOK_SITE_BOOTSTRAP -->
  </head>
  <body>
    <div id="root"></div>
  </body>
</html>
HTML);

        config(['spa.index_path' => $this->indexPath]);
    }

    protected function tearDown(): void
    {
        if (File::exists($this->indexPath)) {
            File::delete($this->indexPath);
        }

        parent::tearDown();
    }

    public function test_spa_shell_injects_bootstrap_payload(): void
    {
        SiteContentPage::query()->create([
            'page_key' => 'global',
            'label' => 'Global',
            'content' => array_replace_recursive(SiteContentDefaults::forPage('global'), [
                'branding' => [
                    'logoMode' => 'text',
                    'prefix' => 'Live',
                    'accent' => 'Terra',
                    'suffix' => 'Book',
                    'favicon' => '/storage/branding/live-favicon.png',
                ],
            ]),
            'is_published' => true,
            'sort_order' => 0,
        ]);

        SiteContentPage::query()->create([
            'page_key' => 'home',
            'label' => 'Home',
            'content' => array_replace_recursive(SiteContentDefaults::forPage('home'), [
                'hero' => ['heading' => 'Live hero heading from CMS'],
            ]),
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $response = $this->get('/spa-shell');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
        $response->assertSee('window.__MYTERRABOOK_BOOTSTRAP__=', false);
        $response->assertSee('Live hero heading from CMS', false);
        $response->assertSee('"prefix":"Live"', false);
        $response->assertSee('/storage/branding/live-favicon.png', false);
        $response->assertDontSee('<!-- MYTERRABOOK_SITE_BOOTSTRAP -->', false);
    }

    public function test_spa_shell_returns_fallback_when_index_missing(): void
    {
        config(['spa.index_path' => storage_path('framework/testing/missing-index.html')]);

        $response = $this->get('/spa-shell');

        $response->assertOk();
        $response->assertSee('Storefront is temporarily unavailable', false);
    }

    public function test_spa_shell_does_not_start_a_session(): void
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

        $response = $this->get('/spa-shell');

        $response->assertOk();
        $response->assertCookieMissing(config('session.cookie'));
    }
}

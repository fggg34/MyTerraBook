<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Pages\SiteContentHub;
use App\Models\SiteContentPage;
use App\Models\User;
use Filament\Schemas\Components\Component;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SiteContentHubSaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_save_persists_branding_text_changes(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin);

        SiteContentPage::query()->create([
            'page_key' => 'global',
            'label' => 'Global',
            'content' => ['branding' => ['logoMode' => 'text', 'prefix' => 'Old']],
            'is_published' => true,
            'sort_order' => 0,
        ]);

        Livewire::test(SiteContentHub::class, ['activePageKey' => 'global'])
            ->set('data.branding.prefix', 'SavedPrefix')
            ->call('save')
            ->assertNotified();

        $branding = SiteContentPage::query()->where('page_key', 'global')->first()?->content['branding'];

        $this->assertSame('SavedPrefix', $branding['prefix'] ?? null);
    }

    public function test_google_reviews_toggle_reveals_place_id_field(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin);

        $page = app(SiteContentHub::class);
        $page->activePageKey = 'home';
        $page->mount();
        $page->data['reviewsSection']['googleEnabled'] = true;

        $toggle = $this->findSchemaField($page, 'googleEnabled');
        $placeIdField = $this->findSchemaField($page, 'googlePlaceId');

        $this->assertNotNull($toggle);
        $this->assertTrue($toggle->isLive());
        $this->assertNotNull($placeIdField);
        $this->assertTrue($placeIdField->isVisible());
    }

    /**
     * @param  list<Component>|null  $components
     */
    private function findSchemaField(SiteContentHub $page, string $needle, ?array $components = null): ?Component
    {
        $components ??= $page->getSchema('form')->getComponents();

        foreach ($components as $component) {
            if (method_exists($component, 'getStatePath') && str_contains((string) $component->getStatePath(), $needle)) {
                return $component;
            }

            if (method_exists($component, 'getChildComponents')) {
                $match = $this->findSchemaField($page, $needle, $component->getChildComponents());

                if ($match !== null) {
                    return $match;
                }
            }
        }

        return null;
    }

    public function test_save_persists_uploaded_logo_path(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin);

        SiteContentPage::query()->create([
            'page_key' => 'global',
            'label' => 'Global',
            'content' => ['branding' => ['logoMode' => 'image']],
            'is_published' => true,
            'sort_order' => 0,
        ]);

        $path = 'site-content/global/test-logo.svg';
        Storage::disk('public')->put($path, '<svg xmlns="http://www.w3.org/2000/svg"/>');

        Livewire::test(SiteContentHub::class, ['activePageKey' => 'global'])
            ->set('data.branding.logoMode', 'image')
            ->set('data.branding.logoImage', [$path])
            ->call('save')
            ->assertNotified();

        $logoImage = SiteContentPage::query()->where('page_key', 'global')->first()?->content['branding']['logoImage'] ?? null;

        $this->assertSame($path, $logoImage);
        $this->assertNotSame([], $logoImage);
    }
}

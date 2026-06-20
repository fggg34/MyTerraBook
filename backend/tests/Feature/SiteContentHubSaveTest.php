<?php

namespace Tests\Feature;

use App\Data\SiteContentDefaults;
use App\Enums\UserRole;
use App\Filament\Pages\SiteContentHub;
use App\Models\SiteContentPage;
use App\Models\User;
use App\Services\SiteContentService;
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

    public function test_save_persists_uploaded_favicon_path(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin);

        SiteContentPage::query()->create([
            'page_key' => 'global',
            'label' => 'Global',
            'content' => ['branding' => ['logoMode' => 'text']],
            'is_published' => true,
            'sort_order' => 0,
        ]);

        $path = 'site-content/global/test-favicon.png';
        Storage::disk('public')->put($path, 'fake-png');

        Livewire::test(SiteContentHub::class, ['activePageKey' => 'global'])
            ->set('data.branding.favicon', [$path])
            ->call('save')
            ->assertNotified();

        $favicon = SiteContentPage::query()->where('page_key', 'global')->first()?->content['branding']['favicon'] ?? null;

        $this->assertSame($path, $favicon);
        $this->assertNotSame([], $favicon);
    }

    public function test_save_persists_rent_section_card_image(): void
    {
        Storage::fake('public');

        $service = app(SiteContentService::class);
        $defaults = SiteContentDefaults::forPage('home');
        $baseline = $service->normalizePageContent('home', $defaults);
        $path = 'site-content/home/rent-card.jpg';
        Storage::disk('public')->put($path, 'fake-jpg');

        $incoming = [
            'rentSection' => [
                'cards' => [
                    array_replace($defaults['rentSection']['cards'][0], ['image' => [$path]]),
                ],
            ],
        ];

        $merged = $service->mergeSavedPageContent($baseline, $incoming);
        $normalized = $service->normalizePageContent('home', $merged);

        $this->assertSame($path, $normalized['rentSection']['cards'][0]['image'] ?? null);
    }

    public function test_save_persists_how_section_step_image(): void
    {
        Storage::fake('public');

        $service = app(SiteContentService::class);
        $defaults = SiteContentDefaults::forPage('home');
        $baseline = $service->normalizePageContent('home', $defaults);
        $path = 'site-content/home/how-step.jpg';
        Storage::disk('public')->put($path, 'fake-jpg');

        $incoming = [
            'howSection' => [
                'steps' => [
                    array_replace($defaults['howSection']['steps'][0], ['image' => [$path]]),
                ],
            ],
        ];

        $merged = $service->mergeSavedPageContent($baseline, $incoming);
        $normalized = $service->normalizePageContent('home', $merged);

        $this->assertSame($path, $normalized['howSection']['steps'][0]['image'] ?? null);
    }

    public function test_merge_preserves_step_image_when_incoming_upload_is_empty(): void
    {
        $service = app(SiteContentService::class);
        $defaults = SiteContentDefaults::forPage('home');
        $baseline = $service->normalizePageContent('home', array_replace_recursive($defaults, [
            'howSection' => [
                'steps' => [
                    array_replace($defaults['howSection']['steps'][0], ['image' => 'site-content/home/existing.jpg']),
                ],
            ],
        ]));

        $incoming = [
            'howSection' => [
                'steps' => [
                    array_replace($defaults['howSection']['steps'][0], ['image' => []]),
                ],
            ],
        ];

        $merged = $service->mergeSavedPageContent($baseline, $incoming);
        $normalized = $service->normalizePageContent('home', $merged);

        $this->assertSame('site-content/home/existing.jpg', $normalized['howSection']['steps'][0]['image'] ?? null);
    }

    public function test_save_persists_about_story_block_image_and_text(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin);

        $path = 'site-content/about/story-chapter.jpg';
        Storage::disk('public')->put($path, 'fake-jpg');

        Livewire::test(SiteContentHub::class, ['activePageKey' => 'about'])
            ->set('data.storyBlocks', [[
                'text' => 'A new chapter',
                'image' => [$path],
                'imageAlt' => 'Team photo',
            ]])
            ->call('save')
            ->assertNotified();

        $saved = SiteContentPage::query()->where('page_key', 'about')->first()?->content ?? [];

        $this->assertSame('A new chapter', $saved['storyBlocks'][0]['text'] ?? null);
        $this->assertSame($path, $saved['storyBlocks'][0]['image'] ?? null);
        $this->assertSame('Team photo', $saved['storyBlocks'][0]['imageAlt'] ?? null);
    }
}

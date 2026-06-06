<?php

namespace App\Filament\Pages;

use App\Data\SiteContentDefaults;
use App\Models\SiteContentPage;
use App\Services\SiteContentFormBuilder;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Livewire\Attributes\Url;
use UnitEnum;

class SiteContentHub extends Page
{
    private const GROUP_ORDER = ['Global', 'Marketing', 'Transactional', 'App'];
    protected static ?string $navigationLabel = 'Site content';

    protected static ?string $title = 'Site content';

    protected static ?string $slug = 'site-content';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Site';

    protected static ?int $navigationSort = -2;

    protected string $view = 'filament.pages.site-content-hub';

    #[Url(as: 'tab')]
    public string $activePageKey = 'global';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->ensureValidPageKey();
        $this->loadPage();
    }

    public function updatedActivePageKey(): void
    {
        $this->ensureValidPageKey();
        $this->loadPage();
    }

    public function switchTab(string $pageKey): void
    {
        if (! config("site_content.pages.{$pageKey}")) {
            return;
        }

        if ($this->activePageKey === $pageKey) {
            return;
        }

        $this->redirect(static::getUrl(['tab' => $pageKey]), navigate: true);
    }

    public function switchGroup(string $group): void
    {
        if ($this->getActiveGroup() === $group) {
            return;
        }

        $tab = collect($this->getPageTabs())->first(
            fn (array $pageTab): bool => $pageTab['group'] === $group,
        );

        if ($tab === null) {
            return;
        }

        $this->redirect(static::getUrl(['tab' => $tab['key']]), navigate: true);
    }

    public function getActiveGroup(): string
    {
        return config("site_content.pages.{$this->activePageKey}.group", 'Other');
    }

    /**
     * @return list<string>
     */
    public function getTabGroups(): array
    {
        $groups = collect($this->getPageTabs())
            ->pluck('group')
            ->unique()
            ->values();

        return collect(self::GROUP_ORDER)
            ->filter(fn (string $group): bool => $groups->contains($group))
            ->merge($groups->diff(self::GROUP_ORDER))
            ->values()
            ->all();
    }

    /**
     * @return list<array{key: string, label: string, group: string}>
     */
    public function getActiveGroupTabs(): array
    {
        $group = $this->getActiveGroup();

        return collect($this->getPageTabs())
            ->filter(fn (array $tab): bool => $tab['group'] === $group)
            ->values()
            ->all();
    }

    public function getTitle(): string
    {
        $label = config("site_content.pages.{$this->activePageKey}.label");

        return is_string($label) && $label !== ''
            ? "Site content — {$label}"
            : 'Site content';
    }

    public function loadPage(): void
    {
        unset($this->cachedSchemas['form'], $this->cachedSchemas['content']);

        $page = SiteContentPage::query()->where('page_key', $this->activePageKey)->first();
        $defaults = SiteContentDefaults::forPage($this->activePageKey);
        $service = app(\App\Services\SiteContentService::class);

        $this->data = $service->normalizePageContent(
            $this->activePageKey,
            array_replace_recursive($defaults, $page?->content ?? []),
        );
        $this->form->fill($this->data);
    }

    public function save(): void
    {
        $service = app(\App\Services\SiteContentService::class);
        $existing = SiteContentPage::query()->where('page_key', $this->activePageKey)->first();
        $defaults = SiteContentDefaults::forPage($this->activePageKey);
        $baseline = $service->normalizePageContent(
            $this->activePageKey,
            array_replace_recursive($defaults, $existing?->content ?? []),
        );

        // Persist uploads before snapshot — getStateSnapshot() skips beforeStateDehydrated().
        $dehydrateState = ['data' => $this->data];
        $this->form->callBeforeStateDehydrated($dehydrateState);

        // Snapshot avoids validating every tab when saving a single section.
        $snapshot = $this->form->getStateSnapshot();
        $incoming = $service->normalizePageContent($this->activePageKey, $snapshot);
        $data = $service->mergeSavedPageContent($baseline, $incoming);

        $meta = config("site_content.pages.{$this->activePageKey}", []);

        SiteContentPage::query()->updateOrCreate(
            ['page_key' => $this->activePageKey],
            [
                'label' => $meta['label'] ?? SiteContentDefaults::labelFor($this->activePageKey),
                'content' => $data,
                'is_published' => true,
                'sort_order' => $meta['sort_order'] ?? 99,
            ],
        );

        $service->clearCache();

        $this->syncLegacySources($data);

        $this->loadPage();

        Notification::make()
            ->title('Page saved')
            ->body($meta['label'] ?? $this->activePageKey)
            ->success()
            ->send();
    }

    /**
     * @return list<array{key: string, label: string, group: string}>
     */
    public function getPageTabs(): array
    {
        $pages = config('site_content.pages', []);

        return collect($pages)
            ->map(fn (array $config, string $key): array => [
                'key' => $key,
                'label' => $config['label'] ?? $key,
                'group' => $config['group'] ?? 'Other',
            ])
            ->sortBy(fn (array $tab): int => config("site_content.pages.{$tab['key']}.sort_order", 99))
            ->values()
            ->all();
    }

    public function previewUrl(): ?string
    {
        $route = config("site_content.pages.{$this->activePageKey}.preview_route");
        if (! is_string($route) || $route === '') {
            return null;
        }

        $frontend = rtrim((string) config('app.frontend_url', config('app.url')), '/');

        return $frontend.$route;
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        $builder = app(SiteContentFormBuilder::class);

        return $schema->components($builder->buildSectionsForPage($this->activePageKey));
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('site-content-form-'.$this->activePageKey)
                ->livewireSubmitHandler('save')
                ->extraAttributes(['novalidate' => true])
                ->footer([
                    Actions::make([
                        Action::make('save')
                            ->label('Save page')
                            ->submit('save')
                            ->action(null)
                            ->keyBindings(['mod+s']),
                    ]),
                ]),
        ]);
    }

    private function ensureValidPageKey(): void
    {
        if (! config("site_content.pages.{$this->activePageKey}")) {
            $this->activePageKey = 'global';
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function syncLegacySources(array $data): void
    {
        if ($this->activePageKey === 'global') {
            foreach (['topbar', 'header', 'footer'] as $key) {
                if (isset($data[$key])) {
                    \App\Models\HomepageSection::query()->updateOrCreate(
                        ['section_key' => $key],
                        ['content' => $data[$key], 'is_active' => true, 'sort_order' => 1],
                    );
                }
            }
        }

        $slugMap = [
            'about' => 'about',
            'faq' => 'faq',
            'contact' => 'contact',
            'terms' => 'terms',
            'privacy' => 'privacy',
            'cookies' => 'cookies',
        ];

        if (! isset($slugMap[$this->activePageKey])) {
            return;
        }

        $slug = $slugMap[$this->activePageKey];

        \App\Models\SitePage::query()->where('slug', $slug)->update([
            'title' => $data['hero']['title'] ?? null,
            'eyebrow' => $data['hero']['eyebrow'] ?? null,
            'lead' => $data['hero']['lead'] ?? null,
            'body' => $data['body'] ?? null,
            'content' => $slug === 'faq' || $slug === 'contact'
                ? array_filter([
                    'phone' => $data['helpCard']['phone'] ?? $data['phone'] ?? $data['items']['phone'] ?? null,
                    'email' => $data['helpCard']['email'] ?? $data['email'] ?? null,
                    'items' => $data['items'] ?? null,
                    'address' => $data['address'] ?? null,
                    'hours' => $data['hours'] ?? null,
                    'show_form' => $data['show_form'] ?? null,
                ], fn ($v) => $v !== null)
                : null,
        ]);
    }
}

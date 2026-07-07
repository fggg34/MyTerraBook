<?php

namespace App\Filament\Pages;

use App\Services\Admin\GlobalConfigurationService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class GlobalConfiguration extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.global-configuration';

    protected static ?string $title = 'Configuration';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $slug = 'global-configuration';

    /**
     * @var array<string, mixed>
     */
    public array $state = [];

    public function mount(GlobalConfigurationService $service): void
    {
        $this->state = $service->load();
    }

    public function save(GlobalConfigurationService $service): void
    {
        $service->save($this->state);

        Notification::make()
            ->title('Configuration saved')
            ->success()
            ->send();
    }
}

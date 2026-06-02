<?php

namespace App\Filament\Pages;

use App\Filament\Clusters\ImpactRentCluster;
use App\Services\Admin\GlobalConfigurationService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class GlobalConfiguration extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $cluster = ImpactRentCluster::class;

    protected string $view = 'filament.pages.global-configuration';

    protected static ?string $title = 'Configuration';

    protected static string|UnitEnum|null $navigationGroup = 'Platform';

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $slug = 'settings';

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

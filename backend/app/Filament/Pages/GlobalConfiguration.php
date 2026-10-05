<?php

namespace App\Filament\Pages;

use App\Services\Admin\GlobalConfigurationService;
use App\Services\Partners\GreenlightSettings;
use App\Services\Partners\GreenlightVehicleSyncService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Throwable;

class GlobalConfiguration extends Page
{
    use WithFileUploads;

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.global-configuration';

    protected static ?string $title = 'Configuration';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $slug = 'global-configuration';

    /**
     * @var array<string, mixed>
     */
    public array $state = [];

    public ?TemporaryUploadedFile $greenlightLogo = null;

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

    public function updatedGreenlightLogo(): void
    {
        $this->validate([
            'greenlightLogo' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'],
        ]);

        $path = $this->greenlightLogo->store('company-logos', 'public');
        $previous = (string) ($this->state['greenlight_logo_path'] ?? '');
        $this->state['greenlight_logo_path'] = $path;
        app(GreenlightSettings::class)->saveFromAdmin($this->state);

        if ($previous !== '' && $previous !== $path && Storage::disk('public')->exists($previous)) {
            Storage::disk('public')->delete($previous);
        }

        $this->greenlightLogo = null;

        Notification::make()
            ->title('Partner logo updated')
            ->success()
            ->send();
    }

    public function syncGreenlight(GlobalConfigurationService $service, GreenlightVehicleSyncService $sync): void
    {
        $this->state['greenlight_enabled'] = true;
        $service->save($this->state);

        try {
            $result = $sync->sync();
            Notification::make()
                ->title('Greenlight sync finished')
                ->body($result['locations'].' locations, '.$result['vehicles'].' vehicles.')
                ->success()
                ->send();
        } catch (Throwable $e) {
            Notification::make()
                ->title('Greenlight sync failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}

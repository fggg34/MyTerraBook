<?php

namespace App\Filament\Resources\RegisteredHosts\Pages;

use App\Filament\Resources\RegisteredHosts\RegisteredHostResource;
use App\Services\Partners\CarenPartnerClient;
use App\Services\Partners\CarenVehicleSyncService;
use App\Services\Partners\FoxiePartnerClient;
use App\Services\Partners\FoxieVehicleSyncService;
use App\Services\Partners\GreenlightPartnerClient;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Throwable;

class EditRegisteredHost extends EditRecord
{
    protected static string $resource = RegisteredHostResource::class;

    public function getTitle(): string
    {
        return 'Manage '.$this->record->name;
    }

    public function getSubheading(): ?string
    {
        return 'Update the host profile, connection settings and commission. For Caren or Foxie, use the save and import button to store credentials and import the fleet here.';
    }

    protected function getFormActions(): array
    {
        return [
            ...parent::getFormActions(),
            $this->carenImportAction('saveAndImportCarenVehicles'),
            $this->foxieImportAction('saveAndImportFoxieVehicles'),
        ];
    }

    protected function carenImportAction(string $name): Action
    {
        return Action::make($name)
            ->label('Save & import Caren vehicles')->icon('heroicon-o-arrow-down-tray')
            ->visible(fn (): bool => ($this->data['partner_provider'] ?? $this->record->partner_provider) === 'caren')
            ->requiresConfirmation()
            ->modalHeading('Save host and import Caren vehicles')
            ->modalDescription('Save the form and import vehicles and locations from Caren. Imported vehicles, including existing listings, are automatically published. For Konvin, enter rental ID 120.')
            ->action(function (CarenVehicleSyncService $sync): void {
                // Let validation errors return to the form rather than treating them as API failures.
                $this->save(shouldRedirect: false, shouldSendSavedNotification: false);
                $this->record->refresh();
                $this->refreshFormData(['partner_api_key', 'partner_api_username', 'partner_api_password']);
                try {
                    $result = $sync->sync($this->record);
                    $this->refreshFormData(['partner_rental_id']);
                    Notification::make()->title('Caren fleet imported')
                        ->body($result['vehicles'].' vehicles, '.$result['locations'].' locations; '.$result['skipped'].' test entries skipped. New vehicles are drafts.')
                        ->success()->send();
                } catch (Throwable $exception) {
                    Notification::make()->title('Host saved, but Caren import failed')
                        ->body($exception->getMessage().' Your settings are saved; you can retry this button.')
                        ->danger()->persistent()->send();
                }
            });
    }

    protected function foxieImportAction(string $name): Action
    {
        return Action::make($name)
            ->label('Save & import Foxie vehicles')->icon('heroicon-o-arrow-down-tray')
            ->visible(fn (): bool => ($this->data['partner_provider'] ?? $this->record->partner_provider) === 'foxie')
            ->requiresConfirmation()
            ->modalHeading('Save host and import Foxie vehicles')
            ->modalDescription('Save the form, turn the Foxie connection on, and import vehicles and locations. The Foxie API address is set for you. Imported vehicles are published. This does not create a reservation.')
            ->action(function (FoxieVehicleSyncService $sync): void {
                $typedKey = trim((string) ($this->data['partner_api_key'] ?? ''));
                $this->data['partner_provider'] = 'foxie';
                $this->data['partner_api_enabled'] = true;
                $this->data['partner_api_base_url'] = FoxiePartnerClient::BASE_URL;
                if ($typedKey !== '') {
                    $this->data['partner_api_key'] = $typedKey;
                }
                $this->save(shouldRedirect: false, shouldSendSavedNotification: false);
                $this->record->refresh();
                $this->record->forceFill([
                    'partner_provider' => 'foxie',
                    'partner_api_enabled' => true,
                    'partner_api_base_url' => FoxiePartnerClient::BASE_URL,
                    ...($typedKey !== '' ? ['partner_api_key' => $typedKey] : []),
                ])->save();
                $this->refreshFormData(['partner_api_key', 'partner_api_enabled', 'partner_api_base_url', 'partner_provider']);
                if (blank($this->record->partner_api_key)) {
                    Notification::make()->title('Host saved, but Foxie import failed')
                        ->body('Enter the Foxie API key, then use this button again.')
                        ->danger()->persistent()->send();

                    return;
                }
                try {
                    $result = $sync->sync($this->record->fresh());
                    Notification::make()->title('Foxie fleet imported')
                        ->body($result['vehicles'].' vehicles, '.$result['locations'].' locations; '.$result['skipped'].' test entries skipped. Imported vehicles are published.')
                        ->success()->send();
                } catch (Throwable $exception) {
                    Notification::make()->title('Host saved, but Foxie import failed')
                        ->body($exception->getMessage().' Your settings are saved; you can retry this button.')
                        ->danger()->persistent()->send();
                }
            });
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('testCarenConnection')
                ->label('Test Caren connection')->icon('heroicon-o-signal')->color('gray')
                ->visible(fn (): bool => $this->record->partner_provider === 'caren')
                ->action(function (CarenPartnerClient $client): void {
                    try {
                        $catalogue = $client->catalogue($this->record);
                        Notification::make()->title('Caren connection succeeded')
                            ->body($catalogue['rental']['Name'].': '.count($catalogue['vehicles']).' vehicle classes found.')
                            ->success()->send();
                    } catch (Throwable $exception) {
                        Notification::make()->title('Caren connection failed')->body($exception->getMessage())->danger()->send();
                    }
                }),
            $this->carenImportAction('syncCarenVehicles'),
            Action::make('testFoxieConnection')
                ->label('Test Foxie connection')->icon('heroicon-o-signal')->color('gray')
                ->visible(fn (): bool => $this->record->partner_provider === 'foxie')
                ->action(function (FoxiePartnerClient $client): void {
                    try {
                        $catalogue = $client->catalogue($this->record);
                        Notification::make()->title('Foxie connection succeeded')
                            ->body(count($catalogue['vehicles']).' vehicles and '.count($catalogue['locations']).' locations found.')
                            ->success()->send();
                    } catch (Throwable $exception) {
                        Notification::make()->title('Foxie connection failed')->body($exception->getMessage())->danger()->send();
                    }
                }),
            $this->foxieImportAction('syncFoxieVehicles'),
            Action::make('testConnection')
                ->label('Test connection')
                ->icon('heroicon-o-signal')
                ->color('gray')
                ->tooltip('Test the saved API URL and key')
                ->visible(fn (): bool => $this->record->partner_provider === 'greenlight')
                ->action(function (GreenlightPartnerClient $client): void {
                    try {
                        $locations = $client->locations();

                        Notification::make()
                            ->title('Greenlight connection succeeded')
                            ->body(count($locations).' location'.(count($locations) === 1 ? '' : 's').' found.')
                            ->success()
                            ->send();
                    } catch (Throwable $exception) {
                        Notification::make()
                            ->title('Greenlight connection failed')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            ViewAction::make(),
        ];
    }
}

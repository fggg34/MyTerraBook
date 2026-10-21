<?php

namespace App\Filament\Resources\RegisteredHosts\Pages;

use App\Filament\Resources\RegisteredHosts\RegisteredHostResource;
use App\Services\Partners\CarenPartnerClient;
use App\Services\Partners\CarenVehicleSyncService;
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
        return 'Update the host profile, connection settings and commission. For Caren, use Save & import Caren vehicles to save your credentials and import the fleet here.';
    }

    protected function getFormActions(): array
    {
        return [
            ...parent::getFormActions(),
            $this->carenImportAction('saveAndImportCarenVehicles'),
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

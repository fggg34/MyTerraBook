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
        return 'Update the host profile, connection settings and commission. Save changes before testing the API.';
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
            Action::make('syncCarenVehicles')
                ->label('Import Caren vehicles')->icon('heroicon-o-arrow-down-tray')
                ->visible(fn (): bool => $this->record->partner_provider === 'caren')
                ->requiresConfirmation()
                ->modalDescription('Import the saved Caren rental catalogue. New vehicles are inactive drafts pending pricing and booking integration. Existing vehicle review status is preserved.')
                ->action(function (CarenVehicleSyncService $sync): void {
                    try {
                        $result = $sync->sync($this->record);
                        Notification::make()->title('Caren fleet imported')
                            ->body($result['vehicles'].' vehicles, '.$result['locations'].' locations; '.$result['skipped'].' test entries skipped. New vehicles are drafts.')
                            ->success()->send();
                    } catch (Throwable $exception) {
                        Notification::make()->title('Caren import failed')->body($exception->getMessage())->danger()->send();
                    }
                }),
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

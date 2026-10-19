<?php

namespace App\Filament\Resources\RegisteredHosts\Pages;

use App\Filament\Resources\RegisteredHosts\RegisteredHostResource;
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

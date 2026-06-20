<?php

namespace App\Filament\GuestHouse\Resources\GuestHouseResource\Pages;

use App\Filament\GuestHouse\Resources\Concerns\NormalizesGuestHouseFormData;
use App\Filament\GuestHouse\Resources\GuestHouseResource;
use App\Models\GuestHouse;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditGuestHouse extends EditRecord
{
    use NormalizesGuestHouseFormData;

    protected static string $resource = GuestHouseResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $this->normalizeGuestHouseFormDataForFill($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->normalizeGuestHouseFormDataForSave($data);
    }

    protected function afterSave(): void
    {
        $this->syncGalleryImages($this->record);
        $this->syncThumbnailFromGallery($this->record);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('backToList')
                ->label('Back')
                ->icon(Heroicon::OutlinedArrowLeft)
                ->color('gray')
                ->url(fn (): string => GuestHouseResource::getUrl('index'))
                ->tooltip('Return to guesthouses list'),
            Action::make('previewStorefront')
                ->label('Preview')
                ->icon(Heroicon::OutlinedEye)
                ->color('gray')
                ->url(fn (): string => config('app.frontend_url').'/guesthouses/'.$this->getRecord()->slug)
                ->openUrlInNewTab()
                ->tooltip('Open this guesthouse on the site (new tab).'),
            DeleteAction::make(),
        ];
    }
}

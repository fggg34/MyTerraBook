<?php

namespace App\Filament\GuestHouse\Resources\GuestHouseResource\Pages;

use App\Filament\GuestHouse\Resources\Concerns\NormalizesGuestHouseFormData;
use App\Filament\GuestHouse\Resources\GuestHouseResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

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
        return [DeleteAction::make()];
    }
}

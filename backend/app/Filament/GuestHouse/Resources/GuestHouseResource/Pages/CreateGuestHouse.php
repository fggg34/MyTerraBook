<?php

namespace App\Filament\GuestHouse\Resources\GuestHouseResource\Pages;

use App\Filament\GuestHouse\Resources\Concerns\NormalizesGuestHouseFormData;
use App\Filament\GuestHouse\Resources\GuestHouseResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGuestHouse extends CreateRecord
{
    use NormalizesGuestHouseFormData;

    protected static string $resource = GuestHouseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->normalizeGuestHouseFormDataForSave($data);
    }

    protected function afterCreate(): void
    {
        $this->syncGalleryImages($this->record);
        $this->syncThumbnailFromGallery($this->record);
    }
}

<?php

namespace App\Filament\Resources\Locations\Pages;

use App\Filament\Resources\Locations\LocationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditLocation extends EditRecord
{
    protected static string $resource = LocationResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    public function getPageClasses(): array
    {
        return [
            ...parent::getPageClasses(),
            'ir-location-form-page',
            'ir-location-form-page--edit',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

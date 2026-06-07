<?php

namespace App\Filament\Resources\Locations\Pages;

use App\Filament\Resources\Locations\LocationResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateLocation extends CreateRecord
{
    protected static string $resource = LocationResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    public function getPageClasses(): array
    {
        return [
            ...parent::getPageClasses(),
            'ir-location-form-page',
            'ir-location-form-page--create',
        ];
    }
}

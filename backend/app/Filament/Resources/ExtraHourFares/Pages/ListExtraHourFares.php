<?php

namespace App\Filament\Resources\ExtraHourFares\Pages;

use App\Filament\Resources\ExtraHourFares\ExtraHourFareResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExtraHourFares extends ListRecords
{
    protected static string $resource = ExtraHourFareResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

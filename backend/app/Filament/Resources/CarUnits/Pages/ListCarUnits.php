<?php

namespace App\Filament\Resources\CarUnits\Pages;

use App\Filament\Resources\CarUnits\CarUnitResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCarUnits extends ListRecords
{
    protected static string $resource = CarUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

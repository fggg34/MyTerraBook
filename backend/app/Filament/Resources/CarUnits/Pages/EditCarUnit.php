<?php

namespace App\Filament\Resources\CarUnits\Pages;

use App\Filament\Resources\CarUnits\CarUnitResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCarUnit extends EditRecord
{
    protected static string $resource = CarUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

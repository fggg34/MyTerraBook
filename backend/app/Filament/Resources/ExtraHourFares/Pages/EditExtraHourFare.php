<?php

namespace App\Filament\Resources\ExtraHourFares\Pages;

use App\Filament\Resources\ExtraHourFares\ExtraHourFareResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditExtraHourFare extends EditRecord
{
    protected static string $resource = ExtraHourFareResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

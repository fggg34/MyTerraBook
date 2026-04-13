<?php

namespace App\Filament\Resources\HourlyFares\Pages;

use App\Filament\Resources\HourlyFares\HourlyFareResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHourlyFare extends EditRecord
{
    protected static string $resource = HourlyFareResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

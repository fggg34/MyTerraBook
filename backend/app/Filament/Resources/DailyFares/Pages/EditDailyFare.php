<?php

namespace App\Filament\Resources\DailyFares\Pages;

use App\Filament\Resources\DailyFares\DailyFareResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDailyFare extends EditRecord
{
    protected static string $resource = DailyFareResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

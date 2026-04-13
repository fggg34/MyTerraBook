<?php

namespace App\Filament\Resources\DailyFares\Pages;

use App\Filament\Resources\DailyFares\DailyFareResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDailyFares extends ListRecords
{
    protected static string $resource = DailyFareResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

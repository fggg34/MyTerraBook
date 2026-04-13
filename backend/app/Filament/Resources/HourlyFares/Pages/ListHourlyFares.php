<?php

namespace App\Filament\Resources\HourlyFares\Pages;

use App\Filament\Resources\HourlyFares\HourlyFareResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHourlyFares extends ListRecords
{
    protected static string $resource = HourlyFareResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

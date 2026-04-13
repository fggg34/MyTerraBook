<?php

namespace App\Filament\Resources\OutOfHoursFees\Pages;

use App\Filament\Resources\OutOfHoursFees\OutOfHoursFeeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOutOfHoursFees extends ListRecords
{
    protected static string $resource = OutOfHoursFeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

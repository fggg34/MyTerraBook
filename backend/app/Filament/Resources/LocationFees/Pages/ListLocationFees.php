<?php

namespace App\Filament\Resources\LocationFees\Pages;

use App\Filament\Resources\LocationFees\LocationFeeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLocationFees extends ListRecords
{
    protected static string $resource = LocationFeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

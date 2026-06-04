<?php

namespace App\Filament\GuestHouse\Resources\GuestHouseResource\Pages;

use App\Filament\GuestHouse\Resources\GuestHouseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGuestHouses extends ListRecords
{
    protected static string $resource = GuestHouseResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}

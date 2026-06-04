<?php

namespace App\Filament\GuestHouse\Resources\GuestHouseAmenityResource\Pages;

use App\Filament\GuestHouse\Resources\GuestHouseAmenityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageGuestHouseAmenities extends ManageRecords
{
    protected static string $resource = GuestHouseAmenityResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}

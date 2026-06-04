<?php

namespace App\Filament\GuestHouse\Resources\GuestHouseResource\Pages;

use App\Filament\GuestHouse\Resources\GuestHouseResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGuestHouse extends EditRecord
{
    protected static string $resource = GuestHouseResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}

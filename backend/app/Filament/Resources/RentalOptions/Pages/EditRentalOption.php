<?php

namespace App\Filament\Resources\RentalOptions\Pages;

use App\Filament\Resources\RentalOptions\RentalOptionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRentalOption extends EditRecord
{
    protected static string $resource = RentalOptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\OutOfHoursFees\Pages;

use App\Filament\Resources\OutOfHoursFees\OutOfHoursFeeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOutOfHoursFee extends EditRecord
{
    protected static string $resource = OutOfHoursFeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

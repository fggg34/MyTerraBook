<?php

namespace App\Filament\Resources\LocationFees\Pages;

use App\Filament\Resources\LocationFees\LocationFeeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLocationFee extends EditRecord
{
    protected static string $resource = LocationFeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

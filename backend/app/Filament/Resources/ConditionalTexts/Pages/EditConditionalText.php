<?php

namespace App\Filament\Resources\ConditionalTexts\Pages;

use App\Filament\Resources\ConditionalTexts\ConditionalTextResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditConditionalText extends EditRecord
{
    protected static string $resource = ConditionalTextResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

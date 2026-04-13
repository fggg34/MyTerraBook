<?php

namespace App\Filament\Resources\ConditionalTexts\Pages;

use App\Filament\Resources\ConditionalTexts\ConditionalTextResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListConditionalTexts extends ListRecords
{
    protected static string $resource = ConditionalTextResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

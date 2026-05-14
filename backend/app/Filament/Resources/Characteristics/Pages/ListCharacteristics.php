<?php

namespace App\Filament\Resources\Characteristics\Pages;

use App\Filament\Resources\Characteristics\CharacteristicResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCharacteristics extends ListRecords
{
    protected static string $resource = CharacteristicResource::class;

    public function getPageClasses(): array
    {
        return [
            ...parent::getPageClasses(),
            'ir-characteristics-page',
            'ir-characteristics-page--list',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

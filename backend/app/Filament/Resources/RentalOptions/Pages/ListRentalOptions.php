<?php

namespace App\Filament\Resources\RentalOptions\Pages;

use App\Filament\Resources\RentalOptions\RentalOptionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRentalOptions extends ListRecords
{
    protected static string $resource = RentalOptionResource::class;

    public function getPageClasses(): array
    {
        return [
            ...parent::getPageClasses(),
            'ir-rental-options-page',
            'ir-rental-options-page--list',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

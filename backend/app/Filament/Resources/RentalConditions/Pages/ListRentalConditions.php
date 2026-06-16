<?php

namespace App\Filament\Resources\RentalConditions\Pages;

use App\Filament\Resources\RentalConditions\RentalConditionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRentalConditions extends ListRecords
{
    protected static string $resource = RentalConditionResource::class;

    public function getPageClasses(): array
    {
        return [
            ...parent::getPageClasses(),
            'ir-rental-conditions-page',
            'ir-rental-conditions-page--list',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

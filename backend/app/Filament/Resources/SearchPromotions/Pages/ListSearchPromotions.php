<?php

namespace App\Filament\Resources\SearchPromotions\Pages;

use App\Filament\Resources\SearchPromotions\SearchPromotionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSearchPromotions extends ListRecords
{
    protected static string $resource = SearchPromotionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

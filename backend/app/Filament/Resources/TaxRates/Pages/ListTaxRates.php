<?php

namespace App\Filament\Resources\TaxRates\Pages;

use App\Filament\Concerns\EmbedsCatalogTable;
use App\Filament\Resources\PriceTypes\PriceTypeResource;
use App\Filament\Resources\TaxRates\TaxRateResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTaxRates extends ListRecords
{
    use EmbedsCatalogTable;

    protected static string $resource = TaxRateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('newPriceType')
                ->label('New price type')
                ->color('gray')
                ->url(PriceTypeResource::getUrl('create')),
            CreateAction::make()->label('New tax rate'),
        ];
    }
}

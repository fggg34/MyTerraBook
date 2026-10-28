<?php

namespace App\Filament\Resources\PriceTypes\Pages;

use App\Filament\Resources\PriceTypes\PriceTypeResource;
use App\Filament\Resources\TaxRates\TaxRateResource;
use Filament\Resources\Pages\ListRecords;

class ListPriceTypes extends ListRecords
{
    protected static string $resource = PriceTypeResource::class;

    public function mount(): void
    {
        parent::mount();
        $this->redirect(TaxRateResource::getUrl('index').'#types-of-price');
    }
}

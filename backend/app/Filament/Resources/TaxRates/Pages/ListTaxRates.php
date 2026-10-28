<?php

namespace App\Filament\Resources\TaxRates\Pages;

use App\Filament\Resources\PriceTypes\PriceTypeResource;
use App\Filament\Resources\TaxRates\TaxRateResource;
use App\Models\PriceType;
use App\Models\TaxRate;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListTaxRates extends ListRecords
{
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

    public function getFooter(): ?View
    {
        return view('filament.pricing.tax-and-price-types', [
            'taxRates' => TaxRate::query()
                ->withCount(['priceTypes', 'locationFees', 'outOfHoursFees', 'rentalOptions'])
                ->orderBy('name')
                ->get(),
            'priceTypes' => PriceType::query()->with('taxRate')->orderBy('name')->get(),
        ]);
    }
}

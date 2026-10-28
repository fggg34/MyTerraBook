<?php

namespace App\Filament\Resources\Coupons\Pages;

use App\Filament\Concerns\EmbedsCatalogTable;
use App\Filament\Resources\Coupons\CouponResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCoupons extends ListRecords
{
    use EmbedsCatalogTable;

    protected static string $resource = CouponResource::class;

    public function mount(): void
    {
        parent::mount();
        $this->tableFilters = array_replace_recursive([
            'type' => ['value' => null],
            'status' => ['value' => null],
        ], $this->tableFilters ?? []);
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

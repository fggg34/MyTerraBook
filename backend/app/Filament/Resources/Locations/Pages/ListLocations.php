<?php

namespace App\Filament\Resources\Locations\Pages;

use App\Filament\Concerns\EmbedsCatalogTable;
use App\Filament\Resources\Locations\LocationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLocations extends ListRecords
{
    use EmbedsCatalogTable;

    protected static string $resource = LocationResource::class;

    public function mount(): void
    {
        parent::mount();
        $this->tableFilters = array_replace_recursive([
            'country_code' => ['value' => null],
        ], $this->tableFilters ?? []);
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

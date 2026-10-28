<?php

namespace App\Filament\Resources\Characteristics\Pages;

use App\Filament\Concerns\EmbedsCatalogTable;
use App\Filament\Resources\Characteristics\CharacteristicResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCharacteristics extends ListRecords
{
    use EmbedsCatalogTable;

    protected static string $resource = CharacteristicResource::class;

    public function mount(): void
    {
        parent::mount();
        $this->tableFilters = array_replace_recursive([
            'group' => ['value' => null],
        ], $this->tableFilters ?? []);
    }

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

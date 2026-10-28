<?php

namespace App\Filament\Resources\CustomFields\Pages;

use App\Filament\Concerns\EmbedsCatalogTable;
use App\Filament\Resources\CustomFields\CustomFieldResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCustomFields extends ListRecords
{
    use EmbedsCatalogTable;

    protected static string $resource = CustomFieldResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

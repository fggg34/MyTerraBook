<?php

namespace App\Filament\Resources\CustomFields\Pages;

use App\Filament\Concerns\EmbedsCatalogTable;
use App\Filament\Resources\CustomFields\CustomFieldResource;
use App\Models\CustomField;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCustomFields extends ListRecords
{
    use EmbedsCatalogTable;

    protected static string $resource = CustomFieldResource::class;

    public function toggleCustomField(int $id, string $field): void
    {
        if (! in_array($field, ['is_required', 'is_email', 'is_active'], true)) {
            return;
        }
        $record = CustomField::query()->find($id);
        if ($record === null) {
            return;
        }
        $record->update([$field => ! $record->{$field}]);
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

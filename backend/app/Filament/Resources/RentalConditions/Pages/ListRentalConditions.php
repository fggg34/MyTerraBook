<?php

namespace App\Filament\Resources\RentalConditions\Pages;

use App\Filament\Concerns\EmbedsCatalogTable;
use App\Filament\Resources\RentalConditions\RentalConditionResource;
use App\Models\RentalCondition;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRentalConditions extends ListRecords
{
    use EmbedsCatalogTable;

    protected static string $resource = RentalConditionResource::class;

    public function toggleActive(int $id): void
    {
        $record = RentalCondition::query()->findOrFail($id);
        abort_unless(RentalConditionResource::canEdit($record), 403);
        $record->update(['is_active' => ! $record->is_active]);
    }

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

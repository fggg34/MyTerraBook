<?php

namespace App\Filament\Resources\CarUnits\Pages;

use App\Filament\Concerns\EmbedsCatalogTable;
use App\Filament\Resources\CarUnits\CarUnitResource;
use App\Models\CarUnit;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCarUnits extends ListRecords
{
    use EmbedsCatalogTable;

    protected static string $resource = CarUnitResource::class;

    public function mount(): void
    {
        parent::mount();
        $this->tableFilters = array_replace_recursive([
            'car_id' => ['value' => null],
        ], $this->tableFilters ?? []);
    }

    public function toggleActive(int $id): void
    {
        $record = CarUnit::query()->findOrFail($id);
        abort_unless(CarUnitResource::canEdit($record), 403);
        $record->update(['is_active' => ! $record->is_active]);
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

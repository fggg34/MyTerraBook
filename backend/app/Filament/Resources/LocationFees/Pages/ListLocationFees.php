<?php

namespace App\Filament\Resources\LocationFees\Pages;

use App\Filament\Concerns\EmbedsCatalogTable;
use App\Filament\Resources\LocationFees\LocationFeeResource;
use App\Models\LocationFee;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLocationFees extends ListRecords
{
    use EmbedsCatalogTable;

    protected static string $resource = LocationFeeResource::class;

    public function mount(): void
    {
        parent::mount();
        $this->tableFilters = array_replace_recursive([
            'pickup_location_id' => ['value' => null],
            'dropoff_location_id' => ['value' => null],
        ], $this->tableFilters ?? []);
    }

    public function toggleActive(int $id): void
    {
        $record = LocationFee::query()->findOrFail($id);
        abort_unless(LocationFeeResource::canEdit($record), 403);
        $record->update(['is_active' => ! $record->is_active]);
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

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

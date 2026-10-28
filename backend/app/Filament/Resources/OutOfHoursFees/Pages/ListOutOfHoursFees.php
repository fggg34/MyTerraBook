<?php

namespace App\Filament\Resources\OutOfHoursFees\Pages;

use App\Filament\Concerns\EmbedsCatalogTable;
use App\Filament\Resources\OutOfHoursFees\OutOfHoursFeeResource;
use App\Models\OutOfHoursFee;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOutOfHoursFees extends ListRecords
{
    use EmbedsCatalogTable;

    protected static string $resource = OutOfHoursFeeResource::class;

    public function mount(): void
    {
        parent::mount();
        $this->tableFilters = array_replace_recursive([
            'applies_to' => ['value' => null],
        ], $this->tableFilters ?? []);
    }

    public function toggleActive(int $id): void
    {
        $record = OutOfHoursFee::query()->findOrFail($id);
        abort_unless(OutOfHoursFeeResource::canEdit($record), 403);
        $record->update(['is_active' => ! $record->is_active]);
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

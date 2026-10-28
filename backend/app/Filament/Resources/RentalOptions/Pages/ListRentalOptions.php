<?php

namespace App\Filament\Resources\RentalOptions\Pages;

use App\Filament\Concerns\EmbedsCatalogTable;
use App\Filament\Concerns\ReordersBySortOrder;
use App\Filament\Resources\RentalOptions\RentalOptionResource;
use App\Models\RentalOption;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRentalOptions extends ListRecords
{
    use EmbedsCatalogTable;
    use ReordersBySortOrder;

    protected static string $resource = RentalOptionResource::class;

    public function reorderVisible(array $order): void
    {
        $record = RentalOption::query()->whereKey($order[0] ?? null)->first();
        abort_unless($record && RentalOptionResource::canEdit($record), 403);
        $this->reorderBySortOrder(RentalOption::class, $order);
    }

    public function toggleMandatory(int $id): void
    {
        $record = RentalOption::query()->findOrFail($id);
        abort_unless(RentalOptionResource::canEdit($record), 403);
        $record->update(['is_mandatory' => ! $record->is_mandatory]);
    }

    public function getPageClasses(): array
    {
        return [
            ...parent::getPageClasses(),
            'ir-rental-options-page',
            'ir-rental-options-page--list',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\BookingRestrictions\Pages;

use App\Filament\Concerns\EmbedsCatalogTable;
use App\Filament\Resources\BookingRestrictions\BookingRestrictionResource;
use App\Models\BookingRestriction;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBookingRestrictions extends ListRecords
{
    use EmbedsCatalogTable;

    protected static string $resource = BookingRestrictionResource::class;

    public function toggleActive(int $id): void
    {
        $record = BookingRestriction::query()->findOrFail($id);
        abort_unless(BookingRestrictionResource::canEdit($record), 403);
        $record->update(['is_active' => ! $record->is_active]);
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

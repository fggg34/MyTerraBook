<?php

namespace App\Filament\Resources\RentalOptions\Pages;

use App\Filament\Resources\RentalOptions\RentalOptionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\Schema as DatabaseSchema;

class EditRentalOption extends EditRecord
{
    protected static string $resource = RentalOptionResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['slug'] = (string) ($this->record->slug ?? '');

        if (DatabaseSchema::hasColumn('rental_options', 'sort_order')) {
            $data['sort_order'] = isset($data['sort_order'])
                ? (int) $data['sort_order']
                : (int) ($this->record->sort_order ?? 0);
        } else {
            unset($data['sort_order']);
        }

        if (! DatabaseSchema::hasColumn('rental_options', 'min_rental_days')) {
            unset($data['min_rental_days']);
        }

        if (! DatabaseSchema::hasColumn('rental_options', 'max_rental_days')) {
            unset($data['max_rental_days']);
        }

        return $data;
    }

    public function getPageClasses(): array
    {
        return [
            ...parent::getPageClasses(),
            'ir-rental-options-page',
            'ir-rental-options-page--edit',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\RentalOptions\Pages;

use App\Filament\Resources\RentalOptions\RentalOptionResource;
use App\Models\RentalOption;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\Schema as DatabaseSchema;

class CreateRentalOption extends CreateRecord
{
    protected static string $resource = RentalOptionResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $name = trim((string) ($data['name'] ?? ''));

        $data['slug'] = RentalOption::uniqueSlugFromName($name);
        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        if (DatabaseSchema::hasColumn('rental_options', 'sort_order')) {
            $requestedSortOrder = isset($data['sort_order']) ? (int) $data['sort_order'] : null;
            $data['sort_order'] = ($requestedSortOrder !== null && $requestedSortOrder > 0)
                ? $requestedSortOrder
                : ((int) RentalOption::query()->max('sort_order')) + 1;
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
            'ir-rental-options-page--create',
        ];
    }
}

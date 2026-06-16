<?php

namespace App\Filament\Resources\RentalConditions\Pages;

use App\Filament\Resources\RentalConditions\RentalConditionResource;
use App\Models\RentalCondition;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateRentalCondition extends CreateRecord
{
    protected static string $resource = RentalConditionResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $name = trim((string) ($data['name'] ?? ''));
        $title = trim((string) ($data['title'] ?? ''));

        $data['slug'] = RentalCondition::uniqueSlugFromName($name !== '' ? $name : $title);

        $requestedSortOrder = isset($data['sort_order']) ? (int) $data['sort_order'] : null;
        $data['sort_order'] = ($requestedSortOrder !== null && $requestedSortOrder > 0)
            ? $requestedSortOrder
            : ((int) RentalCondition::query()->max('sort_order')) + 1;

        return $data;
    }

    public function getPageClasses(): array
    {
        return [
            ...parent::getPageClasses(),
            'ir-rental-conditions-page',
            'ir-rental-conditions-page--create',
        ];
    }
}

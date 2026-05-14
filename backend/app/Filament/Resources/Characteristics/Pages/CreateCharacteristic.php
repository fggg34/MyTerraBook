<?php

namespace App\Filament\Resources\Characteristics\Pages;

use App\Filament\Resources\Characteristics\CharacteristicResource;
use App\Models\Characteristic;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\Schema as DatabaseSchema;

class CreateCharacteristic extends CreateRecord
{
    protected static string $resource = CharacteristicResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $name = trim((string) ($data['name'] ?? ''));

        $data['slug'] = Characteristic::uniqueSlugFromName($name);

        if (DatabaseSchema::hasColumn('characteristics', 'sort_order')) {
            $requestedSortOrder = isset($data['sort_order']) ? (int) $data['sort_order'] : null;
            $data['sort_order'] = ($requestedSortOrder !== null && $requestedSortOrder > 0)
                ? $requestedSortOrder
                : ((int) Characteristic::query()->max('sort_order')) + 1;
        } else {
            unset($data['sort_order']);
        }

        return $data;
    }

    public function getPageClasses(): array
    {
        return [
            ...parent::getPageClasses(),
            'ir-characteristics-page',
            'ir-characteristics-page--create',
        ];
    }
}

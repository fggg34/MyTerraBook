<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use App\Models\MainCategory;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $name = trim((string) ($data['name'] ?? ''));

        $data['slug'] = MainCategory::uniqueSlugFromName($name);
        $data['is_active'] = true;
        $data['sort_order'] = ((int) MainCategory::query()->max('sort_order')) + 1;

        return $data;
    }

    public function getPageClasses(): array
    {
        return [
            ...parent::getPageClasses(),
            'ir-categories-page',
            'ir-categories-page--create',
        ];
    }
}

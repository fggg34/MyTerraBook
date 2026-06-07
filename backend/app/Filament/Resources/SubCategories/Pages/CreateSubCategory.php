<?php

namespace App\Filament\Resources\SubCategories\Pages;

use App\Filament\Resources\SubCategories\SubCategoryResource;
use App\Models\SubCategory;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateSubCategory extends CreateRecord
{
    protected static string $resource = SubCategoryResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $name = trim((string) ($data['name'] ?? ''));

        $data['slug'] = SubCategory::uniqueSlugFromName($name);
        $data['is_active'] = true;
        $data['sort_order'] = ((int) SubCategory::query()
            ->where('main_category_id', $data['main_category_id'] ?? null)
            ->max('sort_order')) + 1;

        return $data;
    }

    public function getPageClasses(): array
    {
        return [
            ...parent::getPageClasses(),
            'ir-sub-categories-page',
            'ir-sub-categories-page--create',
        ];
    }
}

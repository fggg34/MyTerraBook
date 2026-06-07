<?php

namespace App\Filament\Resources\SubCategories\Pages;

use App\Filament\Resources\SubCategories\SubCategoryResource;
use App\Models\SubCategory;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CreateSubCategory extends CreateRecord
{
    protected static string $resource = SubCategoryResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $name = trim((string) ($data['name'] ?? ''));
        $baseSlug = Str::slug($name);
        $trashed = SubCategory::onlyTrashed()->where('slug', $baseSlug)->first();

        $data['slug'] = $trashed?->slug ?? SubCategory::uniqueSlugFromName($name);
        $data['is_active'] = true;
        $data['sort_order'] = ((int) SubCategory::withTrashed()
            ->where('main_category_id', $data['main_category_id'] ?? null)
            ->max('sort_order')) + 1;

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $name = trim((string) ($data['name'] ?? ''));
        $baseSlug = Str::slug($name);
        $trashed = SubCategory::onlyTrashed()->where('slug', $baseSlug)->first();

        if ($trashed !== null) {
            $trashed->restore();
            $trashed->update([
                'main_category_id' => $data['main_category_id'],
                'name' => $data['name'],
                'description' => $data['description'] ?? $trashed->description,
                'is_active' => true,
                'is_search_filter' => $data['is_search_filter'] ?? $trashed->is_search_filter,
                'sort_order' => $data['sort_order'] ?? $trashed->sort_order,
            ]);

            return $trashed;
        }

        return parent::handleRecordCreation($data);
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

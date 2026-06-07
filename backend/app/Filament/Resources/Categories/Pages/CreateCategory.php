<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use App\Models\MainCategory;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $name = trim((string) ($data['name'] ?? ''));
        $baseSlug = Str::slug($name);
        $trashed = MainCategory::onlyTrashed()->where('slug', $baseSlug)->first();

        $data['slug'] = $trashed?->slug ?? MainCategory::uniqueSlugFromName($name);
        $data['is_active'] = true;
        $data['sort_order'] = ((int) MainCategory::withTrashed()->max('sort_order')) + 1;

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $name = trim((string) ($data['name'] ?? ''));
        $baseSlug = Str::slug($name);
        $trashed = MainCategory::onlyTrashed()->where('slug', $baseSlug)->first();

        if ($trashed !== null) {
            $trashed->restore();
            $trashed->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? $trashed->description,
                'is_active' => true,
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
            'ir-categories-page',
            'ir-categories-page--create',
        ];
    }
}

<?php

namespace App\Filament\Resources\SubCategories\Pages;

use App\Filament\Resources\SubCategories\SubCategoryResource;
use App\Models\SubCategory;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditSubCategory extends EditRecord
{
    protected static string $resource = SubCategoryResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $name = trim((string) ($data['name'] ?? ''));

        $data['slug'] = SubCategory::uniqueSlugFromName($name, (int) $this->record->getKey());
        $data['is_active'] = (bool) ($this->record->is_active ?? true);
        $data['sort_order'] = (int) ($this->record->sort_order ?? 0);

        return $data;
    }

    public function getPageClasses(): array
    {
        return [
            ...parent::getPageClasses(),
            'ir-sub-categories-page',
            'ir-sub-categories-page--edit',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}

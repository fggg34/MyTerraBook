<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use App\Models\Category;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $name = trim((string) ($data['name'] ?? ''));

        $data['slug'] = Category::uniqueSlugFromName($name, (int) $this->record->getKey());
        $data['is_active'] = (bool) ($this->record->is_active ?? true);
        $data['sort_order'] = (int) ($this->record->sort_order ?? 0);

        return $data;
    }

    public function getPageClasses(): array
    {
        return [
            ...parent::getPageClasses(),
            'ir-categories-page',
            'ir-categories-page--edit',
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

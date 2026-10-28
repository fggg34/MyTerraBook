<?php

namespace App\Filament\Resources\SubCategories\Pages;

use App\Filament\Concerns\EmbedsCatalogTable;
use App\Filament\Resources\SubCategories\SubCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSubCategories extends ListRecords
{
    use EmbedsCatalogTable;

    protected static string $resource = SubCategoryResource::class;

    public function mount(): void
    {
        parent::mount();
        $this->tableFilters = array_replace_recursive([
            'main_category_id' => ['value' => null],
            'trashed' => ['value' => null],
        ], $this->tableFilters ?? []);
    }

    public function setTrashView(string $mode): void
    {
        $this->tableFilters['trashed']['value'] = match ($mode) {
            'with' => true,
            'only' => false,
            default => null,
        };
        $this->resetPage();
    }

    public function toggleSearchFilter(int $id): void
    {
        $record = \App\Models\SubCategory::withTrashed()->findOrFail($id);
        abort_unless(SubCategoryResource::canEdit($record), 403);
        $record->update(['is_search_filter' => ! $record->is_search_filter]);
    }

    public function getPageClasses(): array
    {
        return [
            ...parent::getPageClasses(),
            'ir-sub-categories-page',
            'ir-sub-categories-page--list',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

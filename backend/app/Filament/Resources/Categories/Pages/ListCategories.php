<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Concerns\EmbedsCatalogTable;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\SubCategories\SubCategoryResource;
use App\Models\SubCategory;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Livewire\Attributes\Url;

class ListCategories extends ListRecords
{
    use EmbedsCatalogTable;

    protected static string $resource = CategoryResource::class;

    #[Url(as: 'main')]
    public ?string $mainCategory = null;

    public function mount(): void
    {
        parent::mount();
        $this->tableFilters = array_replace_recursive([
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
        $this->resetPage('subs');
    }

    public function toggleSearchFilter(int $id): void
    {
        $record = SubCategory::withTrashed()->findOrFail($id);
        abort_unless(SubCategoryResource::canEdit($record), 403);
        $record->update(['is_search_filter' => ! $record->is_search_filter]);
    }

    public function moveSub(int $id, string $direction): void
    {
        $record = SubCategory::withTrashed()->findOrFail($id);
        abort_unless(SubCategoryResource::canEdit($record), 403);
        $neighbor = SubCategory::withTrashed()
            ->where('main_category_id', $record->main_category_id)
            ->when($direction === 'up', fn ($query) => $query->where('sort_order', '<', $record->sort_order)->orderByDesc('sort_order'))
            ->when($direction !== 'up', fn ($query) => $query->where('sort_order', '>', $record->sort_order)->orderBy('sort_order'))
            ->first();

        if (! $neighbor) {
            return;
        }

        $current = (int) $record->sort_order;
        $record->update(['sort_order' => (int) $neighbor->sort_order]);
        $neighbor->update(['sort_order' => $current]);
    }

    public function getPageClasses(): array
    {
        return [
            ...parent::getPageClasses(),
            'ir-categories-page',
            'ir-categories-page--list',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

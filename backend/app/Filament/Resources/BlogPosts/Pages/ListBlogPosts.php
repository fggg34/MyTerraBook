<?php

namespace App\Filament\Resources\BlogPosts\Pages;

use App\Filament\Concerns\EmbedsCatalogTable;
use App\Filament\Resources\BlogPosts\BlogPostResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBlogPosts extends ListRecords
{
    use EmbedsCatalogTable;

    protected static string $resource = BlogPostResource::class;

    public function mount(): void
    {
        parent::mount();
        $this->tableFilters = array_replace_recursive([
            'status' => ['value' => null],
            'kicker' => ['value' => null],
            'is_featured' => ['value' => null],
        ], $this->tableFilters ?? []);
    }

    public function setPostTab(string $tab): void
    {
        $this->tableFilters['status']['value'] = match ($tab) {
            'published' => 'published',
            'draft' => 'draft',
            default => null,
        };
        $this->tableFilters['is_featured']['value'] = $tab === 'featured' ? true : null;
    }

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}

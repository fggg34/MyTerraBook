<?php

namespace App\Filament\Resources\NewsletterSubscribers\Pages;

use App\Filament\Concerns\EmbedsCatalogTable;
use App\Filament\Resources\NewsletterSubscribers\NewsletterSubscriberResource;
use App\Models\NewsletterSubscriber;
use Filament\Resources\Pages\ListRecords;

class ListNewsletterSubscribers extends ListRecords
{
    use EmbedsCatalogTable;

    protected static string $resource = NewsletterSubscriberResource::class;

    public function mount(): void
    {
        parent::mount();
        $this->tableFilters = array_replace_recursive([
            'source' => ['value' => null],
            'is_active' => ['value' => null],
        ], $this->tableFilters ?? []);
    }

    public function getSubheading(): ?string
    {
        $active = NewsletterSubscriber::query()->where('is_active', true)->count();
        $total = NewsletterSubscriber::query()->count();

        return "{$active} active · {$total} total";
    }
}

<?php

namespace App\Filament\Resources\NewsletterSubscribers\Pages;

use App\Filament\Resources\NewsletterSubscribers\NewsletterSubscriberResource;
use App\Models\NewsletterSubscriber;
use Filament\Resources\Pages\ListRecords;

class ListNewsletterSubscribers extends ListRecords
{
    protected static string $resource = NewsletterSubscriberResource::class;

    public function getSubheading(): ?string
    {
        $active = NewsletterSubscriber::query()->where('is_active', true)->count();
        $total = NewsletterSubscriber::query()->count();

        return "{$active} active · {$total} total";
    }
}

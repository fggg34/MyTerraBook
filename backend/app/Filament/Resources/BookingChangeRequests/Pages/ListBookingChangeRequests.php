<?php

namespace App\Filament\Resources\BookingChangeRequests\Pages;

use App\Enums\BookingChangeRequestStatus;
use App\Filament\Concerns\EmbedsCatalogTable;
use App\Filament\Resources\BookingChangeRequests\BookingChangeRequestResource;
use App\Models\BookingChangeRequest;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListBookingChangeRequests extends ListRecords
{
    use EmbedsCatalogTable;

    protected static string $resource = BookingChangeRequestResource::class;

    public function mount(): void
    {
        parent::mount();
        $this->tableFilters = array_replace_recursive([
            'status' => ['value' => null],
            'type' => ['value' => null],
        ], $this->tableFilters ?? []);
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
                ->badge(BookingChangeRequest::query()->count()),
            'pending' => Tab::make('Open')
                ->badge(BookingChangeRequest::query()->where('status', BookingChangeRequestStatus::Pending)->count())
                ->modifyQueryUsing(fn ($query) => $query->where('status', BookingChangeRequestStatus::Pending)),
            'applied' => Tab::make('Applied')
                ->badge(BookingChangeRequest::query()->where('status', BookingChangeRequestStatus::Applied)->count())
                ->modifyQueryUsing(fn ($query) => $query->where('status', BookingChangeRequestStatus::Applied)),
            'rejected' => Tab::make('Rejected')
                ->badge(BookingChangeRequest::query()->where('status', BookingChangeRequestStatus::Rejected)->count())
                ->modifyQueryUsing(fn ($query) => $query->where('status', BookingChangeRequestStatus::Rejected)),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'all';
    }
}

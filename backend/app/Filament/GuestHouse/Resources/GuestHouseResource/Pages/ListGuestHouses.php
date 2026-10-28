<?php

namespace App\Filament\GuestHouse\Resources\GuestHouseResource\Pages;

use App\Enums\GuestHouseStatus;
use App\Filament\GuestHouse\Resources\GuestHouseResource;
use App\Filament\Pages\ListingReviewPage;
use App\Models\GuestHouse;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;

class ListGuestHouses extends ListRecords
{
    protected static string $resource = GuestHouseResource::class;

    public function getTabs(): array
    {
        $pendingCount = GuestHouse::query()->where('status', GuestHouseStatus::PendingReview)->count();

        return [
            'all' => Tab::make('All')
                ->badge(GuestHouse::query()->count() ?: null),
            'active' => Tab::make('Active')
                ->badge(GuestHouse::query()->where('status', GuestHouseStatus::Active)->count() ?: null)
                ->modifyQueryUsing(fn ($query) => $query->where('status', GuestHouseStatus::Active)),
            'pending_review' => Tab::make('Pending review')
                ->badge($pendingCount ?: null)
                ->modifyQueryUsing(fn ($query) => $query->where('status', GuestHouseStatus::PendingReview)),
            'inactive' => Tab::make('Inactive')
                ->badge(GuestHouse::query()->where('status', GuestHouseStatus::Inactive)->count() ?: null)
                ->modifyQueryUsing(fn ($query) => $query->where('status', GuestHouseStatus::Inactive)),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'all';
    }

    protected function getHeaderActions(): array
    {
        return [
            ListingReviewPage::makeApprovalQueueHeaderAction(),
            CreateAction::make()->label('New guest house'),
        ];
    }
}

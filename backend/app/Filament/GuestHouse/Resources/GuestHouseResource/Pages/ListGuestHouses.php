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
            'pending_review' => Tab::make('Pending review')
                ->badge($pendingCount ?: null)
                ->modifyQueryUsing(fn ($query) => $query->where('status', GuestHouseStatus::PendingReview)),
            'all' => Tab::make('All'),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return GuestHouse::query()->where('status', GuestHouseStatus::PendingReview)->exists()
            ? 'pending_review'
            : 'all';
    }

    protected function getHeaderActions(): array
    {
        return [
            ListingReviewPage::makeApprovalQueueHeaderAction(),
            CreateAction::make(),
        ];
    }
}

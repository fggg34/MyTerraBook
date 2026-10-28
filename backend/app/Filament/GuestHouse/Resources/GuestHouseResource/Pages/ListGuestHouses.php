<?php

namespace App\Filament\GuestHouse\Resources\GuestHouseResource\Pages;

use App\Enums\GuestHouseStatus;
use App\Filament\GuestHouse\Resources\GuestHouseResource;
use App\Filament\Pages\ListingReviewPage;
use App\Models\GuestHouse;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Schema;
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
                ->badge((string) GuestHouse::query()->count()),
            'active' => Tab::make('Active')
                ->badge((string) GuestHouse::query()->where('status', GuestHouseStatus::Active)->count())
                ->modifyQueryUsing(fn ($query) => $query->where('status', GuestHouseStatus::Active)),
            'pending_review' => Tab::make('Pending review')
                ->badge((string) $pendingCount)
                ->modifyQueryUsing(fn ($query) => $query->where('status', GuestHouseStatus::PendingReview)),
            'inactive' => Tab::make('Inactive')
                ->badge((string) GuestHouse::query()->where('status', GuestHouseStatus::Inactive)->count())
                ->modifyQueryUsing(fn ($query) => $query->where('status', GuestHouseStatus::Inactive)),
        ];
    }

    public function mount(): void
    {
        parent::mount();

        $this->tableFilters = array_replace_recursive([
            'type' => ['value' => null],
            'city' => ['value' => null],
            'user_id' => ['value' => null],
        ], $this->tableFilters ?? []);
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'all';
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            EmbeddedTable::make(),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            ListingReviewPage::makeApprovalQueueHeaderAction(),
            CreateAction::make()->label('New guest house'),
        ];
    }
}

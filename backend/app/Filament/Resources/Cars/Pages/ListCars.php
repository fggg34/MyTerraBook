<?php

namespace App\Filament\Resources\Cars\Pages;

use App\Enums\ListingApprovalStatus;
use App\Filament\Pages\ListingReviewPage;
use App\Filament\Resources\Cars\CarResource;
use App\Filament\Resources\DailyFares\DailyFareResource;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Car;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;

class ListCars extends ListRecords
{
    protected static string $resource = CarResource::class;

    public function getTabs(): array
    {
        $pendingCount = Car::query()->where('listing_status', ListingApprovalStatus::PendingReview)->count();

        return [
            'pending_review' => Tab::make('Pending review')
                ->badge($pendingCount ?: null)
                ->modifyQueryUsing(fn ($query) => $query->where('listing_status', ListingApprovalStatus::PendingReview)),
            'all' => Tab::make('All'),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return Car::query()->where('listing_status', ListingApprovalStatus::PendingReview)->exists()
            ? 'pending_review'
            : 'all';
    }

    public function getPageClasses(): array
    {
        return [
            ...parent::getPageClasses(),
            'ir-cars-page',
            'ir-cars-page--list',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('listingApprovals')
                ->label('Approval queue')
                ->icon(Heroicon::OutlinedClipboardDocumentCheck)
                ->color('warning')
                ->url(ListingReviewPage::getUrl())
                ->badge(fn (): ?string => ListingReviewPage::getNavigationBadge()),
            Action::make('editViewFares')
                ->label('Edit/View Fares')
                ->icon(Heroicon::OutlinedCurrencyDollar)
                ->color('gray')
                ->url(DailyFareResource::getUrl('index'))
                ->tooltip('Open Fares Table'),
            Action::make('carsCalendar')
                ->label('Cars Calendar')
                ->icon(Heroicon::OutlinedCalendarDays)
                ->color('gray')
                ->url(OrderResource::getUrl('index'))
                ->tooltip('Open Orders (calendar-style scheduling from bookings)'),
            CreateAction::make(),
        ];
    }
}

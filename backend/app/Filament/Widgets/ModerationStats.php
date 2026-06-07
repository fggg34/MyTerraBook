<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\ListingReviewPage;
use App\Filament\Resources\ListingReviews\ListingReviewResource;
use App\Services\Admin\AdminDashboardService;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ModerationStats extends StatsOverviewWidget
{
    protected static ?int $sort = 5;

    protected ?string $heading = 'Moderation queue';

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '60s';

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $data = app(AdminDashboardService::class)->snapshot();
        $moderation = $data['moderation'];
        $volume = $data['volume'];
        $revenue = $data['revenue'];

        $pendingApprovals = $moderation['pending_listing_approvals'];
        $unapprovedReviews = $moderation['unapproved_reviews'];

        return [
            Stat::make('Pending listing approvals', (string) $pendingApprovals)
                ->description($pendingApprovals > 0 ? 'Action required' : 'No pending listings')
                ->descriptionColor($pendingApprovals > 0 ? 'danger' : 'success')
                ->icon(Heroicon::OutlinedClipboardDocumentCheck)
                ->color($pendingApprovals > 0 ? 'warning' : 'success')
                ->url(ListingReviewPage::getUrl()),

            Stat::make('Unapproved reviews', (string) $unapprovedReviews)
                ->description($unapprovedReviews > 0 ? 'Awaiting moderation' : 'All reviews approved')
                ->descriptionColor($unapprovedReviews > 0 ? 'warning' : 'success')
                ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                ->color($unapprovedReviews > 0 ? 'warning' : 'success')
                ->url(ListingReviewResource::getUrl('index')),

            Stat::make('Confirmed orders (30d)', (string) $volume['confirmed_orders_30d'])
                ->description('Avg. order €'.$revenue['average_order_value_30d'])
                ->icon(Heroicon::OutlinedCheckBadge)
                ->color('primary'),

            Stat::make('Total car orders', (string) $volume['total_car_orders'])
                ->description('+'.$volume['car_orders_this_month'].' this month')
                ->icon(Heroicon::OutlinedDocumentText)
                ->color('gray'),
        ];
    }
}

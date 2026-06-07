<?php

namespace App\Services\Admin;

use App\Enums\GuestHouseBookingStatus;
use App\Enums\GuestHouseStatus;
use App\Enums\ListingApprovalStatus;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Car;
use App\Models\GuestHouse;
use App\Models\GuestHouseBooking;
use App\Models\GuestHouseReview;
use App\Models\ListingReview;
use App\Models\NewsletterSubscriber;
use App\Models\Order;
use App\Models\User;
use App\Support\Money;
use Carbon\Carbon;

class AdminDashboardService
{
    public function __construct(
        private readonly AdminReportsService $reportsService,
        private readonly TrackingStatisticsService $trackingService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function snapshot(): array
    {
        $now = Carbon::now();
        $from30d = $now->copy()->subDays(30)->startOfDay();
        $to30d = $now->copy()->endOfDay();

        $reports = $this->reportsService->forPeriod($from30d, $to30d);
        $tracking = $this->trackingService->forPeriod($from30d, $to30d);

        $carRevenueCents = (int) Order::query()
            ->where('order_status', OrderStatus::Confirmed)
            ->sum('total_cents');

        $ghRevenueCents = (int) GuestHouseBooking::query()
            ->whereIn('status', [
                GuestHouseBookingStatus::Confirmed,
                GuestHouseBookingStatus::Completed,
            ])
            ->sum('total_amount');

        $totalRevenueCents = $carRevenueCents + $ghRevenueCents;

        $thisMonthStart = $now->copy()->startOfMonth();
        $lastMonthStart = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();

        $thisMonthCents = $this->platformRevenueBetween($thisMonthStart, $now);
        $lastMonthCents = $this->platformRevenueBetween($lastMonthStart, $lastMonthEnd);
        $monthChangePercent = $this->percentChange($lastMonthCents, $thisMonthCents);

        $activeRentals = Order::query()
            ->where('order_status', OrderStatus::Confirmed)
            ->where('pickup_at', '<=', $now)
            ->where('dropoff_at', '>=', $now)
            ->count();

        $today = $now->toDateString();
        $activeStays = GuestHouseBooking::query()
            ->where('status', GuestHouseBookingStatus::Confirmed)
            ->whereDate('check_in', '<=', $today)
            ->whereDate('check_out', '>=', $today)
            ->count();

        $pendingCarOrders = Order::query()
            ->where('order_status', OrderStatus::Pending)
            ->count();

        $pendingGhBookings = GuestHouseBooking::query()
            ->where('status', GuestHouseBookingStatus::Pending)
            ->count();

        $totalCarOrders = Order::query()->count();
        $carOrdersThisMonth = Order::query()
            ->where('created_at', '>=', $thisMonthStart)
            ->count();

        $totalGhBookings = GuestHouseBooking::query()->count();
        $ghBookingsThisMonth = GuestHouseBooking::query()
            ->where('created_at', '>=', $thisMonthStart)
            ->count();

        $activeCars = Car::query()->where('is_active', true)->count();
        $activeGuestHouses = GuestHouse::query()
            ->where('status', GuestHouseStatus::Active)
            ->count();

        $customers = User::query()->where('role', UserRole::Customer)->count();
        $hosts = User::query()->where('role', UserRole::Host)->count();

        $pendingListingApprovals = Car::query()
            ->where('listing_status', ListingApprovalStatus::PendingReview)
            ->count()
            + GuestHouse::query()
                ->where('status', GuestHouseStatus::PendingReview)
                ->count();

        $unapprovedReviews = ListingReview::query()
            ->where('is_approved', false)
            ->count()
            + GuestHouseReview::query()
                ->where('is_approved', false)
                ->count();

        $newsletterSubscribers = NewsletterSubscriber::query()
            ->where('is_active', true)
            ->count();

        $newSubscribersThisMonth = NewsletterSubscriber::query()
            ->where('created_at', '>=', $thisMonthStart)
            ->count();

        $ghOccupancyRate = $this->guestHouseOccupancyRate($now);

        return [
            'revenue' => [
                'total_cents' => $totalRevenueCents,
                'total_formatted' => Money::formatDecimalFromCents($totalRevenueCents),
                'this_month_cents' => $thisMonthCents,
                'this_month_formatted' => Money::formatDecimalFromCents($thisMonthCents),
                'month_change_percent' => $monthChangePercent,
                'average_order_value_30d' => $reports['revenue_summary']['average_order_value'],
                'gh_occupancy_rate' => $ghOccupancyRate,
                'sparkline' => $this->revenueSparkline(),
            ],
            'operations' => [
                'active_rentals' => $activeRentals,
                'active_stays' => $activeStays,
                'pending_car_orders' => $pendingCarOrders,
                'pending_gh_bookings' => $pendingGhBookings,
                'active_rentals_sparkline' => $this->activeRentalsSparkline(),
                'active_stays_sparkline' => $this->activeStaysSparkline(),
            ],
            'volume' => [
                'total_car_orders' => $totalCarOrders,
                'car_orders_this_month' => $carOrdersThisMonth,
                'total_gh_bookings' => $totalGhBookings,
                'gh_bookings_this_month' => $ghBookingsThisMonth,
                'confirmed_orders_30d' => $reports['revenue_summary']['confirmed_orders'],
                'orders_sparkline' => $this->ordersSparkline(),
            ],
            'traffic' => [
                'visitors_30d' => $tracking['average_values']['total_visitors'],
                'conversion_rate_30d' => $tracking['average_values']['average_conversion_rate'],
                'visitors_sparkline' => $this->visitorsSparkline($tracking),
                'newsletter_subscribers' => $newsletterSubscribers,
                'new_subscribers_this_month' => $newSubscribersThisMonth,
            ],
            'catalog' => [
                'active_cars' => $activeCars,
                'active_guest_houses' => $activeGuestHouses,
                'customers' => $customers,
                'hosts' => $hosts,
            ],
            'moderation' => [
                'pending_listing_approvals' => $pendingListingApprovals,
                'unapproved_reviews' => $unapprovedReviews,
            ],
            'top_countries' => array_slice($reports['top_countries'], 0, 5),
        ];
    }

    private function platformRevenueBetween(Carbon $from, Carbon $to): int
    {
        $carCents = (int) Order::query()
            ->where('order_status', OrderStatus::Confirmed)
            ->whereBetween('created_at', [$from, $to])
            ->sum('total_cents');

        $ghCents = (int) GuestHouseBooking::query()
            ->whereIn('status', [
                GuestHouseBookingStatus::Confirmed,
                GuestHouseBookingStatus::Completed,
            ])
            ->whereBetween('created_at', [$from, $to])
            ->sum('total_amount');

        return $carCents + $ghCents;
    }

    private function percentChange(int $previous, int $current): float
    {
        if ($previous === 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function guestHouseOccupancyRate(Carbon $now): float
    {
        $confirmedNights = (int) GuestHouseBooking::query()
            ->where('status', GuestHouseBookingStatus::Confirmed)
            ->whereMonth('check_in', $now->month)
            ->whereYear('check_in', $now->year)
            ->sum('nights');

        $daysInMonth = $now->daysInMonth;
        $activeHouses = max(1, GuestHouse::query()
            ->where('status', GuestHouseStatus::Active)
            ->count());

        return min(100, round(($confirmedNights / ($daysInMonth * $activeHouses)) * 100, 1));
    }

    /**
     * @return array<int, float>
     */
    private function activeRentalsSparkline(): array
    {
        $values = [];

        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::now()->subDays($i);
            $values[] = (float) Order::query()
                ->where('order_status', OrderStatus::Confirmed)
                ->where('pickup_at', '<=', $day->copy()->endOfDay())
                ->where('dropoff_at', '>=', $day->copy()->startOfDay())
                ->count();
        }

        return $values;
    }

    /**
     * @return array<int, float>
     */
    private function activeStaysSparkline(): array
    {
        $values = [];

        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::now()->subDays($i)->toDateString();
            $values[] = (float) GuestHouseBooking::query()
                ->where('status', GuestHouseBookingStatus::Confirmed)
                ->whereDate('check_in', '<=', $day)
                ->whereDate('check_out', '>=', $day)
                ->count();
        }

        return $values;
    }

    /**
     * @return array<int, float>
     */
    private function revenueSparkline(): array
    {
        $values = [];

        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::now()->subDays($i);
            $carCents = (int) Order::query()
                ->where('order_status', OrderStatus::Confirmed)
                ->whereDate('created_at', $day)
                ->sum('total_cents');
            $ghCents = (int) GuestHouseBooking::query()
                ->whereIn('status', [
                    GuestHouseBookingStatus::Confirmed,
                    GuestHouseBookingStatus::Completed,
                ])
                ->whereDate('created_at', $day)
                ->sum('total_amount');

            $values[] = ($carCents + $ghCents) / 100;
        }

        return $values;
    }

    /**
     * @return array<int, float>
     */
    private function ordersSparkline(): array
    {
        $values = [];

        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::now()->subDays($i);
            $values[] = (float) Order::query()->whereDate('created_at', $day)->count();
        }

        return $values;
    }

    /**
     * @param  array<string, mixed>  $tracking
     * @return array<int, float>
     */
    private function visitorsSparkline(array $tracking): array
    {
        $rates = $tracking['conversion_rates'] ?? [];

        if ($rates !== []) {
            return array_map(
                fn (array $row): float => (float) ($row['visitors'] ?? 0),
                array_slice(array_reverse($rates), 0, 7),
            );
        }

        return array_fill(0, 7, 0.0);
    }

    /**
     * Multi-series chart data for the dashboard welcome header (trading-style overview).
     *
     * @return array<string, mixed>
     */
    public function operationsOverviewChart(int $days = 30): array
    {
        $labels = [];
        $rentalsData = [];
        $staysData = [];
        $revenueData = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $day = Carbon::now()->subDays($i);
            $labels[] = $day->format('M j');

            $rentalsData[] = (float) Order::query()
                ->where('order_status', OrderStatus::Confirmed)
                ->where('pickup_at', '<=', $day->copy()->endOfDay())
                ->where('dropoff_at', '>=', $day->copy()->startOfDay())
                ->count();

            $dayString = $day->toDateString();
            $staysData[] = (float) GuestHouseBooking::query()
                ->where('status', GuestHouseBookingStatus::Confirmed)
                ->whereDate('check_in', '<=', $dayString)
                ->whereDate('check_out', '>=', $dayString)
                ->count();

            $carCents = (int) Order::query()
                ->where('order_status', OrderStatus::Confirmed)
                ->whereDate('created_at', $day)
                ->sum('total_cents');
            $ghCents = (int) GuestHouseBooking::query()
                ->whereIn('status', [
                    GuestHouseBookingStatus::Confirmed,
                    GuestHouseBookingStatus::Completed,
                ])
                ->whereDate('created_at', $day)
                ->sum('total_amount');

            $revenueData[] = round(($carCents + $ghCents) / 100, 2);
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Daily revenue (€)',
                    'data' => $revenueData,
                    'yAxisID' => 'y1',
                    'order' => 0,
                    'borderColor' => 'rgba(34, 197, 94, 0.85)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.12)',
                    'fill' => true,
                    'tension' => 0.35,
                    'pointRadius' => 0,
                    'pointHoverRadius' => 5,
                    'pointHitRadius' => 12,
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Active rentals',
                    'data' => $rentalsData,
                    'yAxisID' => 'y',
                    'order' => 1,
                    'borderColor' => 'rgba(14, 165, 233, 0.95)',
                    'backgroundColor' => 'transparent',
                    'fill' => false,
                    'tension' => 0.3,
                    'pointRadius' => 0,
                    'pointHoverRadius' => 5,
                    'pointHitRadius' => 12,
                    'borderWidth' => 2.5,
                ],
                [
                    'label' => 'Guest stays',
                    'data' => $staysData,
                    'yAxisID' => 'y',
                    'order' => 2,
                    'borderColor' => 'rgba(139, 92, 246, 0.95)',
                    'backgroundColor' => 'transparent',
                    'fill' => false,
                    'tension' => 0.3,
                    'pointRadius' => 0,
                    'pointHoverRadius' => 5,
                    'pointHitRadius' => 12,
                    'borderWidth' => 2.5,
                ],
            ],
        ];
    }
}

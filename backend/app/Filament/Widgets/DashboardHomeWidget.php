<?php

namespace App\Filament\Widgets;

use App\Enums\GuestHouseBookingStatus;
use App\Enums\GuestHouseStatus;
use App\Enums\ListingApprovalStatus;
use App\Enums\OrderStatus;
use App\Filament\GuestHouse\Resources\GuestHouseBookingResource;
use App\Filament\Pages\GlobalConfiguration;
use App\Filament\Pages\ListingReviewPage;
use App\Filament\Pages\OrdersCalendar;
use App\Filament\Pages\TrackingStatistics;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Car;
use App\Models\GuestHouse;
use App\Models\GuestHouseBooking;
use App\Models\Order;
use App\Models\Setting;
use App\Services\Admin\AdminDashboardService;
use App\Services\SitePreviewService;
use App\Support\AdminTableBadgeColors;
use Filament\Widgets\Widget;
use Throwable;

class DashboardHomeWidget extends Widget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.dashboard-home';

    public string $orderTab = 'all';

    public function setOrderTab(string $tab): void
    {
        if (in_array($tab, ['all', 'cars', 'stays'], true)) {
            $this->orderTab = $tab;
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        try {
            return $this->board();
        } catch (Throwable) {
            return $this->emptyBoard();
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function board(): array
    {
        $snapshot = app(AdminDashboardService::class)->snapshot();
        $currency = Setting::getValue('shop.currency', []);
        $name = (string) data_get($currency, 'name', 'Euro');
        $symbol = (string) data_get($currency, 'symbol', 'EUR');
        $code = (string) data_get($currency, 'code', 'EUR');
        $decimal = (string) data_get($currency, 'decimal_separator', '.');
        $thousand = (string) data_get($currency, 'thousand_separator', ',');
        $decimals = (int) data_get($currency, 'decimals', 2);

        $pendingOrders = Order::query()
            ->where('order_status', OrderStatus::Pending)
            ->latest()
            ->limit(4)
            ->get(['reference', 'total_cents']);
        $pendingCents = (int) Order::query()
            ->where('order_status', OrderStatus::Pending)
            ->sum('total_cents');

        $pendingHouses = GuestHouse::query()->where('status', GuestHouseStatus::PendingReview)->count();
        $pendingVehicles = Car::query()->where('listing_status', ListingApprovalStatus::PendingReview)->count();

        $adminEmail = trim((string) data_get(Setting::getValue('shop.admin_email', ['email' => '']), 'email', ''));
        $comingSoon = app(SitePreviewService::class)->isComingSoonEnabled();
        $currencyMismatch = (strcasecmp($name, 'Euro') === 0 && strcasecmp($code, 'EUR') !== 0)
            || (strcasecmp($symbol, 'kr') === 0 && strcasecmp($name, 'Euro') === 0);

        $settings = GlobalConfiguration::getUrl();
        $ordersUrl = OrderResource::getUrl('index');
        $calendarUrl = OrdersCalendar::getUrl();
        $analyticsUrl = TrackingStatistics::getUrl();
        $approvalsUrl = ListingReviewPage::getUrl();

        $attention = [];
        if ($pendingOrders->isNotEmpty()) {
            $attention[] = [
                'tone' => 'warn',
                'icon' => 'icon-attn-car.svg',
                'title' => $snapshot['operations']['pending_car_orders'].' '.($snapshot['operations']['pending_car_orders'] === 1 ? 'car order' : 'car orders').' waiting for confirmation',
                'meta' => $pendingOrders->pluck('reference')->filter()->implode(' · '),
                'action' => 'Review',
                'url' => $ordersUrl,
                'todo' => true,
            ];
        }
        if ($currencyMismatch) {
            $attention[] = [
                'tone' => 'bad',
                'icon' => 'icon-attn-alert.svg',
                'title' => 'Currency is set to “'.$name.'” with symbol “'.$symbol.'” and code '.$code,
                'meta' => 'Settings › Prices & payments',
                'action' => 'Fix',
                'url' => $settings.'?tab=prices',
                'todo' => true,
            ];
        }
        if ($adminEmail === '') {
            $attention[] = [
                'tone' => 'bad',
                'icon' => 'icon-attn-mail.svg',
                'title' => 'Admin e-mail is empty',
                'meta' => 'Order alerts are not delivered',
                'action' => 'Fix',
                'url' => $settings.'?tab=shop',
                'todo' => true,
            ];
        }
        if ($comingSoon) {
            $attention[] = [
                'tone' => 'warn',
                'icon' => 'icon-attn-globe.svg',
                'title' => 'Coming Soon mode is on',
                'meta' => 'Visitors see the Under construction page',
                'action' => 'Manage',
                'url' => $settings.'?tab=shop',
                'todo' => true,
            ];
        }

        $pendingListings = $pendingHouses + $pendingVehicles;
        $attention[] = [
            'tone' => $pendingListings > 0 ? 'warn' : 'info',
            'icon' => 'icon-attn-shield.svg',
            'title' => $pendingListings > 0
                ? $pendingListings.' listings waiting for approval'
                : 'Listing approvals queue is clear',
            'meta' => $pendingHouses.' guest houses · '.$pendingVehicles.' vehicles',
            'action' => 'Open',
            'url' => $approvalsUrl,
            'todo' => $pendingListings > 0,
        ];

        $chart = app(AdminDashboardService::class)->operationsOverviewChart();
        $revenue = $chart['datasets'][0]['data'] ?? [];
        $max = max(1, ...array_map('floatval', $revenue ?: [0]));
        $bars = [];
        foreach ($revenue as $value) {
            $height = (float) $value <= 0 ? 4 : max(8, (int) round(((float) $value / $max) * 72));
            $bars[] = ['height' => $height, 'active' => (float) $value > 0];
        }
        $labels = $chart['labels'] ?? [];

        $cars = Order::query()->with('car')->latest()->limit(8)->get();
        $stays = GuestHouseBooking::query()->with('guestHouse')->latest()->limit(8)->get();
        $carRows = $cars->map(fn (Order $order): array => [
            'type' => 'cars',
            'url' => OrderResource::getUrl('edit', ['record' => $order]),
            'reference' => $order->reference ?: (string) $order->id,
            'customer' => $order->customer_name ?: '—',
            'email' => $order->customer_email ?: '',
            'item' => $order->car?->name ?: '—',
            'dates' => $this->range($order->pickup_at, $order->dropoff_at),
            'status' => AdminTableBadgeColors::humanize($order->order_status),
            'tone' => $order->order_status === OrderStatus::Confirmed ? 'ok' : ($order->order_status === OrderStatus::Cancelled ? 'bad' : 'warn'),
            'total' => $code.' '.$this->money((int) $order->total_cents, $decimals, $decimal, $thousand),
            'at' => $order->created_at?->timestamp ?? 0,
        ]);
        $stayRows = $stays->map(fn (GuestHouseBooking $booking): array => [
            'type' => 'stays',
            'url' => GuestHouseBookingResource::getUrl('view', ['record' => $booking]),
            'reference' => $booking->booking_reference ?: (string) $booking->id,
            'customer' => $booking->guest_name ?: '—',
            'email' => $booking->guest_email ?? '',
            'item' => $booking->guestHouse?->name ?: '—',
            'dates' => $this->range($booking->check_in, $booking->check_out),
            'status' => AdminTableBadgeColors::humanize($booking->status),
            'tone' => $booking->status === GuestHouseBookingStatus::Confirmed ? 'ok' : 'warn',
            'total' => $code.' '.$this->money((int) $booking->total_amount, $decimals, $decimal, $thousand),
            'at' => $booking->created_at?->timestamp ?? 0,
        ]);

        $rows = match ($this->orderTab) {
            'cars' => $carRows,
            'stays' => $stayRows,
            default => $carRows->concat($stayRows)->sortByDesc('at')->take(8)->values(),
        };

        $change = $snapshot['revenue']['month_change_percent'];
        $changeLabel = ($change >= 0 ? '+' : '').$this->number($change, 0, $decimal, $thousand).'% vs last month →';

        return [
            'code' => $code,
            'kpis' => [
                [
                    'icon' => 'icon-kpi-alert.svg',
                    'tile' => 'warn',
                    'label' => 'Awaiting confirmation',
                    'value' => (string) $snapshot['operations']['pending_car_orders'],
                    'meta' => 'car orders · '.$code.' '.$this->money($pendingCents, $decimals, $decimal, $thousand),
                    'link' => 'Review →',
                    'linkTone' => 'warn',
                    'url' => $ordersUrl,
                ],
                [
                    'icon' => 'icon-kpi-chart.svg',
                    'tile' => 'info',
                    'label' => 'Revenue this month',
                    'value' => $code.' '.$this->money((int) $snapshot['revenue']['this_month_cents'], $decimals, $decimal, $thousand),
                    'meta' => 'Confirmed bookings only',
                    'link' => $changeLabel,
                    'linkTone' => 'muted',
                    'url' => $analyticsUrl,
                ],
                [
                    'icon' => 'icon-kpi-calendar.svg',
                    'tile' => 'info',
                    'label' => 'Bookings this month',
                    'value' => (string) ($snapshot['volume']['car_orders_this_month'] + $snapshot['volume']['gh_bookings_this_month']),
                    'meta' => $snapshot['volume']['car_orders_this_month'].' car · '.$snapshot['volume']['gh_bookings_this_month'].' guest house',
                    'link' => 'View all →',
                    'linkTone' => 'steel',
                    'url' => $ordersUrl,
                ],
                [
                    'icon' => 'icon-kpi-clock.svg',
                    'tile' => 'info',
                    'label' => 'Live right now',
                    'value' => (string) ($snapshot['operations']['active_rentals'] + $snapshot['operations']['active_stays']),
                    'meta' => $snapshot['operations']['active_rentals'].' rentals · '.$snapshot['operations']['active_stays'].' stays',
                    'link' => 'Open calendar →',
                    'linkTone' => 'steel',
                    'url' => $calendarUrl,
                ],
                [
                    'icon' => 'icon-kpi-globe.svg',
                    'tile' => 'info',
                    'label' => 'Website visitors',
                    'value' => (string) $snapshot['traffic']['visitors_30d'],
                    'meta' => $this->number((float) $snapshot['traffic']['conversion_rate_30d'], 1, $decimal, $thousand).'% conversion (30 days)',
                    'link' => 'Analytics →',
                    'linkTone' => 'steel',
                    'url' => $analyticsUrl,
                ],
            ],
            'todoCount' => collect($attention)->where('todo', true)->count(),
            'attention' => $attention,
            'bars' => $bars,
            'chartStart' => $labels[0] ?? '',
            'chartEnd' => $labels === [] ? '' : $labels[array_key_last($labels)],
            'countries' => $snapshot['top_countries'],
            'ordersUrl' => $ordersUrl,
            'staysUrl' => GuestHouseBookingResource::getUrl('index'),
            'carCount' => Order::query()->count(),
            'stayCount' => GuestHouseBooking::query()->count(),
            'rows' => $rows,
            'orderTab' => $this->orderTab,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyBoard(): array
    {
        return [
            'kpis' => [],
            'todoCount' => 0,
            'attention' => [],
            'bars' => [],
            'chartStart' => '',
            'chartEnd' => '',
            'countries' => [],
            'ordersUrl' => url('/admin'),
            'staysUrl' => url('/admin'),
            'carCount' => 0,
            'stayCount' => 0,
            'rows' => collect(),
            'orderTab' => 'all',
        ];
    }

    private function money(int $cents, int $decimals, string $decimal, string $thousand): string
    {
        return number_format($cents / 100, $decimals, $decimal, $thousand);
    }

    private function number(float $value, int $decimals, string $decimal, string $thousand): string
    {
        return number_format($value, $decimals, $decimal, $thousand);
    }

    private function range(mixed $start, mixed $end): string
    {
        if ($start === null) {
            return '—';
        }
        $from = \Illuminate\Support\Carbon::parse($start);
        $to = $end ? \Illuminate\Support\Carbon::parse($end) : null;
        if ($to === null) {
            return $from->format('j M Y');
        }
        if ($from->isSameDay($to)) {
            return $from->format('j M Y');
        }
        if ($from->year === $to->year) {
            return $from->format('j M').' – '.$to->format('j M Y');
        }

        return $from->format('j M Y').' – '.$to->format('j M Y');
    }
}

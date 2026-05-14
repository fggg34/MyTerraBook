<?php

namespace App\Filament\Pages;

use App\Enums\OrderStatus;
use App\Filament\Clusters\ImpactRentCluster;
use App\Models\Order;
use BackedEnum;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use UnitEnum;

class OrdersCalendar extends Page
{
    protected static ?string $cluster = ImpactRentCluster::class;

    protected string $view = 'filament.pages.orders-calendar';

    protected static ?string $title = 'Orders Calendar';

    protected static string|UnitEnum|null $navigationGroup = 'Orders';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $slug = 'orders-calendar';

    /** @var array<int, array{label: string, cells: array<int, array{date: string, day: string, inMonth: bool, reservations: int}>}> */
    public array $months = [];

    public function mount(): void
    {
        $this->months = $this->buildMonths();
    }

    /**
     * @return array<int, array{label: string, cells: array<int, array{date: string, day: string, inMonth: bool, reservations: int}>}>
     */
    private function buildMonths(): array
    {
        $start = now()->startOfMonth();
        $end = $start->copy()->addMonths(3)->endOfMonth();

        $orders = Order::query()
            ->whereIn('order_status', [
                OrderStatus::Confirmed->value,
                OrderStatus::StandBy->value,
                OrderStatus::Pending->value,
            ])
            ->whereDate('pickup_at', '<=', $end->toDateString())
            ->whereDate('dropoff_at', '>=', $start->toDateString())
            ->get(['pickup_at', 'dropoff_at']);

        $countsByDate = $this->countReservationsByDate($orders, $start, $end);

        $months = [];

        for ($i = 0; $i < 4; $i++) {
            $monthStart = $start->copy()->addMonths($i);
            $gridStart = $monthStart->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
            $gridEnd = $monthStart->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

            $cells = [];
            $cursor = $gridStart->copy();

            while ($cursor->lessThanOrEqualTo($gridEnd)) {
                $date = $cursor->toDateString();
                $cells[] = [
                    'date' => $date,
                    'day' => $cursor->format('d'),
                    'inMonth' => $cursor->month === $monthStart->month,
                    'reservations' => (int) ($countsByDate[$date] ?? 0),
                ];

                $cursor->addDay();
            }

            $months[] = [
                'label' => $monthStart->format('F Y'),
                'cells' => $cells,
            ];
        }

        return $months;
    }

    /**
     * @param Collection<int, Order> $orders
     * @return array<string, int>
     */
    private function countReservationsByDate(Collection $orders, Carbon $from, Carbon $to): array
    {
        $counts = [];

        foreach ($orders as $order) {
            if (! $order->pickup_at || ! $order->dropoff_at) {
                continue;
            }

            $cursor = Carbon::parse($order->pickup_at)->startOfDay();
            $last = Carbon::parse($order->dropoff_at)->startOfDay();

            while ($cursor->lessThan($last)) {
                if ($cursor->betweenIncluded($from, $to)) {
                    $key = $cursor->toDateString();
                    $counts[$key] = (int) (($counts[$key] ?? 0) + 1);
                }

                $cursor->addDay();
            }
        }

        return $counts;
    }
}

<?php

namespace App\Services\Admin;

use App\Enums\OrderStatus;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminReportsService
{
    /**
     * @return array{
     *   period: array{from: string, to: string},
     *   revenue_summary: array{
     *     confirmed_orders: int,
     *     revenue_cents: int,
     *     revenue: string,
     *     average_order_value_cents: int,
     *     average_order_value: string
     *   },
     *   occupancy_ranking: array<int, array{
     *     car_id: int,
     *     car_name: string,
     *     confirmed_orders: int,
     *     booked_hours: int,
     *     booked_days: float
     *   }>,
     *   top_countries: array<int, array{
     *     country: string,
     *     confirmed_orders: int,
     *     revenue_cents: int,
     *     revenue: string
     *   }>,
     *   rate_plan_revenue: array<int, array{
     *     price_type_id: ?int,
     *     rate_plan_name: string,
     *     confirmed_orders: int,
     *     revenue_cents: int,
     *     revenue: string
     *   }>
     * }
     */
    public function forPeriod(Carbon $from, Carbon $to): array
    {
        $ordersQuery = DB::table('orders')
            ->where('order_status', OrderStatus::Confirmed->value)
            ->whereBetween('pickup_at', [$from, $to]);

        $confirmedCount = (int) (clone $ordersQuery)->count();
        $revenueCents = (int) (clone $ordersQuery)->sum('total_cents');
        $avgCents = $confirmedCount > 0 ? (int) floor($revenueCents / $confirmedCount) : 0;

        $driver = DB::connection()->getDriverName();
        $bookedHoursExpr = $driver === 'sqlite'
            ? 'SUM(MAX((julianday(o.dropoff_at) - julianday(o.pickup_at)) * 24, 0)) as booked_hours'
            : 'SUM(GREATEST(TIMESTAMPDIFF(HOUR, o.pickup_at, o.dropoff_at), 0)) as booked_hours';

        $occupancyRows = DB::table('orders as o')
            ->join('cars as c', 'c.id', '=', 'o.car_id')
            ->select(
                'o.car_id',
                'c.name as car_name',
                DB::raw('COUNT(o.id) as confirmed_orders'),
                DB::raw($bookedHoursExpr)
            )
            ->where('o.order_status', OrderStatus::Confirmed->value)
            ->whereBetween('o.pickup_at', [$from, $to])
            ->groupBy('o.car_id', 'c.name')
            ->orderByDesc('booked_hours')
            ->limit(20)
            ->get()
            ->map(fn ($row) => [
                'car_id' => (int) $row->car_id,
                'car_name' => (string) $row->car_name,
                'confirmed_orders' => (int) $row->confirmed_orders,
                'booked_hours' => (int) $row->booked_hours,
                'booked_days' => round(((int) $row->booked_hours) / 24, 2),
            ])
            ->values()
            ->all();

        $topCountries = DB::table('orders')
            ->select(
                'customer_country',
                DB::raw('COUNT(*) as confirmed_orders'),
                DB::raw('SUM(total_cents) as revenue_cents')
            )
            ->where('order_status', OrderStatus::Confirmed->value)
            ->whereBetween('pickup_at', [$from, $to])
            ->whereNotNull('customer_country')
            ->where('customer_country', '!=', '')
            ->groupBy('customer_country')
            ->orderByDesc('revenue_cents')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'country' => (string) $row->customer_country,
                'confirmed_orders' => (int) $row->confirmed_orders,
                'revenue_cents' => (int) $row->revenue_cents,
                'revenue' => Money::formatDecimalFromCents((int) $row->revenue_cents),
            ])
            ->values()
            ->all();

        $ratePlanRevenue = DB::table('orders as o')
            ->leftJoin('price_types as pt', 'pt.id', '=', 'o.price_type_id')
            ->select(
                'o.price_type_id',
                DB::raw("COALESCE(pt.name, 'Unknown') as rate_plan_name"),
                DB::raw('COUNT(o.id) as confirmed_orders'),
                DB::raw('SUM(o.total_cents) as revenue_cents')
            )
            ->where('o.order_status', OrderStatus::Confirmed->value)
            ->whereBetween('o.pickup_at', [$from, $to])
            ->groupBy('o.price_type_id', 'pt.name')
            ->orderByDesc('revenue_cents')
            ->get()
            ->map(fn ($row) => [
                'price_type_id' => $row->price_type_id !== null ? (int) $row->price_type_id : null,
                'rate_plan_name' => (string) $row->rate_plan_name,
                'confirmed_orders' => (int) $row->confirmed_orders,
                'revenue_cents' => (int) $row->revenue_cents,
                'revenue' => Money::formatDecimalFromCents((int) $row->revenue_cents),
            ])
            ->values()
            ->all();

        return [
            'period' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'revenue_summary' => [
                'confirmed_orders' => $confirmedCount,
                'revenue_cents' => $revenueCents,
                'revenue' => Money::formatDecimalFromCents($revenueCents),
                'average_order_value_cents' => $avgCents,
                'average_order_value' => Money::formatDecimalFromCents($avgCents),
            ],
            'occupancy_ranking' => $occupancyRows,
            'top_countries' => $topCountries,
            'rate_plan_revenue' => $ratePlanRevenue,
        ];
    }
}

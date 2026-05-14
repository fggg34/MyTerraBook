<?php

namespace App\Services\Admin;

use App\Enums\OrderStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TrackingStatisticsService
{
    /**
     * @return array{
     *   countries: array<int, string>,
     *   referrers: array<int, string>,
     *   most_demanded_days: array<int, array{date:string,requests:int,visitors:int}>,
     *   average_values: array{
     *     total_visitors:int,
     *     total_bookings:int,
     *     average_length_of_rent:float,
     *     average_conversion_rate:float
     *   },
     *   best_referrers: array<int, array{referrer:string,visitors:int}>,
     *   conversion_rates: array<int, array{date:string,visitors:int,bookings:int,conversion_rate:float}>
     * }
     */
    public function forPeriod(
        Carbon $from,
        Carbon $to,
        ?string $country = null,
        ?string $referrer = null,
        ?string $search = null,
    ): array {
        $dayExpr = 'DATE(created_at)';
        $driver = DB::connection()->getDriverName();

        $filteredEvents = DB::table('tracking_events')
            ->whereBetween('created_at', [$from, $to]);

        if ($country !== null && $country !== '') {
            $filteredEvents->where('country', $country);
        }

        if ($referrer !== null && $referrer !== '') {
            $filteredEvents->where('referrer_host', $referrer);
        }

        if ($search !== null && trim($search) !== '') {
            $needle = '%'.mb_strtolower(trim($search)).'%';
            $filteredEvents->where(function ($query) use ($needle): void {
                $query
                    ->whereRaw('LOWER(COALESCE(country, "")) LIKE ?', [$needle])
                    ->orWhereRaw('LOWER(COALESCE(referrer_host, "")) LIKE ?', [$needle])
                    ->orWhereRaw('LOWER(COALESCE(event_type, "")) LIKE ?', [$needle]);
            });
        }

        $eventsCount = (int) (clone $filteredEvents)->count();

        $visitorCase = "CASE
            WHEN LOWER(event_type) IN ('page_view', 'visitor', 'visit', 'session_start') THEN 1
            ELSE 0
        END";

        $requestCase = "CASE
            WHEN LOWER(event_type) IN ('request', 'quote_request', 'booking_request', 'booking_attempt', 'checkout_start')
                OR LOWER(event_type) LIKE '%request%'
            THEN 1
            ELSE 0
        END";

        $ordersDemandQuery = DB::table('orders')
            ->whereBetween('created_at', [$from, $to]);

        if ($country !== null && $country !== '') {
            $ordersDemandQuery->where('customer_country', $country);
        }

        if ($search !== null && trim($search) !== '') {
            $needle = '%'.mb_strtolower(trim($search)).'%';
            $ordersDemandQuery->where(function ($query) use ($needle): void {
                $query
                    ->whereRaw('LOWER(COALESCE(reference, "")) LIKE ?', [$needle])
                    ->orWhereRaw('LOWER(COALESCE(customer_name, "")) LIKE ?', [$needle])
                    ->orWhereRaw('LOWER(COALESCE(customer_email, "")) LIKE ?', [$needle])
                    ->orWhereRaw('LOWER(COALESCE(customer_country, "")) LIKE ?', [$needle]);
            });
        }

        $hasUnsupportedReferrerFilter = $referrer !== null
            && $referrer !== ''
            && mb_strtolower($referrer) !== 'direct';

        if ($eventsCount > 0) {
            $mostDemandedDays = (clone $filteredEvents)
                ->selectRaw($dayExpr.' as event_day')
                ->selectRaw('SUM('.$requestCase.') as requests')
                ->selectRaw('SUM('.$visitorCase.') as visitors')
                ->groupBy('event_day')
                ->orderByDesc('requests')
                ->orderByDesc('visitors')
                ->orderByDesc('event_day')
                ->limit(14)
                ->get()
                ->map(fn ($row) => [
                    'date' => (string) $row->event_day,
                    'requests' => (int) $row->requests,
                    'visitors' => (int) $row->visitors,
                ])
                ->all();

            $totalVisitors = (int) (clone $filteredEvents)
                ->selectRaw('SUM('.$visitorCase.') as visitors')
                ->value('visitors');
        } else {
            $mostDemandedDays = $hasUnsupportedReferrerFilter
                ? []
                : (clone $ordersDemandQuery)
                    ->selectRaw($dayExpr.' as event_day')
                    ->selectRaw('COUNT(*) as requests')
                    ->selectRaw('COUNT(DISTINCT customer_email) as visitors')
                    ->groupBy('event_day')
                    ->orderByDesc('requests')
                    ->orderByDesc('visitors')
                    ->orderByDesc('event_day')
                    ->limit(14)
                    ->get()
                    ->map(fn ($row) => [
                        'date' => (string) $row->event_day,
                        'requests' => (int) $row->requests,
                        'visitors' => (int) $row->visitors,
                    ])
                    ->all();

            $totalVisitors = $hasUnsupportedReferrerFilter
                ? 0
                : (int) ((clone $ordersDemandQuery)
                    ->selectRaw('COUNT(DISTINCT customer_email) as visitors')
                    ->value('visitors') ?? 0);
        }

        $ordersQuery = DB::table('orders')
            ->where('order_status', OrderStatus::Confirmed->value)
            ->whereBetween('created_at', [$from, $to]);

        if ($country !== null && $country !== '') {
            $ordersQuery->where('customer_country', $country);
        }

        if ($search !== null && trim($search) !== '') {
            $needle = '%'.mb_strtolower(trim($search)).'%';
            $ordersQuery->where(function ($query) use ($needle): void {
                $query
                    ->whereRaw('LOWER(COALESCE(reference, "")) LIKE ?', [$needle])
                    ->orWhereRaw('LOWER(COALESCE(customer_name, "")) LIKE ?', [$needle])
                    ->orWhereRaw('LOWER(COALESCE(customer_email, "")) LIKE ?', [$needle])
                    ->orWhereRaw('LOWER(COALESCE(customer_country, "")) LIKE ?', [$needle]);
            });
        }

        if ($eventsCount === 0 && $hasUnsupportedReferrerFilter) {
            $ordersQuery->whereRaw('1 = 0');
        }

        $totalBookings = (int) (clone $ordersQuery)->count();

        $rentalDaysExpr = $driver === 'sqlite'
            ? 'MAX((julianday(dropoff_at) - julianday(pickup_at)), 0)'
            : 'GREATEST(TIMESTAMPDIFF(HOUR, pickup_at, dropoff_at) / 24, 0)';

        $avgLength = (float) ((clone $ordersQuery)
            ->selectRaw('AVG('.$rentalDaysExpr.') as avg_rental_days')
            ->value('avg_rental_days') ?? 0.0);

        $conversionRate = $totalVisitors > 0
            ? round(($totalBookings / $totalVisitors) * 100, 2)
            : 0.0;

        if ($eventsCount > 0) {
            $bestReferrers = (clone $filteredEvents)
                ->selectRaw("COALESCE(NULLIF(referrer_host, ''), 'Direct') as referrer_label")
                ->selectRaw('SUM('.$visitorCase.') as visitors')
                ->groupBy('referrer_label')
                ->orderByDesc('visitors')
                ->limit(8)
                ->get()
                ->map(fn ($row) => [
                    'referrer' => (string) $row->referrer_label,
                    'visitors' => (int) $row->visitors,
                ])
                ->all();

            $visitorDays = (clone $filteredEvents)
                ->selectRaw($dayExpr.' as event_day')
                ->selectRaw('SUM('.$visitorCase.') as visitors')
                ->groupBy('event_day')
                ->get()
                ->mapWithKeys(fn ($row) => [
                    (string) $row->event_day => (int) $row->visitors,
                ]);
        } else {
            $bestReferrers = $totalVisitors > 0
                ? [['referrer' => 'Direct', 'visitors' => $totalVisitors]]
                : [];

            $visitorDays = $hasUnsupportedReferrerFilter
                ? collect()
                : (clone $ordersDemandQuery)
                    ->selectRaw($dayExpr.' as event_day')
                    ->selectRaw('COUNT(DISTINCT customer_email) as visitors')
                    ->groupBy('event_day')
                    ->get()
                    ->mapWithKeys(fn ($row) => [
                        (string) $row->event_day => (int) $row->visitors,
                    ]);
        }

        $bookingDays = (clone $ordersQuery)
            ->selectRaw($dayExpr.' as booking_day')
            ->selectRaw('COUNT(*) as bookings')
            ->groupBy('booking_day')
            ->get()
            ->mapWithKeys(fn ($row) => [
                (string) $row->booking_day => (int) $row->bookings,
            ]);

        $allDays = collect(array_unique(array_merge($visitorDays->keys()->all(), $bookingDays->keys()->all())))
            ->sortDesc()
            ->values();

        $conversionRates = $allDays
            ->map(function (string $day) use ($visitorDays, $bookingDays): array {
                $visitors = (int) ($visitorDays->get($day) ?? 0);
                $bookings = (int) ($bookingDays->get($day) ?? 0);

                return [
                    'date' => $day,
                    'visitors' => $visitors,
                    'bookings' => $bookings,
                    'conversion_rate' => $visitors > 0 ? round(($bookings / $visitors) * 100, 2) : 0.0,
                ];
            })
            ->take(14)
            ->all();

        $countries = DB::table('tracking_events')
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->distinct()
            ->orderBy('country')
            ->pluck('country')
            ->map(fn ($value): string => (string) $value)
            ->values()
            ->all();
        $orderCountries = DB::table('orders')
            ->whereNotNull('customer_country')
            ->where('customer_country', '!=', '')
            ->distinct()
            ->orderBy('customer_country')
            ->pluck('customer_country')
            ->map(fn ($value): string => (string) $value)
            ->values()
            ->all();
        $countries = collect(array_merge($countries, $orderCountries))
            ->unique()
            ->values()
            ->all();

        $referrers = DB::table('tracking_events')
            ->whereNotNull('referrer_host')
            ->where('referrer_host', '!=', '')
            ->distinct()
            ->orderBy('referrer_host')
            ->pluck('referrer_host')
            ->map(fn ($value): string => (string) $value)
            ->values()
            ->all();
        if ($referrers === []) {
            $referrers = ['Direct'];
        }

        return [
            'countries' => $countries,
            'referrers' => $referrers,
            'most_demanded_days' => $mostDemandedDays,
            'average_values' => [
                'total_visitors' => $totalVisitors,
                'total_bookings' => $totalBookings,
                'average_length_of_rent' => round($avgLength, 2),
                'average_conversion_rate' => $conversionRate,
            ],
            'best_referrers' => $bestReferrers,
            'conversion_rates' => $conversionRates,
        ];
    }
}

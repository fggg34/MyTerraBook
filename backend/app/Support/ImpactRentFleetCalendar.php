<?php

namespace App\Support;

use App\Enums\OrderStatus;
use App\Enums\RentalStatus;
use App\Models\AvailabilityBlock;
use App\Models\Car;
use App\Models\Location;
use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ImpactRentFleetCalendar
{
    public const DAY_WIDTH = 44;

    public const SIDEBAR_WIDTH = 236;

    public const LANE_HEIGHT = 30;

    public const LANE_GAP = 4;

    public const ROW_PAD = 6;

    /**
     * @return array<string, mixed>
     */
    public function present(string $from, ?int $locationId = null, ?int $selectedOrderId = null, int $days = 90): array
    {
        $days = max(14, min(180, $days));
        $start = $this->parseDay($from)->startOfDay();
        $end = $start->copy()->addDays($days);

        $cars = Car::query()
            ->with(['carUnits', 'locations'])
            ->when($locationId, function ($query) use ($locationId): void {
                $query->whereHas('locations', fn ($locations) => $locations->whereKey($locationId));
            })
            ->orderBy('name')
            ->get();

        $carIds = $cars->modelKeys();
        $orders = $carIds === []
            ? collect()
            : Order::query()
                ->with(['pickupLocation', 'dropoffLocation', 'car'])
                ->whereIn('car_id', $carIds)
                ->where('pickup_at', '<', $end)
                ->where('dropoff_at', '>', $start)
                ->where('order_status', '!=', OrderStatus::Cancelled)
                ->orderBy('pickup_at')
                ->get();

        $blocks = $carIds === []
            ? collect()
            : AvailabilityBlock::query()
                ->whereIn('car_id', $carIds)
                ->where('is_active', true)
                ->where('starts_at', '<', $end)
                ->where('ends_at', '>', $start)
                ->orderBy('starts_at')
                ->get();

        $ordersByCar = $orders->groupBy('car_id');
        $blocksByCar = $blocks->groupBy('car_id');
        $groups = [];
        $plateCount = 0;

        foreach ($cars as $car) {
            $units = $car->carUnits->sortBy('id')->sortBy('sort_order')->values();
            $unitRows = $units->isEmpty()
                ? collect([(object) ['id' => null, 'is_active' => (bool) $car->is_active]])
                : $units;
            $rows = [];

            foreach ($unitRows as $index => $unit) {
                $unitId = $unit->id;
                $rowOrders = $ordersByCar->get($car->id, collect())
                    ->filter(function (Order $order) use ($unitId, $unitRows): bool {
                        if ($unitId === null) {
                            return true;
                        }
                        if ($order->car_unit_id === null) {
                            return $unitId === $unitRows->first()->id;
                        }

                        return (int) $order->car_unit_id === (int) $unitId;
                    })
                    ->values();

                $built = $this->bars($rowOrders, $start, $days);
                $blockBars = $index === 0
                    ? $this->blockBars($blocksByCar->get($car->id, collect()), $start, $days)
                    : [];
                $laneCount = max(1, $built['laneCount']);
                $plateCount++;
                $rows[] = [
                    'key' => $unitId === null ? 'car-'.$car->id : 'unit-'.$unitId,
                    'title' => 'Unit '.($index + 1),
                    'subtitle' => $car->name.($unit->is_active ? '' : ' · Inactive'),
                    'active' => (bool) $car->is_active && (bool) $unit->is_active,
                    'height' => $laneCount * (self::LANE_HEIGHT + self::LANE_GAP) + (self::ROW_PAD * 2),
                    'bars' => $built['bars'],
                    'blocks' => $blockBars,
                ];
            }

            $groups[] = [
                'key' => 'car-'.$car->id,
                'label' => $car->name,
                'unitCount' => count($rows),
                'rows' => $rows,
            ];
        }

        $selected = null;
        if ($selectedOrderId !== null) {
            $selectedOrder = $orders->firstWhere('id', $selectedOrderId)
                ?? Order::query()->with(['pickupLocation', 'dropoffLocation', 'car'])->find($selectedOrderId);
            if ($selectedOrder instanceof Order) {
                $selected = $this->detail($selectedOrder);
            }
        }

        $dayColumns = [];
        $months = [];
        for ($i = 0; $i < $days; $i++) {
            $day = $start->copy()->addDays($i);
            $month = strtoupper($day->format('F Y'));
            $last = $months[array_key_last($months)] ?? null;
            if ($last !== null && $last['label'] === $month) {
                $months[array_key_last($months)]['count']++;
            } else {
                $months[] = ['label' => $month, 'count' => 1];
            }
            $dayColumns[] = [
                'key' => $day->toDateString(),
                'number' => $day->format('j'),
                'dow' => strtoupper($day->format('D')),
                'weekend' => $day->isWeekend(),
                'today' => $day->isSameDay(now()),
            ];
        }

        return [
            'rangeLabel' => $start->format('j M').' to '.$start->copy()->addDays($days - 1)->format('j M Y'),
            'days' => $dayColumns,
            'months' => $months,
            'timelineWidth' => $days * self::DAY_WIDTH,
            'dayWidth' => self::DAY_WIDTH,
            'sidebarWidth' => self::SIDEBAR_WIDTH,
            'plateCount' => $plateCount,
            'groups' => $groups,
            'locations' => Location::query()->orderBy('name')->get(['id', 'name'])->map(fn (Location $location): array => [
                'id' => $location->id,
                'name' => $location->name,
            ])->all(),
            'selected' => $selected,
        ];
    }

    public static function money(int $cents, string $currency): string
    {
        $negative = $cents < 0;
        $cents = abs($cents);
        $whole = intdiv($cents, 100);
        $fraction = str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
        $grouped = preg_replace('/\B(?=(\d{3})+(?!\d))/', ',', (string) $whole) ?? (string) $whole;

        return ($negative ? '-' : '').$grouped.'.'.$fraction.' '.strtoupper($currency);
    }

    private function parseDay(string $from): Carbon
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) === 1) {
            return Carbon::parse($from);
        }

        return now();
    }

    /**
     * @param  Collection<int, Order>  $orders
     * @return array{bars: list<array<string, mixed>>, laneCount: int}
     */
    private function bars(Collection $orders, Carbon $start, int $days): array
    {
        $rangeStart = $start->getTimestamp();
        $laneEnds = [];
        $bars = [];

        foreach ($orders->sortBy(fn (Order $order) => $order->pickup_at?->getTimestamp() ?? 0) as $order) {
            if ($order->pickup_at === null || $order->dropoff_at === null) {
                continue;
            }
            $startIdx = max(0, ($order->pickup_at->getTimestamp() - $rangeStart) / 86400);
            $endIdx = min($days, ($order->dropoff_at->getTimestamp() - $rangeStart) / 86400);
            if ($endIdx <= 0 || $startIdx >= $days) {
                continue;
            }

            $lane = 0;
            $placed = false;
            foreach ($laneEnds as $index => $laneEnd) {
                if ($laneEnd <= $order->pickup_at->getTimestamp()) {
                    $lane = $index;
                    $laneEnds[$index] = $order->dropoff_at->getTimestamp();
                    $placed = true;
                    break;
                }
            }
            if (! $placed) {
                $lane = count($laneEnds);
                $laneEnds[] = $order->dropoff_at->getTimestamp();
            }

            $bars[] = [
                'id' => $order->id,
                'left' => $startIdx * self::DAY_WIDTH,
                'width' => max(($endIdx - $startIdx) * self::DAY_WIDTH, 18),
                'top' => self::ROW_PAD + ($lane * (self::LANE_HEIGHT + self::LANE_GAP)),
                'tone' => $this->tone($order),
                'label' => trim($order->reference.' · '.$order->customer_name),
            ];
        }

        return [
            'bars' => $bars,
            'laneCount' => count($laneEnds),
        ];
    }

    /**
     * @param  Collection<int, AvailabilityBlock>  $blocks
     * @return list<array<string, mixed>>
     */
    private function blockBars(Collection $blocks, Carbon $start, int $days): array
    {
        $rangeStart = $start->getTimestamp();
        $bars = [];
        foreach ($blocks as $block) {
            $startIdx = max(0, ($block->starts_at->getTimestamp() - $rangeStart) / 86400);
            $endIdx = min($days, ($block->ends_at->getTimestamp() - $rangeStart) / 86400);
            if ($endIdx <= 0 || $startIdx >= $days) {
                continue;
            }
            $bars[] = [
                'left' => $startIdx * self::DAY_WIDTH,
                'width' => max(($endIdx - $startIdx) * self::DAY_WIDTH, 18),
                'label' => filled($block->notes) ? 'Blocked · '.$block->notes : 'Blocked',
            ];
        }

        return $bars;
    }

    private function tone(Order $order): string
    {
        return match ($order->rental_status) {
            RentalStatus::Started => 'active',
            RentalStatus::Terminated => 'returned',
            RentalStatus::NoShow => 'noshow',
            default => match ($order->order_status) {
                OrderStatus::StandBy, OrderStatus::Pending => 'quote',
                default => 'confirmed',
            },
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function detail(Order $order): array
    {
        $status = match ($order->rental_status) {
            RentalStatus::Started => 'On the road',
            RentalStatus::Terminated => 'Returned',
            RentalStatus::NoShow => 'No show',
            default => match ($order->order_status) {
                OrderStatus::StandBy => 'Stand by',
                OrderStatus::Pending => 'Pending',
                OrderStatus::Confirmed => 'Confirmed',
                OrderStatus::Cancelled => 'Cancelled',
            },
        };

        return [
            'id' => $order->id,
            'reference' => $order->reference,
            'customer' => $order->customer_name,
            'email' => $order->customer_email,
            'phone' => $order->customer_phone,
            'status' => $status,
            'tone' => $this->tone($order),
            'vehicle' => $order->car?->name ?? 'Vehicle',
            'pickup' => $order->pickup_at?->format('j M Y H:i') ?? '',
            'pickupLocation' => $order->pickupLocation?->name ?? '',
            'dropoff' => $order->dropoff_at?->format('j M Y H:i') ?? '',
            'dropoffLocation' => $order->dropoffLocation?->name ?? '',
            'total' => self::money((int) $order->total_cents, (string) ($order->currency ?: 'EUR')),
        ];
    }
}

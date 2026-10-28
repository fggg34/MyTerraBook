<?php

namespace App\Support;

use App\Enums\GuestHouseBookingStatus;
use App\Enums\OrderStatus;
use App\Enums\RentalStatus;
use App\Models\AvailabilityBlock;
use App\Models\Car;
use App\Models\GuestHouse;
use App\Models\GuestHouseAvailabilityBlock;
use App\Models\GuestHouseBooking;
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
    public function present(
        string $from,
        ?int $locationId = null,
        ?int $selectedOrderId = null,
        int $days = 28,
        string $catalog = 'all',
        string $statusFilter = '',
        ?int $hostId = null,
        ?int $selectedStayId = null,
    ): array {
        $days = max(1, min(180, $days));
        $start = $this->parseDay($from)->startOfDay();
        $end = $start->copy()->addDays($days);

        $cars = $catalog === 'stays'
            ? Car::query()->whereRaw('0 = 1')->get()
            : Car::query()
                ->with(['carUnits', 'locations', 'host'])
                ->when($locationId, function ($query) use ($locationId): void {
                    $query->whereHas('locations', fn ($locations) => $locations->whereKey($locationId));
                })
                ->when($hostId, fn ($query) => $query->where('user_id', $hostId))
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
                $hostName = $car->host?->name ?: (filled($car->external_provider) ? ucfirst((string) $car->external_provider) : 'MyTerraBook');
                $rows[] = [
                    'key' => $unitId === null ? 'car-'.$car->id : 'unit-'.$unitId,
                    'title' => $car->name,
                    'subtitle' => 'Unit '.($index + 1).' · '.$hostName.($unit->is_active ? '' : ' · Inactive'),
                    'active' => (bool) $car->is_active && (bool) $unit->is_active,
                    'height' => $laneCount * (self::LANE_HEIGHT + self::LANE_GAP) + (self::ROW_PAD * 2),
                    'bars' => $this->filterBars($built['bars'], $statusFilter),
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

        $stayRows = [];
        $stayCount = 0;
        $stayBookings = collect();
        if ($catalog !== 'cars') {
            $houses = GuestHouse::query()
                ->with(['host'])
                ->when($hostId, fn ($query) => $query->where('user_id', $hostId))
                ->orderBy('name')
                ->get();
            $houseIds = $houses->modelKeys();
            $stayBookings = $houseIds === []
                ? collect()
                : GuestHouseBooking::query()
                    ->with('guestHouse')
                    ->whereIn('guest_house_id', $houseIds)
                    ->where('status', '!=', GuestHouseBookingStatus::Cancelled)
                    ->whereDate('check_in', '<', $end->toDateString())
                    ->whereDate('check_out', '>', $start->toDateString())
                    ->orderBy('check_in')
                    ->get();
            $staysByHouse = $stayBookings->groupBy('guest_house_id');
            $houseBlocks = $houseIds === []
                ? collect()
                : GuestHouseAvailabilityBlock::query()
                    ->whereIn('guest_house_id', $houseIds)
                    ->whereDate('blocked_from', '<', $end->toDateString())
                    ->whereDate('blocked_to', '>', $start->toDateString())
                    ->get()
                    ->groupBy('guest_house_id');

            foreach ($houses as $house) {
                $built = $this->stayBars($staysByHouse->get($house->id, collect()), $start, $days);
                $laneCount = max(1, $built['laneCount']);
                $stayCount++;
                $stayRows[] = [
                    'key' => 'stay-'.$house->id,
                    'title' => $house->name,
                    'subtitle' => trim(($house->city ?: 'Iceland').' · #1'),
                    'active' => true,
                    'height' => $laneCount * (self::LANE_HEIGHT + self::LANE_GAP) + (self::ROW_PAD * 2),
                    'bars' => $this->filterBars($built['bars'], $statusFilter),
                    'blocks' => $this->stayBlockBars($houseBlocks->get($house->id, collect()), $start, $days),
                ];
            }
        }

        $carRows = [];
        foreach ($groups as $group) {
            foreach ($group['rows'] as $row) {
                $carRows[] = $row;
            }
        }

        $selected = null;
        $selectedKind = 'order';
        if ($selectedOrderId !== null) {
            $selectedOrder = $orders->firstWhere('id', $selectedOrderId)
                ?? Order::query()->with(['pickupLocation', 'dropoffLocation', 'car'])->find($selectedOrderId);
            if ($selectedOrder instanceof Order) {
                $selected = $this->detail($selectedOrder);
            }
        } elseif ($selectedStayId !== null) {
            $selectedStay = $stayBookings->firstWhere('id', $selectedStayId)
                ?? GuestHouseBooking::query()->with('guestHouse')->find($selectedStayId);
            if ($selectedStay instanceof GuestHouseBooking) {
                $selected = $this->stayDetail($selectedStay);
                $selectedKind = 'stay';
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

        $hosts = collect()
            ->merge($cars->map(fn (Car $car) => $car->host)->filter())
            ->unique('id')
            ->sortBy('name')
            ->values();

        $sections = [];
        if ($catalog !== 'stays' && $carRows !== []) {
            $sections[] = ['key' => 'cars', 'label' => 'CARS', 'rows' => $carRows];
        }
        if ($catalog !== 'cars' && $stayRows !== []) {
            $sections[] = ['key' => 'stays', 'label' => 'GUEST HOUSES', 'rows' => $stayRows];
        }

        return [
            'rangeLabel' => $start->format('j M').' to '.$start->copy()->addDays($days - 1)->format('j M Y'),
            'days' => $dayColumns,
            'months' => $months,
            'timelineWidth' => $days * self::DAY_WIDTH,
            'dayWidth' => self::DAY_WIDTH,
            'sidebarWidth' => self::SIDEBAR_WIDTH,
            'plateCount' => $plateCount,
            'stayCount' => $stayCount,
            'groups' => $groups,
            'sections' => $sections,
            'hosts' => $hosts->map(fn ($host): array => ['id' => $host->id, 'name' => $host->name])->all(),
            'locations' => Location::query()->orderBy('name')->get(['id', 'name'])->map(fn (Location $location): array => [
                'id' => $location->id,
                'name' => $location->name,
            ])->all(),
            'selected' => $selected,
            'selectedKind' => $selectedKind,
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

            $fromCode = $this->locationCode($order->pickupLocation);
            $toCode = $this->locationCode($order->dropoffLocation);
            $route = ($fromCode !== '' && $toCode !== '') ? $fromCode.' → '.$toCode : '';
            $visible = trim(($order->customer_name ?: 'Guest').($route !== '' ? ' · '.$route : ''));
            $bars[] = [
                'id' => $order->id,
                'kind' => 'order',
                'left' => $startIdx * self::DAY_WIDTH,
                'width' => max(($endIdx - $startIdx) * self::DAY_WIDTH, 18),
                'top' => self::ROW_PAD + ($lane * (self::LANE_HEIGHT + self::LANE_GAP)),
                'tone' => $this->tone($order),
                'label' => $visible,
                'title' => trim($order->reference.' · '.$visible),
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

    /**
     * @param  list<array<string, mixed>>  $bars
     * @return list<array<string, mixed>>
     */
    private function filterBars(array $bars, string $statusFilter): array
    {
        if ($statusFilter === '') {
            return $bars;
        }

        return array_values(array_filter($bars, fn (array $bar): bool => ($bar['tone'] ?? '') === $statusFilter));
    }

    /**
     * @param  Collection<int, GuestHouseBooking>  $bookings
     * @return array{bars: list<array<string, mixed>>, laneCount: int}
     */
    private function stayBars(Collection $bookings, Carbon $start, int $days): array
    {
        $rangeStart = $start->getTimestamp();
        $laneEnds = [];
        $bars = [];

        foreach ($bookings as $booking) {
            if ($booking->check_in === null || $booking->check_out === null) {
                continue;
            }
            $from = $booking->check_in->copy()->startOfDay();
            $to = $booking->check_out->copy()->startOfDay();
            $startIdx = max(0, ($from->getTimestamp() - $rangeStart) / 86400);
            $endIdx = min($days, ($to->getTimestamp() - $rangeStart) / 86400);
            if ($endIdx <= 0 || $startIdx >= $days) {
                continue;
            }

            $lane = 0;
            $placed = false;
            foreach ($laneEnds as $index => $laneEnd) {
                if ($laneEnd <= $from->getTimestamp()) {
                    $lane = $index;
                    $laneEnds[$index] = $to->getTimestamp();
                    $placed = true;
                    break;
                }
            }
            if (! $placed) {
                $lane = count($laneEnds);
                $laneEnds[] = $to->getTimestamp();
            }

            $nights = max(1, (int) ($booking->nights ?: $from->diffInDays($to)));
            $visible = trim(($booking->guest_name ?: 'Guest').' · '.$nights.' nights');
            $bars[] = [
                'id' => $booking->id,
                'kind' => 'stay',
                'left' => $startIdx * self::DAY_WIDTH,
                'width' => max(($endIdx - $startIdx) * self::DAY_WIDTH, 18),
                'top' => self::ROW_PAD + ($lane * (self::LANE_HEIGHT + self::LANE_GAP)),
                'tone' => match ($booking->status) {
                    GuestHouseBookingStatus::Pending => 'quote',
                    GuestHouseBookingStatus::Completed => 'returned',
                    default => 'confirmed',
                },
                'label' => $visible,
                'title' => trim($booking->booking_reference.' · '.$visible),
            ];
        }

        return [
            'bars' => $bars,
            'laneCount' => count($laneEnds),
        ];
    }

    /**
     * @param  Collection<int, GuestHouseAvailabilityBlock>  $blocks
     * @return list<array<string, mixed>>
     */
    private function stayBlockBars(Collection $blocks, Carbon $start, int $days): array
    {
        $rangeStart = $start->getTimestamp();
        $bars = [];
        foreach ($blocks as $block) {
            if ($block->blocked_from === null || $block->blocked_to === null) {
                continue;
            }
            $startIdx = max(0, ($block->blocked_from->copy()->startOfDay()->getTimestamp() - $rangeStart) / 86400);
            $endIdx = min($days, ($block->blocked_to->copy()->startOfDay()->getTimestamp() - $rangeStart) / 86400);
            if ($endIdx <= 0 || $startIdx >= $days) {
                continue;
            }
            $note = filled($block->note) ? (string) $block->note : '';
            $bars[] = [
                'left' => $startIdx * self::DAY_WIDTH,
                'width' => max(($endIdx - $startIdx) * self::DAY_WIDTH, 18),
                'label' => $note !== '' ? 'Blocked · '.$note : 'Blocked',
            ];
        }

        return $bars;
    }

    /**
     * @return array<string, mixed>
     */
    private function stayDetail(GuestHouseBooking $booking): array
    {
        $status = match ($booking->status) {
            GuestHouseBookingStatus::Pending => 'Stand by',
            GuestHouseBookingStatus::Completed => 'Returned',
            GuestHouseBookingStatus::Cancelled => 'Cancelled',
            default => 'Confirmed',
        };

        return [
            'id' => $booking->id,
            'reference' => $booking->booking_reference,
            'customer' => $booking->guest_name,
            'email' => $booking->guest_email,
            'phone' => $booking->guest_phone,
            'status' => $status,
            'tone' => match ($booking->status) {
                GuestHouseBookingStatus::Pending => 'quote',
                GuestHouseBookingStatus::Completed => 'returned',
                default => 'confirmed',
            },
            'vehicle' => $booking->guestHouse?->name ?? 'Guest house',
            'pickup' => $booking->check_in?->format('j M Y') ?? '',
            'pickupLocation' => $booking->guestHouse?->city ?? '',
            'dropoff' => $booking->check_out?->format('j M Y') ?? '',
            'dropoffLocation' => '',
            'total' => self::money((int) $booking->total_amount, 'EUR'),
        ];
    }

    private function locationCode(?Location $location): string
    {
        if ($location === null) {
            return '';
        }
        $haystack = strtoupper($location->name.' '.$location->slug);
        foreach (['KEF', 'AEY', 'RKV', 'EGS', 'IFJ', 'VEY'] as $code) {
            if (str_contains($haystack, $code)) {
                return $code;
            }
        }
        if (str_contains($haystack, 'KEFLAV')) {
            return 'KEF';
        }
        if (str_contains($haystack, 'AKUREY')) {
            return 'AEY';
        }
        if (str_contains($haystack, 'REYKJAV')) {
            return 'RKV';
        }
        if (str_contains($haystack, 'EGILS')) {
            return 'EGS';
        }

        $slug = strtoupper((string) preg_replace('/[^A-Za-z]/', '', $location->slug ?: $location->name));

        return substr($slug, 0, 3);
    }
}

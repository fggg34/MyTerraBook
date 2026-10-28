<?php

namespace App\Filament\Pages;

use App\Filament\Clusters\ImpactRentCluster;
use App\Filament\GuestHouse\Resources\GuestHouseBookingResource;
use App\Filament\Resources\Orders\OrderResource;
use App\Support\ImpactRentFleetCalendar;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Carbon;
use UnitEnum;

class OrdersCalendar extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $cluster = ImpactRentCluster::class;

    protected string $view = 'filament.impact-rent.fleet-calendar';

    protected static ?string $title = 'Calendar';

    protected static string|UnitEnum|null $navigationGroup = 'Orders';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $slug = 'orders-calendar';

    protected Width|string|null $maxContentWidth = Width::Full;

    public string $from = '';

    public string $locationId = '';

    public string $span = 'month';

    public string $catalog = 'all';

    public string $statusFilter = '';

    public string $hostId = '';

    public ?int $selectedOrderId = null;

    public ?int $selectedStayId = null;

    public function mount(): void
    {
        $requested = request()->query('from');
        $this->from = is_string($requested) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $requested) === 1
            ? $requested
            : now()->toDateString();

        $catalog = request()->query('catalog');
        if (is_string($catalog) && in_array($catalog, ['all', 'cars', 'stays'], true)) {
            $this->catalog = $catalog;
        }
    }

    public function getHeading(): string|Htmlable
    {
        return '';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return null;
    }

    public function spanDays(): int
    {
        return match ($this->span) {
            'week' => 7,
            'day' => 1,
            default => 28,
        };
    }

    public function setSpan(string $span): void
    {
        $this->span = in_array($span, ['week', 'day'], true) ? $span : 'month';
        $this->clearOrder();
    }

    public function setCatalog(string $catalog): void
    {
        $this->catalog = in_array($catalog, ['cars', 'stays'], true) ? $catalog : 'all';
        $this->clearOrder();
    }

    public function shift(int $days): void
    {
        $this->from = Carbon::parse($this->from)->addDays($days)->toDateString();
        $this->clearOrder();
    }

    public function shiftSpan(int $direction): void
    {
        $this->shift($direction * $this->spanDays());
    }

    public function jumpToday(): void
    {
        $this->from = now()->toDateString();
        $this->clearOrder();
    }

    public function selectOrder(int $orderId): void
    {
        $this->selectedOrderId = $orderId;
        $this->selectedStayId = null;
    }

    public function selectStay(int $stayId): void
    {
        $this->selectedStayId = $stayId;
        $this->selectedOrderId = null;
    }

    public function clearOrder(): void
    {
        $this->selectedOrderId = null;
        $this->selectedStayId = null;
    }

    /**
     * @return array<string, mixed>
     */
    public function board(): array
    {
        return app(ImpactRentFleetCalendar::class)->present(
            $this->from,
            $this->locationId !== '' ? (int) $this->locationId : null,
            $this->selectedOrderId,
            $this->spanDays(),
            $this->catalog,
            $this->statusFilter,
            $this->hostId !== '' ? (int) $this->hostId : null,
            $this->selectedStayId,
        );
    }

    public function createBookingUrl(): string
    {
        return OrderResource::getUrl('create');
    }

    public function orderEditUrl(int $orderId): string
    {
        return OrderResource::getUrl('edit', ['record' => $orderId]);
    }

    public function stayEditUrl(int $stayId): string
    {
        return GuestHouseBookingResource::getUrl('view', ['record' => $stayId]);
    }
}

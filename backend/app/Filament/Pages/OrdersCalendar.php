<?php

namespace App\Filament\Pages;

use App\Filament\Clusters\ImpactRentCluster;
use App\Filament\Resources\Orders\OrderResource;
use App\Support\ImpactRentFleetCalendar;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
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

    public ?int $selectedOrderId = null;

    public function mount(): void
    {
        $requested = request()->query('from');
        $this->from = is_string($requested) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $requested) === 1
            ? $requested
            : now()->toDateString();
    }

    public function getHeading(): string|Htmlable
    {
        return 'Calendar';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Every vehicle on its own row. Select a booking to see the reservation.';
    }

    public function shift(int $days): void
    {
        $this->from = \Illuminate\Support\Carbon::parse($this->from)->addDays($days)->toDateString();
        $this->selectedOrderId = null;
    }

    public function jumpToday(): void
    {
        $this->from = now()->toDateString();
        $this->selectedOrderId = null;
    }

    public function selectOrder(int $orderId): void
    {
        $this->selectedOrderId = $orderId;
    }

    public function clearOrder(): void
    {
        $this->selectedOrderId = null;
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
}

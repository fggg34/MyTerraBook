<?php

namespace App\Filament\GuestHouse\Pages;

use App\Enums\GuestHouseBookingStatus;
use App\Filament\Clusters\GuestHouseCluster;
use App\Models\GuestHouse;
use App\Models\GuestHouseBooking;
use BackedEnum;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class GuestHouseCalendarPage extends Page
{
    protected static ?string $cluster = GuestHouseCluster::class;

    protected string $view = 'filament.guest-house.pages.calendar';

    protected static ?string $title = 'Bookings Calendar';

    protected static string|UnitEnum|null $navigationGroup = 'Bookings';

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static ?string $slug = 'calendar';

    /** @var array<int, array{house: string, cells: array<int, array{date: string, day: string, bookings: int}>}> */
    public array $rows = [];

    public function mount(): void
    {
        $start = now()->startOfMonth();
        $end = $start->copy()->addMonth()->endOfMonth();

        $bookings = GuestHouseBooking::query()
            ->whereIn('status', [GuestHouseBookingStatus::Confirmed, GuestHouseBookingStatus::Pending])
            ->where('check_in', '<=', $end)
            ->where('check_out', '>=', $start)
            ->with('guestHouse')
            ->get();

        $houses = GuestHouse::query()->orderBy('name')->get();

        foreach ($houses as $house) {
            $cells = [];
            $cursor = $start->copy();
            while ($cursor->lte($end)) {
                $date = $cursor->toDateString();
                $count = $bookings->filter(
                    fn (GuestHouseBooking $b) => $b->guest_house_id === $house->id
                        && $b->check_in->toDateString() <= $date
                        && $b->check_out->toDateString() > $date,
                )->count();
                $cells[] = [
                    'date' => $date,
                    'day' => $cursor->format('d'),
                    'bookings' => $count,
                ];
                $cursor->addDay();
            }
            $this->rows[] = ['house' => $house->name, 'cells' => $cells];
        }
    }
}

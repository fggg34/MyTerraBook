<?php

namespace App\Filament\GuestHouse\Resources\GuestHouseBookingResource\Pages;

use App\Enums\GuestHouseBookingStatus;
use App\Filament\Concerns\EmbedsCatalogTable;
use App\Filament\GuestHouse\Resources\GuestHouseBookingResource;
use App\Filament\Pages\OrdersCalendar;
use App\Models\GuestHouseBooking;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;

class ListGuestHouseBookings extends ListRecords
{
    use EmbedsCatalogTable;

    protected static string $resource = GuestHouseBookingResource::class;

    protected static ?string $title = 'Guest house bookings';

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
                ->badge(GuestHouseBooking::query()->count()),
            'pending' => Tab::make('Pending')
                ->badge(GuestHouseBooking::query()->where('status', GuestHouseBookingStatus::Pending)->count())
                ->modifyQueryUsing(fn ($query) => $query->where('status', GuestHouseBookingStatus::Pending)),
            'confirmed' => Tab::make('Confirmed')
                ->badge(GuestHouseBooking::query()->where('status', GuestHouseBookingStatus::Confirmed)->count())
                ->modifyQueryUsing(fn ($query) => $query->where('status', GuestHouseBookingStatus::Confirmed)),
            'cancelled' => Tab::make('Cancelled')
                ->badge(GuestHouseBooking::query()->where('status', GuestHouseBookingStatus::Cancelled)->count())
                ->modifyQueryUsing(fn ($query) => $query->where('status', GuestHouseBookingStatus::Cancelled)),
        ];
    }

    public function mount(): void
    {
        parent::mount();
        $this->tableFilters = array_replace_recursive([
            'status' => ['value' => null],
            'guest_house_id' => ['value' => null],
            'date_range' => ['from' => null, 'to' => null],
        ], $this->tableFilters ?? []);
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'all';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('calendarView')
                ->label('Calendar view')
                ->icon(Heroicon::OutlinedCalendarDays)
                ->color('gray')
                ->outlined()
                ->url(OrdersCalendar::getUrl().'?catalog=stays'),
        ];
    }
}

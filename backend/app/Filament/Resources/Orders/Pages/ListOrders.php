<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\OrderStatus;
use App\Enums\RentalStatus;
use App\Filament\Pages\OrdersCalendar;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected static ?string $title = 'Car reservations';

    protected array $extraBodyAttributes = [
        'class' => 'ir-orders-page',
    ];

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
                ->badge(Order::query()->count() ?: null),
            'pending' => Tab::make('Pending')
                ->badge(Order::query()->where('order_status', OrderStatus::Pending)->count() ?: null)
                ->modifyQueryUsing(fn ($query) => $query->where('order_status', OrderStatus::Pending)),
            'confirmed' => Tab::make('Confirmed')
                ->badge(Order::query()
                    ->where('order_status', OrderStatus::Confirmed)
                    ->where(fn ($query) => $query->whereNull('rental_status')->orWhere('rental_status', RentalStatus::Upcoming))
                    ->count() ?: null)
                ->modifyQueryUsing(fn ($query) => $query
                    ->where('order_status', OrderStatus::Confirmed)
                    ->where(fn ($inner) => $inner->whereNull('rental_status')->orWhere('rental_status', RentalStatus::Upcoming))),
            'on_the_road' => Tab::make('On the road')
                ->badge(Order::query()->where('rental_status', RentalStatus::Started)->count() ?: null)
                ->modifyQueryUsing(fn ($query) => $query->where('rental_status', RentalStatus::Started)),
            'returned' => Tab::make('Returned')
                ->badge(Order::query()->where('rental_status', RentalStatus::Terminated)->count() ?: null)
                ->modifyQueryUsing(fn ($query) => $query->where('rental_status', RentalStatus::Terminated)),
            'cancelled' => Tab::make('Cancelled')
                ->badge(Order::query()->where('order_status', OrderStatus::Cancelled)->count() ?: null)
                ->modifyQueryUsing(fn ($query) => $query->where('order_status', OrderStatus::Cancelled)),
        ];
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
                ->url(OrdersCalendar::getUrl()),
            CreateAction::make()->label('New booking'),
        ];
    }
}

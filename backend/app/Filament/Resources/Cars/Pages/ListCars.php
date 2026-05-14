<?php

namespace App\Filament\Resources\Cars\Pages;

use App\Filament\Resources\Cars\CarResource;
use App\Filament\Resources\DailyFares\DailyFareResource;
use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListCars extends ListRecords
{
    protected static string $resource = CarResource::class;

    public function getPageClasses(): array
    {
        return [
            ...parent::getPageClasses(),
            'ir-cars-page',
            'ir-cars-page--list',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('editViewFares')
                ->label('Edit/View Fares')
                ->icon(Heroicon::OutlinedCurrencyDollar)
                ->color('gray')
                ->url(DailyFareResource::getUrl('index'))
                ->tooltip('Open Fares Table'),
            Action::make('carsCalendar')
                ->label('Cars Calendar')
                ->icon(Heroicon::OutlinedCalendarDays)
                ->color('gray')
                ->url(OrderResource::getUrl('index'))
                ->tooltip('Open Orders (calendar-style scheduling from bookings)'),
            CreateAction::make(),
        ];
    }
}

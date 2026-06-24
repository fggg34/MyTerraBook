<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use App\Enums\RentalStatus;
use App\Models\AvailabilityBlock;
use App\Models\Car;
use App\Models\CarUnit;
use App\Models\Order;
use App\Models\PaymentMethod;
use Carbon\Carbon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('reference')
                    ->disabled()
                    ->dehydrated(false)
                    ->visibleOn('edit'),
                TextInput::make('confirmation_url')
                    ->label('Confirmation page URL')
                    ->disabled()
                    ->dehydrated(false)
                    ->visibleOn('edit')
                    ->columnSpanFull(),
                Grid::make([
                    'default' => 1,
                    'xl' => 12,
                ])
                    ->columnSpanFull()
                    ->schema([
                        Section::make('Quick Reservation')
                            ->columnSpan([
                                'default' => 1,
                                'xl' => 5,
                            ])
                            ->schema([
                                Select::make('car_id')
                                    ->label('Car')
                                    ->relationship('car', 'name')
                                    ->live()
                                    ->required(),
                                Grid::make([
                                    'default' => 1,
                                    'md' => 2,
                                ])
                                    ->schema([
                                        DateTimePicker::make('pickup_at')
                                            ->label('Pickup Date and Time')
                                            ->live()
                                            ->required(),
                                        DateTimePicker::make('dropoff_at')
                                            ->label('Drop Off Date and Time')
                                            ->live()
                                            ->required(),
                                    ]),
                                Select::make('car_unit_id')
                                    ->label('Car Unit')
                                    ->options(
                                        fn (): array => CarUnit::query()
                                            ->with('car')
                                            ->orderBy('id')
                                            ->get()
                                            ->mapWithKeys(fn (CarUnit $u): array => [
                                                $u->id => ($u->car?->name ?? 'Car').' · unit #'.$u->id,
                                            ])
                                            ->all()
                                    )
                                    ->searchable(),
                                Select::make('price_type_id')
                                    ->relationship('priceType', 'name'),
                                Placeholder::make('stop_rentals_hint')
                                    ->label('Stop rentals on these dates')
                                    ->content('To block dates for this car, add an availability block from the car\'s record. Blocked dates appear in red on the calendar.'),
                                Select::make('pickup_location_id')
                                    ->label('Pickup Location')
                                    ->relationship('pickupLocation', 'name')
                                    ->required(),
                                Select::make('dropoff_location_id')
                                    ->label('Drop Off Location')
                                    ->relationship('dropoffLocation', 'name')
                                    ->required(),
                                Select::make('order_status')
                                    ->label('Order Status')
                                    ->options(collect(OrderStatus::cases())->mapWithKeys(fn (OrderStatus $s) => [$s->value => str_replace('_', ' ', ucfirst($s->value))]))
                                    ->default(OrderStatus::Pending->value)
                                    ->required(),
                                Select::make('rental_status')
                                    ->label('Rental Status')
                                    ->options(collect(RentalStatus::cases())->mapWithKeys(fn (RentalStatus $s) => [$s->value => str_replace('_', ' ', ucfirst($s->value))])),
                                Select::make('payment_method_preview')
                                    ->label('Payment Method')
                                    ->options(fn (): array => ['' => '-undefined-'] + PaymentMethod::query()
                                        ->where('is_enabled', true)
                                        ->orderBy('sort_order')
                                        ->pluck('name', 'code')
                                        ->all())
                                    ->default('')
                                    ->dehydrated(false),
                                Select::make('user_id')
                                    ->label('Assign Customer')
                                    ->relationship('user', 'name')
                                    ->searchable(),
                                TextInput::make('customer_name')
                                    ->label('Customer Name')
                                    ->required(),
                                TextInput::make('customer_email')
                                    ->label('E-Mail')
                                    ->email()
                                    ->required(),
                                TextInput::make('customer_phone')
                                    ->label('Phone')
                                    ->tel(),
                                TextInput::make('customer_country')
                                    ->label('Country'),
                                Textarea::make('notes')
                                    ->label('Customer Information')
                                    ->rows(5),
                            ]),

                        Section::make('Car Reservation Calendar')
                            ->columnSpan([
                                'default' => 1,
                                'xl' => 7,
                            ])
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'md' => 4,
                                ])
                                    ->schema([
                                        Select::make('calendar_months_ahead')
                                            ->label('Calendar Range')
                                            ->options([
                                                3 => '3 Months',
                                                6 => '6 Months',
                                                12 => '1 Year',
                                            ])
                                            ->default(6)
                                            ->live()
                                            ->dehydrated(false),
                                        TextInput::make('calendar_sync_link')
                                            ->label('iCal Sync Link')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->placeholder('Available once car has iCal URL'),
                                    ]),

                                View::make('filament.resources.orders.components.car-reservation-calendar')
                                    ->columnSpanFull()
                                    ->viewData(fn (Get $get, ?object $record): array => self::buildCalendarViewData($get, $record)),
                            ]),

                        Section::make('Reservation Costs')
                            ->columnSpan([
                                'default' => 1,
                                'xl' => 12,
                            ])
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'md' => 2,
                                    'xl' => 4,
                                ])
                                    ->schema([
                                        TextInput::make('base_rental_cents')
                                            ->label('Rental Cost (cents)')
                                            ->required()
                                            ->numeric()
                                            ->default(0),
                                        TextInput::make('total_cents')
                                            ->label('Total (cents)')
                                            ->required()
                                            ->numeric()
                                            ->default(0),
                                        TextInput::make('extras_cents')
                                            ->label('Extras (cents)')
                                            ->required()
                                            ->numeric()
                                            ->default(0),
                                        TextInput::make('fees_cents')
                                            ->label('Fees (cents)')
                                            ->required()
                                            ->numeric()
                                            ->default(0),
                                        TextInput::make('discount_cents')
                                            ->label('Discount (cents)')
                                            ->required()
                                            ->numeric()
                                            ->default(0),
                                        TextInput::make('tax_cents')
                                            ->label('Tax (cents)')
                                            ->required()
                                            ->numeric()
                                            ->default(0),
                                        TextInput::make('currency')
                                            ->required()
                                            ->default('EUR'),
                                        Select::make('coupon_id')
                                            ->relationship('coupon', 'code'),
                                    ]),

                                Placeholder::make('amount_paid_preview')
                                    ->label('Amount Paid')
                                    ->content(function (?object $record): string {
                                        if (! $record || ! method_exists($record, 'payments')) {
                                            return '-';
                                        }

                                        $paid = (int) $record->payments()->sum('amount_cents');

                                        return number_format($paid, 0).' '.((string) ($record->currency ?? 'EUR'));
                                    }),
                                Placeholder::make('remaining_balance_preview')
                                    ->label('Remaining Balance')
                                    ->content(function (?object $record): string {
                                        if (! $record || ! method_exists($record, 'payments')) {
                                            return '-';
                                        }

                                        $paid = (int) $record->payments()->sum('amount_cents');
                                        $remaining = max(0, (int) ($record->total_cents ?? 0) - $paid);

                                        return number_format($remaining, 0).' '.((string) ($record->currency ?? 'EUR'));
                                    }),
                            ]),
                    ]),

                Section::make('Administration')
                    ->columnSpanFull()
                    ->schema([
                        Textarea::make('admin_internal_note')
                            ->label('Internal Note')
                            ->rows(4)
                            ->columnSpanFull(),
                        Grid::make([
                            'default' => 1,
                            'md' => 2,
                        ])
                            ->schema([
                                Select::make('created_by_admin_id')
                                    ->relationship('createdByAdmin', 'name'),
                                DateTimePicker::make('payment_lock_expires_at'),
                            ]),
                    ]),
            ]);
    }

    /**
     * @return array{
     *     carName: string,
     *     carImageUrl: ?string,
     *     calendarLabel: string,
     *     months: array<int, array{label: string, weeks: array<int, array<int, array{date: string, day: string, inMonth: bool, blocked: bool, selected: bool}>>>}
     * }
     */
    private static function buildCalendarViewData(Get $get, ?object $record): array
    {
        $carId = (int) ($get('car_id') ?? 0);
        $monthsAhead = max(3, min(12, (int) ($get('calendar_months_ahead') ?? 6)));
        $pickupRaw = $get('pickup_at');
        $dropoffRaw = $get('dropoff_at');

        $car = $carId > 0 ? Car::query()->find($carId) : null;

        if ($car !== null && filled($car->ical_import_url)) {
            $getContainer = function () use ($get): ?object {
                try {
                    return $get->getContainer();
                } catch (\Throwable) {
                    return null;
                }
            };

            $container = $getContainer();
            if ($container && method_exists($container, 'getComponent')) {
                $component = $container->getComponent('calendar_sync_link');
                if ($component) {
                    $component->state((string) $car->ical_import_url);
                }
            }
        }

        $selectedStart = filled($pickupRaw) ? Carbon::parse($pickupRaw)->startOfDay() : null;
        $selectedEnd = filled($dropoffRaw) ? Carbon::parse($dropoffRaw)->startOfDay() : null;

        $orderRanges = [];
        if ($carId > 0) {
            $query = Order::query()
                ->where('car_id', $carId)
                ->whereIn('order_status', [OrderStatus::Confirmed->value, OrderStatus::StandBy->value]);

            if ($record && isset($record->id)) {
                $query->whereKeyNot($record->id);
            }

            foreach ($query->get(['pickup_at', 'dropoff_at']) as $order) {
                if (! $order->pickup_at || ! $order->dropoff_at) {
                    continue;
                }

                $orderRanges[] = [
                    'from' => $order->pickup_at->copy()->startOfDay(),
                    'to' => $order->dropoff_at->copy()->startOfDay(),
                ];
            }
        }

        $blockRanges = [];
        if ($carId > 0) {
            foreach (AvailabilityBlock::query()->where('car_id', $carId)->where('is_active', true)->get(['starts_at', 'ends_at']) as $block) {
                if (! $block->starts_at || ! $block->ends_at) {
                    continue;
                }

                $blockRanges[] = [
                    'from' => $block->starts_at->copy()->startOfDay(),
                    'to' => $block->ends_at->copy()->startOfDay(),
                ];
            }
        }

        $months = [];
        $cursor = now()->startOfMonth();

        for ($i = 0; $i < $monthsAhead; $i++) {
            $monthStart = $cursor->copy()->addMonths($i);
            $gridStart = $monthStart->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
            $gridEnd = $monthStart->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

            $weeks = [];
            $week = [];

            $dayCursor = $gridStart->copy();
            while ($dayCursor->lessThanOrEqualTo($gridEnd)) {
                $isBlocked = false;

                foreach ($orderRanges as $range) {
                    if ($dayCursor->betweenIncluded($range['from'], $range['to']->copy()->subDay())) {
                        $isBlocked = true;
                        break;
                    }
                }

                if (! $isBlocked) {
                    foreach ($blockRanges as $range) {
                        if ($dayCursor->betweenIncluded($range['from'], $range['to'])) {
                            $isBlocked = true;
                            break;
                        }
                    }
                }

                $isSelected = false;
                if ($selectedStart && $selectedEnd && $selectedEnd->greaterThan($selectedStart)) {
                    $isSelected = $dayCursor->betweenIncluded($selectedStart, $selectedEnd->copy()->subDay());
                }

                $week[] = [
                    'date' => $dayCursor->toDateString(),
                    'day' => $dayCursor->format('d'),
                    'inMonth' => $dayCursor->month === $monthStart->month,
                    'blocked' => $isBlocked,
                    'selected' => $isSelected,
                ];

                if (count($week) === 7) {
                    $weeks[] = $week;
                    $week = [];
                }

                $dayCursor->addDay();
            }

            $months[] = [
                'label' => $monthStart->format('F Y'),
                'weeks' => $weeks,
            ];
        }

        return [
            'carName' => (string) ($car?->name ?? 'Select a car'),
            'carImageUrl' => self::resolveCarImageUrl($car),
            'calendarLabel' => $monthsAhead === 12 ? '1 Year' : ($monthsAhead.' Months'),
            'months' => $months,
        ];
    }

    private static function resolveCarImageUrl(?Car $car): ?string
    {
        if (! $car || blank($car->main_image_path)) {
            return null;
        }

        $path = (string) $car->main_image_path;

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset('storage/'.$path);
    }
}

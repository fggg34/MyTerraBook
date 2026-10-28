<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use App\Enums\RentalStatus;
use App\Filament\Pages\OrdersCalendar;
use App\Models\AvailabilityBlock;
use App\Models\Car;
use App\Models\CarUnit;
use App\Models\CustomField;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Support\AdminMoney;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
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
                Grid::make([
                    'default' => 1,
                    'lg' => 12,
                ])
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(1)
                            ->columnSpan(['default' => 1, 'lg' => 8])
                            ->extraAttributes(['class' => 'tb-edit-main'])
                            ->schema([
                                Section::make('Rental')
                                    ->description('Vehicle, places and times.')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            Select::make('car_id')
                                                ->label('Vehicle')
                                                ->relationship('car', 'name')
                                                ->live()
                                                ->required()
                                                ->searchable(),
                                            Select::make('car_unit_id')
                                                ->label('Fleet unit')
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
                                            Select::make('pickup_location_id')
                                                ->label('Pickup location')
                                                ->relationship('pickupLocation', 'name')
                                                ->required()
                                                ->searchable(),
                                            Select::make('dropoff_location_id')
                                                ->label('Drop-off location')
                                                ->relationship('dropoffLocation', 'name')
                                                ->required()
                                                ->searchable(),
                                        ]),
                                        Grid::make(4)->schema([
                                            DatePicker::make('pickup_date')
                                                ->label('Pickup date')
                                                ->displayFormat('d/m/Y')
                                                ->native(false)
                                                ->live()
                                                ->required()
                                                ->dehydrated(false),
                                            TimePicker::make('pickup_time')
                                                ->label('Pickup time')
                                                ->seconds(false)
                                                ->displayFormat('H:i')
                                                ->live()
                                                ->required()
                                                ->dehydrated(false),
                                            DatePicker::make('dropoff_date')
                                                ->label('Drop-off date')
                                                ->displayFormat('d/m/Y')
                                                ->native(false)
                                                ->live()
                                                ->required()
                                                ->dehydrated(false),
                                            TimePicker::make('dropoff_time')
                                                ->label('Drop-off time')
                                                ->seconds(false)
                                                ->displayFormat('H:i')
                                                ->live()
                                                ->required()
                                                ->dehydrated(false),
                                        ]),
                                    ]),
                                Section::make('Customer')
                                    ->description('Who is driving. Link to an existing client to see their history.')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            TextInput::make('customer_name')
                                                ->label('Customer name')
                                                ->required(),
                                            Select::make('user_id')
                                                ->label('Linked client account')
                                                ->relationship('user', 'name')
                                                ->searchable()
                                                ->placeholder('Not linked'),
                                            TextInput::make('customer_email')
                                                ->label('E-mail')
                                                ->email()
                                                ->required(),
                                            TextInput::make('customer_phone')
                                                ->label('Phone')
                                                ->tel(),
                                            TextInput::make('customer_country')
                                                ->label('Country'),
                                            Select::make('price_type_id')
                                                ->label('Price type')
                                                ->relationship('priceType', 'name'),
                                            ...self::customFieldInputs(),
                                            Textarea::make('notes')
                                                ->label('Customer information')
                                                ->placeholder('Notes visible on the confirmation...')
                                                ->rows(3)
                                                ->columnSpanFull(),
                                        ]),
                                    ]),
                                Section::make('Price breakdown')
                                    ->description('Amounts in ISK. Stored the same way as today, shown without cents.')
                                    ->schema([
                                        Grid::make(3)->schema([
                                            self::moneyInput('base_rental_cents', 'Rental cost'),
                                            self::moneyInput('extras_cents', 'Extras'),
                                            self::moneyInput('fees_cents', 'Fees'),
                                            self::moneyInput('discount_cents', 'Discount'),
                                            self::moneyInput('tax_cents', 'Tax'),
                                            Select::make('coupon_id')
                                                ->label('Coupon')
                                                ->relationship('coupon', 'code')
                                                ->placeholder('No coupon'),
                                        ]),
                                        View::make('filament.resources.orders.components.price-totals')
                                            ->columnSpanFull()
                                            ->viewData(fn (Get $get, ?object $record): array => self::totalsViewData($get, $record)),
                                        TextInput::make('currency')
                                            ->hidden()
                                            ->dehydrated()
                                            ->default('ISK'),
                                    ]),
                                Section::make('Status & administration')
                                    ->schema([
                                        Grid::make(3)->schema([
                                            Select::make('order_status')
                                                ->label('Order status')
                                                ->options([
                                                    OrderStatus::Pending->value => 'Pending',
                                                    OrderStatus::StandBy->value => 'Stand by',
                                                    OrderStatus::Confirmed->value => 'Confirmed',
                                                    OrderStatus::Cancelled->value => 'Cancelled',
                                                ])
                                                ->default(OrderStatus::Pending->value)
                                                ->required(),
                                            Select::make('rental_status')
                                                ->label('Rental status')
                                                ->options([
                                                    RentalStatus::Upcoming->value => 'Upcoming',
                                                    RentalStatus::Started->value => 'Started',
                                                    RentalStatus::Terminated->value => 'Terminated',
                                                    RentalStatus::NoShow->value => 'No show',
                                                ]),
                                            Select::make('payment_method_preview')
                                                ->label('Payment method')
                                                ->options(fn (): array => ['' => '-'] + PaymentMethod::query()
                                                    ->where('is_enabled', true)
                                                    ->orderBy('sort_order')
                                                    ->pluck('name', 'code')
                                                    ->all())
                                                ->default('')
                                                ->dehydrated(false),
                                            Select::make('created_by_admin_id')
                                                ->label('Created by admin')
                                                ->relationship('createdByAdmin', 'name'),
                                            DateTimePicker::make('payment_lock_expires_at')
                                                ->label('Payment lock expires'),
                                            Textarea::make('admin_internal_note')
                                                ->label('Internal note')
                                                ->placeholder('Only admins see this.')
                                                ->rows(3)
                                                ->columnSpanFull(),
                                        ]),
                                    ]),
                            ]),
                        Grid::make(1)
                            ->columnSpan(['default' => 1, 'lg' => 4])
                            ->extraAttributes(['class' => 'tb-edit-side'])
                            ->schema([
                                Section::make('Vehicle availability')
                                    ->schema([
                                        View::make('filament.resources.orders.components.car-reservation-calendar')
                                            ->columnSpanFull()
                                            ->viewData(fn (Get $get, ?object $record): array => self::buildCalendarViewData($get, $record, compact: true)),
                                        Placeholder::make('open_full_calendar')
                                            ->hiddenLabel()
                                            ->content(new \Illuminate\Support\HtmlString(
                                                '<a class="tb-calendar-open" href="'.e(OrdersCalendar::getUrl()).'">Open full calendar</a>'
                                            )),
                                    ]),
                                View::make('filament.resources.orders.components.order-activity')
                                    ->viewData(fn (?object $record): array => [
                                        'events' => self::activityEvents($record),
                                    ]),
                                View::make('filament.resources.orders.components.order-danger'),
                            ]),
                    ]),
            ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function applySchedule(array $data): array
    {
        $data['pickup_at'] = self::combineDateAndTime($data['pickup_date'] ?? null, $data['pickup_time'] ?? null);
        $data['dropoff_at'] = self::combineDateAndTime($data['dropoff_date'] ?? null, $data['dropoff_time'] ?? null);
        unset($data['pickup_date'], $data['pickup_time'], $data['dropoff_date'], $data['dropoff_time']);

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function splitSchedule(array $data): array
    {
        foreach (['pickup', 'dropoff'] as $key) {
            $at = $data[$key.'_at'] ?? null;
            if (filled($at)) {
                $parsed = Carbon::parse($at);
                $data[$key.'_date'] = $parsed->toDateString();
                $data[$key.'_time'] = $parsed->format('H:i');
            }
        }

        return $data;
    }

    private static function combineDateAndTime(mixed $date, mixed $time): ?string
    {
        if (blank($date)) {
            return null;
        }

        $clock = filled($time) ? Carbon::parse($time)->format('H:i:s') : '00:00:00';

        return Carbon::parse($date)->format('Y-m-d').' '.$clock;
    }

    /**
     * @return list<TextInput>
     */
    private static function customFieldInputs(): array
    {
        return CustomField::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (CustomField $field): TextInput => TextInput::make('custom_field_values.'.$field->field_key)
                ->label($field->label ?: $field->field_key)
                ->helperText('Custom field'))
            ->all();
    }

    private static function moneyInput(string $name, string $label): TextInput
    {
        return TextInput::make($name)
            ->label($label)
            ->numeric()
            ->default(0)
            ->prefix('ISK')
            ->live()
            ->afterStateHydrated(function (TextInput $component, $state): void {
                $component->state($state === null || $state === '' ? 0 : (int) intdiv((int) $state, 100));
            })
            ->dehydrateStateUsing(fn ($state) => (int) round(((float) $state) * 100));
    }

    /**
     * @return array{total: string, paid: string, due: string, currency: string}
     */
    private static function totalsViewData(Get $get, ?object $record): array
    {
        $currency = (string) ($get('currency') ?: $record?->currency ?: 'ISK');
        $rental = (int) round(((float) ($get('base_rental_cents') ?? 0)) * 100);
        $extras = (int) round(((float) ($get('extras_cents') ?? 0)) * 100);
        $fees = (int) round(((float) ($get('fees_cents') ?? 0)) * 100);
        $discount = (int) round(((float) ($get('discount_cents') ?? 0)) * 100);
        $tax = (int) round(((float) ($get('tax_cents') ?? 0)) * 100);
        $total = max(0, $rental + $extras + $fees - $discount + $tax);
        $paid = 0;
        if ($record instanceof Order) {
            $paid = (int) $record->payments()->sum('amount_cents');
        }

        return [
            'total' => AdminMoney::fromCents($total, $currency),
            'paid' => AdminMoney::fromCents($paid, $currency),
            'due' => AdminMoney::fromCents(max(0, $total - $paid), $currency),
            'currency' => strtoupper($currency),
        ];
    }

    /**
     * @return list<array{label: string, at: string}>
     */
    private static function activityEvents(?object $record): array
    {
        if (! $record instanceof Order) {
            return [];
        }

        $events = [
            ['label' => 'Order created', 'at' => $record->created_at?->format('j M Y, H:i') ?? ''],
        ];

        if ($record->order_status === OrderStatus::Confirmed) {
            $events[] = ['label' => 'Confirmation e-mail sent', 'at' => $record->updated_at?->format('j M Y, H:i') ?? ''];
        }

        if ($record->payment_lock_expires_at) {
            $expired = $record->payment_lock_expires_at->isPast();
            $events[] = [
                'label' => $expired ? 'Payment lock expired' : 'Payment lock',
                'at' => $record->payment_lock_expires_at->format('j M Y, H:i'),
            ];
        }

        return $events;
    }

    /**
     * @return array{
     *     carName: string,
     *     carImageUrl: ?string,
     *     calendarLabel: string,
     *     compact: bool,
     *     months: array<int, mixed>
     * }
     */
    private static function buildCalendarViewData(Get $get, ?object $record, bool $compact = false): array
    {
        $carId = (int) ($get('car_id') ?? 0);
        $monthsAhead = $compact ? 1 : max(3, min(12, (int) ($get('calendar_months_ahead') ?? 6)));
        $pickupRaw = filled($get('pickup_date'))
            ? trim((string) $get('pickup_date').' '.((string) ($get('pickup_time') ?: '00:00')))
            : $get('pickup_at');
        $dropoffRaw = filled($get('dropoff_date'))
            ? trim((string) $get('dropoff_date').' '.((string) ($get('dropoff_time') ?: '00:00')))
            : $get('dropoff_at');

        $car = $carId > 0 ? Car::query()->find($carId) : null;

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
        $cursor = $selectedStart ? $selectedStart->copy()->startOfMonth() : now()->startOfMonth();

        for ($i = 0; $i < $monthsAhead; $i++) {
            $monthStart = $cursor->copy()->addMonths($i);
            $gridStart = $monthStart->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
            $gridEnd = $monthStart->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

            $weeks = [];
            $week = [];

            $dayCursor = $gridStart->copy();
            while ($dayCursor->lessThanOrEqualTo($gridEnd)) {
                $kind = null;

                if ($selectedStart && $selectedEnd && $selectedEnd->greaterThan($selectedStart)
                    && $dayCursor->betweenIncluded($selectedStart, $selectedEnd->copy()->subDay())) {
                    $kind = 'this';
                }

                if ($kind === null) {
                    foreach ($orderRanges as $range) {
                        if ($dayCursor->betweenIncluded($range['from'], $range['to']->copy()->subDay())) {
                            $kind = 'other';
                            break;
                        }
                    }
                }

                if ($kind === null) {
                    foreach ($blockRanges as $range) {
                        if ($dayCursor->betweenIncluded($range['from'], $range['to'])) {
                            $kind = 'blocked';
                            break;
                        }
                    }
                }

                $week[] = [
                    'date' => $dayCursor->toDateString(),
                    'day' => $dayCursor->format('j'),
                    'inMonth' => $dayCursor->month === $monthStart->month,
                    'kind' => $kind,
                    'blocked' => $kind === 'blocked' || $kind === 'other',
                    'selected' => $kind === 'this',
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
            'carImageUrl' => $compact ? null : self::resolveCarImageUrl($car),
            'calendarLabel' => $compact ? ($months[0]['label'] ?? '') : ($monthsAhead === 12 ? '1 year' : ($monthsAhead.' months')),
            'compact' => $compact,
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

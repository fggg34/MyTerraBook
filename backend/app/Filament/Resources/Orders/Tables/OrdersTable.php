<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderStatus;
use App\Enums\RentalStatus;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Support\AdminTableBadgeColors;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Support\Enums\VerticalAlignment;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['car', 'pickupLocation', 'dropoffLocation', 'payments']))
            ->defaultSort('created_at', 'desc')
            ->searchPlaceholder('Search bookings…')
            ->striped()
            ->stackedOnMobile()
            ->columns([
                TextColumn::make('partner_sync_state')->label('Partner sync')->badge()->toggleable(isToggledHiddenByDefault: true)
                    ->description(fn ($record) => $record->partner_sync_error)
                    ->wrap()
                    ->verticalAlignment(VerticalAlignment::Start),
                TextColumn::make('id')
                    ->label('Reference')
                    ->formatStateUsing(fn ($record): string => $record->reference ?: (string) $record->id)
                    ->badge()
                    ->color(fn (): string => AdminTableBadgeColors::neutral())
                    ->sortable()
                    ->searchable()
                    ->wrap()
                    ->verticalAlignment(VerticalAlignment::Start),
                TextColumn::make('created_at')
                    ->label('Booked')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->date('j M Y')
                    ->description(fn ($record): string => $record->created_at?->format('D H:i') ?? '-')
                    ->sortable()
                    ->wrap()
                    ->verticalAlignment(VerticalAlignment::Start),
                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->description(fn ($record): string => trim(($record->customer_email ?? '').' '.($record->customer_phone ?? '')) ?: '-')
                    ->wrap()
                    ->verticalAlignment(VerticalAlignment::Start),
                TextColumn::make('car.name')
                    ->label('Vehicle')
                    ->searchable()
                    ->wrap()
                    ->verticalAlignment(VerticalAlignment::Start),
                TextColumn::make('pickup_at')
                    ->label('Pickup → Drop-off')
                    ->date('d/m/Y')
                    ->description(fn ($record): string => trim(($record->pickupLocation?->name ?? '').' '.($record->pickup_at?->format('D H:i') ?? '')) ?: '-')
                    ->sortable()
                    ->wrap()
                    ->verticalAlignment(VerticalAlignment::Start),
                TextColumn::make('dropoff_at')
                    ->label('Drop-off')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->date('d/m/Y')
                    ->description(fn ($record): string => trim(($record->dropoffLocation?->name ?? '').' '.($record->dropoff_at?->format('D H:i') ?? '')) ?: '-')
                    ->sortable()
                    ->wrap()
                    ->verticalAlignment(VerticalAlignment::Start),
                TextColumn::make('rental_days')
                    ->label('Days')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->state(fn ($record): int => max(1, (int) $record->pickup_at?->diffInDays($record->dropoff_at)))
                    ->alignCenter()
                    ->verticalAlignment(VerticalAlignment::Start),
                TextColumn::make('total_cents')
                    ->label('Total')
                    ->state(fn ($record): string => static::formatMoney((int) $record->total_cents, (string) $record->currency))
                    ->description(function ($record): string {
                        $paid = (int) $record->payments->sum('amount_cents');
                        $remaining = max(0, (int) $record->total_cents - $paid);

                        return 'Due '.static::formatMoney($remaining, (string) $record->currency);
                    })
                    ->wrap()
                    ->verticalAlignment(VerticalAlignment::Start),
                TextColumn::make('order_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (mixed $state): string => AdminTableBadgeColors::orderStatus($state))
                    ->formatStateUsing(fn (mixed $state): string => AdminTableBadgeColors::humanize($state))
                    ->searchable()
                    ->wrap()
                    ->verticalAlignment(VerticalAlignment::Start),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                static::textFilter('reference', 'Booking ID', 'ID, confirmation or partner reference', function (Builder $query, string $term): void {
                    $query->where(function (Builder $nested) use ($term): void {
                        $nested->where('reference', 'like', '%'.$term.'%')
                            ->orWhere('external_reference', 'like', '%'.$term.'%');
                        if (ctype_digit($term)) {
                            $nested->orWhere('id', (int) $term);
                        }
                    });
                }),
                static::textFilter('customer', 'Customer', 'Name, email or phone', function (Builder $query, string $term): void {
                    $query->where(function (Builder $nested) use ($term): void {
                        $nested->where('customer_name', 'like', '%'.$term.'%')
                            ->orWhere('customer_email', 'like', '%'.$term.'%')
                            ->orWhere('customer_phone', 'like', '%'.$term.'%');
                    });
                }),
                SelectFilter::make('order_status')
                    ->label('Booking status')
                    ->options(static::enumOptions(OrderStatus::cases()))
                    ->native(false),
                SelectFilter::make('rental_status')
                    ->label('Rental progress')
                    ->options(static::enumOptions(RentalStatus::cases()))
                    ->native(false),
                SelectFilter::make('car')
                    ->label('Vehicle')
                    ->relationship('car', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),
                SelectFilter::make('pickup_location')
                    ->label('Pickup location')
                    ->relationship('pickupLocation', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),
                SelectFilter::make('dropoff_location')
                    ->label('Drop-off location')
                    ->relationship('dropoffLocation', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),
                static::dateFilter('pickup_from', 'Pickup from', 'pickup_at', '>='),
                static::dateFilter('pickup_until', 'Pickup until', 'pickup_at', '<='),
                static::dateFilter('dropoff_from', 'Drop-off from', 'dropoff_at', '>='),
                static::dateFilter('dropoff_until', 'Drop-off until', 'dropoff_at', '<='),
                static::dateFilter('booked_from', 'Booked from', 'created_at', '>='),
                static::dateFilter('booked_until', 'Booked until', 'created_at', '<='),
                SelectFilter::make('payment')
                    ->label('Payment')
                    ->options([
                        'unpaid' => 'Unpaid',
                        'partial' => 'Part paid',
                        'paid' => 'Paid in full',
                    ])
                    ->native(false)
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;
                        if (! filled($value)) {
                            return $query;
                        }

                        $paid = static::paidCentsSql();
                        $total = $query->getModel()->qualifyColumn('total_cents');

                        return match ($value) {
                            'unpaid' => $query->whereRaw($paid.' = 0'),
                            'partial' => $query->whereRaw($paid.' > 0 and '.$paid.' < '.$total),
                            'paid' => $query->whereRaw($total.' > 0 and '.$paid.' >= '.$total),
                            default => $query,
                        };
                    }),
                SelectFilter::make('source')
                    ->label('Source')
                    ->options([
                        'myterrabook' => 'MyTerraBook',
                        'caren' => 'Caren',
                        'greenlight' => 'Greenlight',
                        'foxie' => 'Foxie',
                    ])
                    ->native(false)
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;
                        if (! filled($value)) {
                            return $query;
                        }

                        if ($value === 'myterrabook') {
                            return $query->where(function (Builder $nested): void {
                                $nested->whereNull('external_provider')->orWhere('external_provider', '');
                            });
                        }

                        return $query->where('external_provider', $value);
                    }),
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->deferFilters(false)
            ->filtersFormColumns([
                'sm' => 2,
                'lg' => 3,
                'xl' => 4,
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function textFilter(string $name, string $label, string $placeholder, \Closure $apply): Filter
    {
        return Filter::make($name)
            ->label($label)
            ->form([
                TextInput::make('value')
                    ->label($label)
                    ->placeholder($placeholder),
            ])
            ->query(function (Builder $query, array $data) use ($apply): Builder {
                $term = trim((string) ($data['value'] ?? ''));
                if ($term === '') {
                    return $query;
                }

                $apply($query, $term);

                return $query;
            })
            ->indicateUsing(function (array $data) use ($label): ?string {
                $term = trim((string) ($data['value'] ?? ''));

                return $term !== '' ? $label.': '.$term : null;
            });
    }

    private static function dateFilter(string $name, string $label, string $column, string $operator): Filter
    {
        return Filter::make($name)
            ->label($label)
            ->form([
                DatePicker::make('date')
                    ->label($label)
                    ->native(false),
            ])
            ->query(function (Builder $query, array $data) use ($column, $operator): Builder {
                return $query->when(
                    filled($data['date'] ?? null),
                    fn (Builder $inner): Builder => $inner->whereDate($column, $operator, $data['date']),
                );
            })
            ->indicateUsing(function (array $data) use ($label): ?string {
                return filled($data['date'] ?? null) ? $label.': '.$data['date'] : null;
            });
    }

    /**
     * @param  list<\BackedEnum>  $cases
     * @return array<string, string>
     */
    private static function enumOptions(array $cases): array
    {
        $options = [];
        foreach ($cases as $case) {
            $options[$case->value] = AdminTableBadgeColors::humanize($case);
        }

        return $options;
    }

    private static function paidCentsSql(): string
    {
        $orders = (new Order)->getTable();
        $payments = (new OrderPayment)->getTable();

        return '(select coalesce(sum('.$payments.'.amount_cents), 0) from '.$payments.' where '.$payments.'.order_id = '.$orders.'.id)';
    }

    private static function formatMoney(int $cents, string $currency): string
    {
        return $currency.' '.number_format((int) floor($cents / 100), 0, '.', ',');
    }
}

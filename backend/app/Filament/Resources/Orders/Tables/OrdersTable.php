<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['car', 'pickupLocation', 'dropoffLocation', 'payments']))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Date')
                    ->date('j M Y')
                    ->description(fn ($record): string => $record->created_at?->format('D H:i') ?? '-')
                    ->sortable(),
                TextColumn::make('customer_name')
                    ->label('Customer Information')
                    ->searchable()
                    ->description(fn ($record): string => trim(($record->customer_email ?? '').' '.($record->customer_phone ?? '')) ?: '-'),
                TextColumn::make('car.name')
                    ->label('Car')
                    ->searchable(),
                TextColumn::make('pickup_at')
                    ->label('Pickup')
                    ->date('d/m/Y')
                    ->description(fn ($record): string => $record->pickup_at?->format('D H:i') ?? '-')
                    ->sortable(),
                TextColumn::make('dropoff_at')
                    ->label('Drop Off')
                    ->date('d/m/Y')
                    ->description(fn ($record): string => $record->dropoff_at?->format('D H:i') ?? '-')
                    ->sortable(),
                TextColumn::make('rental_days')
                    ->label('Days')
                    ->state(fn ($record): int => max(1, (int) $record->pickup_at?->diffInDays($record->dropoff_at)))
                    ->alignCenter(),
                TextColumn::make('total_cents')
                    ->label('Total')
                    ->state(fn ($record): string => static::formatMoney((int) $record->total_cents, (string) $record->currency))
                    ->description(function ($record): string {
                        $paid = (int) $record->payments->sum('amount_cents');
                        $remaining = max(0, (int) $record->total_cents - $paid);

                        return static::formatMoney($remaining, (string) $record->currency);
                    }),
                TextColumn::make('order_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state): string => static::statusColor($state))
                    ->searchable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('search_tools')
                    ->label('Search Tools')
                    ->form([
                        TextInput::make('reference_or_id')
                            ->label('ID/Confirmation Number'),
                        TextInput::make('customer_name')
                            ->label('Customer Name'),
                        DatePicker::make('pickup_from')
                            ->label('Pickup From'),
                        DatePicker::make('pickup_to')
                            ->label('Pickup To'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $referenceOrId = trim((string) ($data['reference_or_id'] ?? ''));
                        $customerName = trim((string) ($data['customer_name'] ?? ''));

                        return $query
                            ->when($referenceOrId !== '', function (Builder $q) use ($referenceOrId): void {
                                $q->where(function (Builder $nested) use ($referenceOrId): void {
                                    $nested->where('reference', 'like', '%'.$referenceOrId.'%');
                                    if (ctype_digit($referenceOrId)) {
                                        $nested->orWhere('id', (int) $referenceOrId);
                                    }
                                });
                            })
                            ->when($customerName !== '', fn (Builder $q): Builder => $q->where('customer_name', 'like', '%'.$customerName.'%'))
                            ->when(
                                filled($data['pickup_from'] ?? null),
                                fn (Builder $q): Builder => $q->whereDate('pickup_at', '>=', $data['pickup_from'])
                            )
                            ->when(
                                filled($data['pickup_to'] ?? null),
                                fn (Builder $q): Builder => $q->whereDate('pickup_at', '<=', $data['pickup_to'])
                            );
                    }),
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function formatMoney(int $cents, string $currency): string
    {
        return $currency.' '.number_format((int) floor($cents / 100), 0, '.', ',');
    }

    private static function statusColor(mixed $state): string
    {
        $value = $state instanceof OrderStatus ? $state->value : (string) $state;

        return match ($value) {
            'confirmed' => 'success',
            'stand_by' => 'warning',
            'cancelled' => 'danger',
            default => 'gray',
        };
    }
}

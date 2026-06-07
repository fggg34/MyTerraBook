<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\CanPoll;
use Filament\Widgets\TableWidget;

class RecentOrdersWidget extends TableWidget
{
    use CanPoll;

    protected static ?int $sort = 2;

    protected static ?string $heading = 'Recent orders';

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.recent-orders-widget';

    protected function getPollingInterval(): ?string
    {
        return '60s';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->with(['car'])
                    ->latest()
            )
            ->defaultPaginationPageOption(8)
            ->paginated([8])
            ->columns([
                TextColumn::make('reference')
                    ->label('Reference')
                    ->searchable(),
                TextColumn::make('customer_name')
                    ->label('Customer'),
                TextColumn::make('car.name')
                    ->label('Car'),
                TextColumn::make('order_status')
                    ->label('Status')
                    ->badge()
                    ->color(function (mixed $state): string {
                        $value = $state instanceof OrderStatus ? $state->value : (string) $state;

                        return match ($value) {
                            'confirmed' => 'success',
                            'stand_by' => 'warning',
                            'cancelled' => 'danger',
                            default => 'gray',
                        };
                    }),
                TextColumn::make('total_cents')
                    ->label('Total')
                    ->state(fn (Order $record): string => '€'.number_format($record->total_cents / 100, 2)),
                TextColumn::make('created_at')
                    ->label('Date')
                    ->date('j M Y')
                    ->sortable(),
            ])
            ->recordUrl(fn (Order $record): string => OrderResource::getUrl('edit', ['record' => $record]));
    }
}

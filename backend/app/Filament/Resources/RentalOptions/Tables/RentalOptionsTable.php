<?php

namespace App\Filament\Resources\RentalOptions\Tables;

use App\Models\RentalOption;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RentalOptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Option Name')
                    ->searchable(),
                TextColumn::make('cost_cents')
                    ->label('Option Price')
                    ->money('ISK')
                    ->sortable(),
                IconColumn::make('is_daily_cost')
                    ->label('Daily Cost')
                    ->boolean(),
                TextColumn::make('max_cost_cap_cents')
                    ->label('Maximum Cost')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('taxRate.name')
                    ->label('Tax Rate')
                    ->searchable(),
                ImageColumn::make('image_path')
                    ->label('Option Image'),
                IconColumn::make('has_quantity')
                    ->label('Selectable Quantity')
                    ->boolean(),
                IconColumn::make('is_mandatory')
                    ->label('Always Selected')
                    ->boolean(),
                TextColumn::make('sort_order')
                ->label('Ordering')
                ->alignCenter()
                ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('move_up')
                    ->label('')
                    ->tooltip('Move up')
                    ->icon('heroicon-o-arrow-up')
                    ->action(function (RentalOption $record): void {
                        $previous = RentalOption::query()
                            ->where('sort_order', '<', $record->sort_order)
                            ->orderByDesc('sort_order')
                            ->first();

                        if (! $previous) {
                            return;
                        }

                        $currentOrder = (int) $record->sort_order;
                        $record->update(['sort_order' => (int) $previous->sort_order]);
                        $previous->update(['sort_order' => $currentOrder]);
                    }),
                Action::make('move_down')
                    ->label('')
                    ->tooltip('Move down')
                    ->icon('heroicon-o-arrow-down')
                    ->action(function (RentalOption $record): void {
                        $next = RentalOption::query()
                            ->where('sort_order', '>', $record->sort_order)
                            ->orderBy('sort_order')
                            ->first();

                        if (! $next) {
                            return;
                        }

                        $currentOrder = (int) $record->sort_order;
                        $record->update(['sort_order' => (int) $next->sort_order]);
                        $next->update(['sort_order' => $currentOrder]);
                    }),
                EditAction::make(),
            ])
            ->defaultSort('sort_order')
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

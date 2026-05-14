<?php

namespace App\Filament\Resources\DailyFares\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DailyFaresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('car.name')
                    ->label('Vehicle')
                    ->searchable(),
                TextColumn::make('priceType.name')
                    ->label('Price type')
                    ->searchable(),
                TextColumn::make('from_days')
                    ->label('Fare for days')
                    ->formatStateUsing(fn ($record): string => (string) $record->from_days.'–'.(string) $record->to_days)
                    ->sortable(),
                TextColumn::make('price_per_day_cents')
                    ->label('Price (ISK / day)')
                    ->formatStateUsing(fn ($state): string => number_format(((int) $state) / 100, 2, ',', '.').' ISK')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
}

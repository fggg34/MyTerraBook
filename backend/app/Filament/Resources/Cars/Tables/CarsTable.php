<?php

namespace App\Filament\Resources\Cars\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CarsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('category.name')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('transmission')
                    ->searchable(),
                TextColumn::make('fuel_type')
                    ->searchable(),
                TextColumn::make('seats')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('bags')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('availability_status')
                    ->searchable(),
                TextColumn::make('base_daily_price')
                    ->money()
                    ->sortable(),
                TextColumn::make('base_hourly_price')
                    ->money()
                    ->sortable(),
                TextColumn::make('min_rental_hours')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('min_rental_days')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('thumbnail_path')
                    ->searchable(),
                IconColumn::make('is_active')
                    ->boolean(),
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

<?php

namespace App\Filament\Resources\LocationFees\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LocationFeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['pickupLocation', 'dropoffLocation', 'taxRate']))
            ->view('filament.admin.location-fees')
            ->columns([
                TextColumn::make('pickupLocation.name')
                    ->searchable(),
                TextColumn::make('dropoffLocation.name')
                    ->searchable(),
                TextColumn::make('cost_cents')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('multiply_by_days')
                    ->boolean(),
                TextColumn::make('taxRate.name')
                    ->searchable(),
                IconColumn::make('apply_inverted')
                    ->boolean(),
                IconColumn::make('is_one_way_fee')
                    ->boolean(),
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
                SelectFilter::make('pickup_location_id')
                    ->label('Pickup')
                    ->relationship('pickupLocation', 'name'),
                SelectFilter::make('dropoff_location_id')
                    ->label('Drop-off')
                    ->relationship('dropoffLocation', 'name'),
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

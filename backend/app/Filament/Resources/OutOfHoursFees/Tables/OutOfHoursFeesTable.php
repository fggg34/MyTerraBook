<?php

namespace App\Filament\Resources\OutOfHoursFees\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OutOfHoursFeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('time_from')
                    ->time()
                    ->sortable(),
                TextColumn::make('time_to')
                    ->time()
                    ->sortable(),
                TextColumn::make('applies_to')
                    ->searchable(),
                TextColumn::make('pickup_cost_cents')
                    ->label('Pick Up Charge')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('dropoff_cost_cents')
                    ->label('Drop Off Charge')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_combined_charge_cents')
                    ->label('Max Charge')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('taxRate.name')
                    ->label('Tax Rate')
                    ->toggleable(),
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

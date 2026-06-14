<?php

namespace App\Filament\Resources\Locations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LocationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Location Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('address')
                    ->label('Location Address')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('cars_count')
                    ->label('Linked vehicles')
                    ->counts('cars')
                    ->sortable(),
                TextColumn::make('taxRate.name')
                    ->label('Override Tax Rate')
                    ->placeholder('-'),
                TextColumn::make('default_opening_time')
                    ->label('Opens')
                    ->time('H:i')
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('default_closing_time')
                    ->label('Closes')
                    ->time('H:i')
                    ->sortable()
                    ->placeholder('-'),
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

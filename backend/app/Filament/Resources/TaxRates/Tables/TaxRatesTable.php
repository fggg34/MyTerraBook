<?php

namespace App\Filament\Resources\TaxRates\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TaxRatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount(['priceTypes', 'rentalOptions', 'locationFees', 'outOfHoursFees']))
            ->paginated(false)
            ->view('filament.admin.tax-rates')
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable(),
                TextColumn::make('basis_points')
                    ->label('Rate')
                    ->formatStateUsing(static fn ($state): string => rtrim(rtrim(number_format(((int) $state) / 100, 2, '.', ''), '0'), '.').'%')
                    ->sortable(),
                TextColumn::make('used_by')
                    ->label('Used by')
                    ->state(function ($record): string {
                        $parts = [];
                        if ($record->priceTypes()->exists()) {
                            $parts[] = 'Price types';
                        }
                        if ($record->rentalOptions()->exists()) {
                            $parts[] = 'Add-ons';
                        }

                        return $parts !== [] ? implode(', ', $parts) : '-';
                    }),
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

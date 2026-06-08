<?php

namespace App\Filament\Resources\EmailTemplates\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EmailTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'account' => 'info',
                        'orders' => 'success',
                        'bookings' => 'warning',
                        'listings' => 'primary',
                        default => 'gray',
                    }),
                TextColumn::make('audience')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('subject')
                    ->limit(50)
                    ->toggleable(),
                IconColumn::make('is_enabled')
                    ->label('Enabled')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->options([
                        'account' => 'Account',
                        'orders' => 'Orders',
                        'bookings' => 'Bookings',
                        'listings' => 'Listings',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->defaultSort('sort_order')
            ->paginated(false);
    }
}

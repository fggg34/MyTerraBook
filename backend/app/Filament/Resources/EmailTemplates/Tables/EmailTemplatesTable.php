<?php

namespace App\Filament\Resources\EmailTemplates\Tables;

use App\Support\AdminTableBadgeColors;
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
                TextColumn::make('key')
                    ->label('Key')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')
                    ->badge()
                    ->sortable()
                    ->color(fn (): string => AdminTableBadgeColors::neutral())
                    ->formatStateUsing(fn (string $state): string => AdminTableBadgeColors::humanize($state)),
                TextColumn::make('audience')
                    ->badge()
                    ->color(fn (): string => AdminTableBadgeColors::neutral())
                    ->formatStateUsing(fn (string $state): string => AdminTableBadgeColors::humanize($state)),
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
                        'general' => 'General',
                        'custom' => 'Custom',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->defaultSort('sort_order')
            ->paginated(false);
    }
}

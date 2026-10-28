<?php

namespace App\Filament\Resources\PaymentMethods\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentMethodsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                IconColumn::make('is_enabled')
                    ->boolean(),
                IconColumn::make('auto_confirm_order')
                    ->boolean(),
                TextColumn::make('charge_or_discount')
                    ->label('Charge or discount')
                    ->formatStateUsing(fn (mixed $state): string => \App\Support\AdminTableBadgeColors::humanize($state))
                    ->searchable(),
                TextColumn::make('charge_discount_type')
                    ->label('Amount type')
                    ->formatStateUsing(fn (mixed $state): string => $state === 'fixed' ? 'Fixed amount' : \App\Support\AdminTableBadgeColors::humanize($state))
                    ->searchable(),
                TextColumn::make('charge_fixed_cents')
                    ->label('Fixed amount')
                    ->formatStateUsing(fn ($state): string => $state === null ? '—' : \App\Support\AdminMoney::fromCents((int) $state))
                    ->sortable(),
                TextColumn::make('charge_percent_bips')
                    ->label('Percent')
                    ->formatStateUsing(fn ($state): string => $state === null ? '—' : rtrim(rtrim(number_format(((int) $state) / 100, 2, '.', ''), '0'), '.').'%')
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->numeric()
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
            ])
            ->view('filament.admin.payment-methods');
    }
}

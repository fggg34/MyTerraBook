<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->searchable(),
                TextColumn::make('car.name')
                    ->searchable(),
                TextColumn::make('carUnit.id')
                    ->searchable(),
                TextColumn::make('priceType.name')
                    ->searchable(),
                TextColumn::make('pickupLocation.name')
                    ->searchable(),
                TextColumn::make('dropoffLocation.name')
                    ->searchable(),
                TextColumn::make('pickup_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('dropoff_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('order_status')
                    ->badge()
                    ->searchable(),
                TextColumn::make('rental_status')
                    ->badge()
                    ->searchable(),
                TextColumn::make('customer_name')
                    ->searchable(),
                TextColumn::make('customer_email')
                    ->searchable(),
                TextColumn::make('customer_phone')
                    ->searchable(),
                TextColumn::make('customer_country')
                    ->searchable(),
                TextColumn::make('base_rental_cents')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('extras_cents')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('fees_cents')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('discount_cents')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tax_cents')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_cents')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('currency')
                    ->searchable(),
                TextColumn::make('coupon.id')
                    ->searchable(),
                TextColumn::make('createdByAdmin.name')
                    ->searchable(),
                TextColumn::make('payment_lock_expires_at')
                    ->dateTime()
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

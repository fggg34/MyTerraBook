<?php

namespace App\Filament\Resources\RegisteredClients\Tables;

use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RegisteredClientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('phone')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('orders_count')
                    ->label('Vehicle bookings')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('guest_house_bookings_count')
                    ->label('Stay bookings')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Registered')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('email_verified_at')
                    ->label('Verified')
                    ->dateTime()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ViewAction::make(),
                self::makeDeleteAction(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function (User $record): void {
                            $record->tokens()->delete();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function makeDeleteAction(): DeleteAction
    {
        return DeleteAction::make()
            ->modalHeading('Delete registered client')
            ->modalDescription(fn (User $record): string => self::deleteConfirmationMessage($record))
            ->before(function (User $record): void {
                $record->tokens()->delete();
            });
    }

    public static function deleteConfirmationMessage(User $record): string
    {
        $orderCount = $record->orders()->count();
        $stayCount = $record->guestHouseBookings()->count();

        $parts = [
            'This permanently removes the client account for '.$record->email.'.',
        ];

        if ($orderCount > 0 || $stayCount > 0) {
            $bookingParts = [];
            if ($orderCount > 0) {
                $bookingParts[] = $orderCount.' vehicle booking'.($orderCount === 1 ? '' : 's');
            }
            if ($stayCount > 0) {
                $bookingParts[] = $stayCount.' stay booking'.($stayCount === 1 ? '' : 's');
            }
            $parts[] = 'Their '.implode(' and ', $bookingParts).' will remain in the system but will no longer be linked to this account.';
        }

        $parts[] = 'This action cannot be undone.';

        return implode(' ', $parts);
    }
}

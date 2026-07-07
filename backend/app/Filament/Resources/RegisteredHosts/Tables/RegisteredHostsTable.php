<?php

namespace App\Filament\Resources\RegisteredHosts\Tables;

use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class RegisteredHostsTable
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
                TextColumn::make('cars_count')
                    ->label('Vehicles')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('guest_houses_count')
                    ->label('Guesthouses')
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
                        ->before(function (Collection $records): void {
                            $records->each(fn (User $record) => self::revokeApiTokens($record));
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function makeDeleteAction(): DeleteAction
    {
        return DeleteAction::make()
            ->modalHeading('Delete registered host')
            ->modalDescription(fn (User $record): string => self::deleteConfirmationMessage($record))
            ->before(fn (User $record) => self::revokeApiTokens($record));
    }

    private static function revokeApiTokens(User $record): void
    {
        $record->tokens()->delete();
    }

    public static function deleteConfirmationMessage(User $record): string
    {
        $vehicleCount = $record->cars()->count();
        $guesthouseCount = $record->guestHouses()->count();

        $parts = [
            'This permanently removes the host account for '.$record->email.'.',
        ];

        if ($vehicleCount > 0 || $guesthouseCount > 0) {
            $listingParts = [];
            if ($vehicleCount > 0) {
                $listingParts[] = $vehicleCount.' vehicle'.($vehicleCount === 1 ? '' : 's');
            }
            if ($guesthouseCount > 0) {
                $listingParts[] = $guesthouseCount.' guesthouse'.($guesthouseCount === 1 ? '' : 's');
            }
            $parts[] = 'Their '.implode(' and ', $listingParts).' will remain on the platform but will no longer be linked to a host account.';
        }

        $parts[] = 'This action cannot be undone.';

        return implode(' ', $parts);
    }
}

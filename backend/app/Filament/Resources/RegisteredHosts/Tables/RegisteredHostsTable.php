<?php

namespace App\Filament\Resources\RegisteredHosts\Tables;

use App\Models\User;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class RegisteredHostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->searchPlaceholder('Search hosts by name or email')
            ->striped()
            ->emptyStateHeading('No hosts found')
            ->emptyStateDescription('Try another search or clear the filters to see more hosts.')
            ->columns([
                TextColumn::make('name')
                    ->label('Host / partner')
                    ->weight('semibold')
                    ->description(fn (User $record): string => $record->email)
                    ->wrap()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('phone')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('country_code')
                    ->label('Country')
                    ->formatStateUsing(fn (?string $state): string => $state
                        ? (string) config('destinations.'.$state, $state)
                        : '—')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('cars_count')
                    ->label('Vehicles')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('guest_houses_count')
                    ->label('Guesthouses')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('partner_api_enabled')
                    ->label('Partner API')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Enabled' : 'Disabled')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray')
                    ->sortable(),
                TextColumn::make('partner_commission_rate')
                    ->label('Commission')
                    ->state(fn (User $record): string => $record->partner_commission_rate !== null
                        ? rtrim(rtrim($record->partner_commission_rate, '0'), '.').'%'
                        : ((float) config('rapyd.commission_rate', 0.15) * 100).'% · Default')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Registered')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('email_verified_at')
                    ->label('Verified')
                    ->dateTime()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('country_code')->label('Operating country')
                    ->options(config('destinations', []))->searchable(),
                SelectFilter::make('partner_provider')->label('Connection type')
                    ->options(['greenlight' => 'Greenlight', 'caren' => 'Caren', 'foxie' => 'Foxie Camper', 'custom' => 'Custom API']),
                TernaryFilter::make('partner_api_enabled')->label('API enabled'),
            ])
            ->recordActions([
                EditAction::make()->label('Manage host')->icon('heroicon-o-cog-6-tooth'),
                ActionGroup::make([
                    ViewAction::make(),
                    self::makeDeleteAction(),
                ]),
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

<?php

namespace App\Filament\Resources\RegisteredClients;

use App\Enums\UserRole;
use App\Filament\Resources\RegisteredClients\Pages\ListRegisteredClients;
use App\Filament\Resources\RegisteredClients\Pages\ViewRegisteredClient;
use App\Filament\Resources\RegisteredClients\Tables\RegisteredClientsTable;
use App\Models\User;
use BackedEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class RegisteredClientResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $slug = 'registered-clients';

    protected static ?string $navigationLabel = 'Registered Clients';

    protected static ?string $modelLabel = 'registered client';

    protected static ?string $pluralModelLabel = 'Registered Clients';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|UnitEnum|null $navigationGroup = 'Moderation';

    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('role', UserRole::Customer)
            ->withCount(['orders', 'guestHouseBookings']);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = User::query()->where('role', UserRole::Customer)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Client details')->schema([
                TextEntry::make('name'),
                TextEntry::make('email')->copyable(),
                TextEntry::make('phone')->placeholder('—'),
                TextEntry::make('locale')
                    ->label('Language')
                    ->formatStateUsing(fn (mixed $state): string => strtoupper((string) $state)),
                TextEntry::make('currency')
                    ->placeholder('—'),
                TextEntry::make('email_verified_at')
                    ->label('Email verified')
                    ->dateTime()
                    ->placeholder('Not verified'),
                TextEntry::make('created_at')
                    ->label('Registered')
                    ->dateTime(),
            ])->columns(2),
            Section::make('Activity')->schema([
                TextEntry::make('orders_count')
                    ->label('Vehicle bookings')
                    ->formatStateUsing(fn (mixed $state): string => (string) ((int) $state)),
                TextEntry::make('guest_house_bookings_count')
                    ->label('Stay bookings')
                    ->formatStateUsing(fn (mixed $state): string => (string) ((int) $state)),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return RegisteredClientsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRegisteredClients::route('/'),
            'view' => ViewRegisteredClient::route('/{record}'),
        ];
    }
}

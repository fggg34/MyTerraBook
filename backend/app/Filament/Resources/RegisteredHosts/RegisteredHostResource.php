<?php

namespace App\Filament\Resources\RegisteredHosts;

use App\Enums\UserRole;
use App\Filament\Resources\RegisteredHosts\Pages\ListRegisteredHosts;
use App\Filament\Resources\RegisteredHosts\Pages\ViewRegisteredHost;
use App\Filament\Resources\RegisteredHosts\Tables\RegisteredHostsTable;
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

class RegisteredHostResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $slug = 'registered-hosts';

    protected static ?string $navigationLabel = 'Registered Hosts';

    protected static ?string $modelLabel = 'registered host';

    protected static ?string $pluralModelLabel = 'Registered Hosts';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Moderation';

    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('role', UserRole::Host)
            ->withCount(['cars', 'guestHouses']);
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
        $count = User::query()->where('role', UserRole::Host)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Host details')->schema([
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
            Section::make('Listings')->schema([
                TextEntry::make('cars_count')
                    ->label('Vehicles')
                    ->formatStateUsing(fn (mixed $state): string => (string) ((int) $state)),
                TextEntry::make('guest_houses_count')
                    ->label('Guesthouses')
                    ->formatStateUsing(fn (mixed $state): string => (string) ((int) $state)),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return RegisteredHostsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRegisteredHosts::route('/'),
            'view' => ViewRegisteredHost::route('/{record}'),
        ];
    }
}

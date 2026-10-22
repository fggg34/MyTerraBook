<?php

namespace App\Filament\Resources\RegisteredHosts;

use App\Enums\UserRole;
use App\Filament\Resources\RegisteredHosts\Pages\CreateRegisteredHost;
use App\Filament\Resources\RegisteredHosts\Pages\EditRegisteredHost;
use App\Filament\Resources\RegisteredHosts\Pages\ListRegisteredHosts;
use App\Filament\Resources\RegisteredHosts\Pages\ViewRegisteredHost;
use App\Filament\Resources\RegisteredHosts\Schemas\RegisteredHostForm;
use App\Filament\Resources\RegisteredHosts\Tables\RegisteredHostsTable;
use App\Models\User;
use BackedEnum;
use Filament\Infolists\Components\ImageEntry;
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
        return auth()->user()?->isFullAdmin() ?? false;
    }

    public static function canEdit($record): bool
    {
        return true;
    }

    public static function form(Schema $schema): Schema
    {
        return RegisteredHostForm::configure($schema);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = User::query()->where('role', UserRole::Host)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Host details')->icon('heroicon-o-building-office-2')->columnSpanFull()->schema([
                TextEntry::make('name'),
                ImageEntry::make('company_logo_path')
                    ->label('Partner logo')
                    ->disk('public')
                    ->height(64)
                    ->placeholder('No logo'),
                TextEntry::make('email')->copyable(),
                TextEntry::make('phone')->placeholder('—'),
                TextEntry::make('country_code')
                    ->label('Operating country')
                    ->formatStateUsing(fn (?string $state): string => $state
                        ? (string) config('destinations.'.$state, $state)
                        : '—')
                    ->placeholder('—'),
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
            Section::make('Listings')->icon('heroicon-o-squares-2x2')->columnSpanFull()->schema([
                TextEntry::make('cars_count')
                    ->label('Vehicles')
                    ->formatStateUsing(fn (mixed $state): string => (string) ((int) $state)),
                TextEntry::make('guest_houses_count')
                    ->label('Guesthouses')
                    ->formatStateUsing(fn (mixed $state): string => (string) ((int) $state)),
            ])->columns(2),
            Section::make('Partner API connection')->icon('heroicon-o-signal')->columnSpanFull()->schema([
                TextEntry::make('partner_api_enabled')
                    ->label('Connection status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Enabled' : 'Disabled')
                    ->helperText('Use Test connection in Manage host to verify saved credentials.')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                TextEntry::make('partner_provider')
                    ->label('Connection type')
                    ->placeholder('—')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'greenlight' => 'Greenlight car rental',
                        'foxie' => 'Foxie Camper',
                        null, '' => '-',
                        default => ucfirst($state).' API',
                    }),
                TextEntry::make('partner_api_base_url')
                    ->label('Partner API URL')
                    ->copyable()
                    ->placeholder('—')
                    ->columnSpanFull(),
            ])->columns(2),
            Section::make('Commission')->icon('heroicon-o-receipt-percent')->columnSpanFull()->schema([
                TextEntry::make('partner_commission_rate')
                    ->label('Partner commission')
                    ->state(fn (User $record): string => $record->partner_commission_rate !== null
                        ? rtrim(rtrim($record->partner_commission_rate, '0'), '.').'%'
                        : ((float) config('rapyd.commission_rate', 0.15) * 100).'% · Platform default'),
            ]),
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
            'create' => CreateRegisteredHost::route('/create'),
            'view' => ViewRegisteredHost::route('/{record}'),
            'edit' => EditRegisteredHost::route('/{record}/edit'),
        ];
    }
}

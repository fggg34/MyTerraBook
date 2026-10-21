<?php

namespace App\Filament\Resources\RegisteredHosts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RegisteredHostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Partner profile')
                ->icon('heroicon-o-building-office-2')
                ->columnSpanFull()
                ->description('These details identify this host or partner on MyTerra.')
                ->schema([
                    TextInput::make('name')->required()->maxLength(255),
                    TextInput::make('email')->email()->required()->maxLength(255)->unique(ignoreRecord: true),
                    TextInput::make('phone')->tel()->maxLength(32),
                    Select::make('country_code')
                        ->label('Operating country')
                        ->options(config('destinations', []))
                        ->searchable()
                        ->native(false),
                ])->columns(2),
            Section::make('Partner API connection')
                ->icon('heroicon-o-signal')
                ->columnSpanFull()
                ->description('Save your changes before testing the connection. The test uses saved credentials.')
                ->schema([
                    Toggle::make('partner_api_enabled')
                        ->label('Enable this partner connection')
                        ->helperText('Enabling a connection does not verify its credentials.')
                        ->live(),
                    Select::make('partner_provider')
                        ->label('Connection type')
                        ->options([
                            'greenlight' => 'Greenlight car rental',
                            'caren' => 'Caren',
                            'custom' => 'Custom partner API',
                        ])
                        ->placeholder('Select a connection type')
                        ->live()
                        ->required(fn ($get): bool => (bool) $get('partner_api_enabled')),
                    TextInput::make('partner_api_base_url')
                        ->label('Partner API URL')
                        ->url()
                        ->placeholder('https://partner.example/api')
                        ->required(fn ($get): bool => (bool) $get('partner_api_enabled'))
                        ->columnSpanFull(),
                    TextInput::make('partner_api_username')
                        ->label('Caren API username')
                        ->visible(fn ($get): bool => $get('partner_provider') === 'caren')
                        ->formatStateUsing(fn (): ?string => null)
                        ->dehydrated(fn (?string $state): bool => filled($state))
                        ->helperText('Stored encrypted. Leave blank to keep the saved username.'),
                    TextInput::make('partner_api_password')
                        ->label('Caren API password')
                        ->password()->revealable()->autocomplete('new-password')
                        ->visible(fn ($get): bool => $get('partner_provider') === 'caren')
                        ->formatStateUsing(fn (): ?string => null)
                        ->dehydrated(fn (?string $state): bool => filled($state))
                        ->helperText('Stored encrypted. Leave blank to keep the saved password.'),
                    TextInput::make('partner_rental_id')
                        ->label('Caren rental ID')
                        ->numeric()->minValue(1)
                        ->visible(fn ($get): bool => $get('partner_provider') === 'caren')
                        ->helperText('Leave blank to select automatically when the account has one rental.'),
                    TextInput::make('partner_api_key')
                        ->label('API key')
                        ->password()
                        ->revealable()
                        ->autocomplete('new-password')
                        ->helperText('Stored encrypted. Leave blank to keep the current key.')
                        ->formatStateUsing(fn (): ?string => null)
                        ->dehydrated(fn (?string $state): bool => filled($state))
                        ->columnSpanFull(),
                ])->columns(2),
            Section::make('Commission')
                ->icon('heroicon-o-receipt-percent')
                ->columnSpanFull()
                ->description('Platform default: '.((float) config('rapyd.commission_rate', 0.15) * 100).'%. Leave blank to inherit it, or enter a partner-specific percentage.')
                ->schema([
                    TextInput::make('partner_commission_rate')
                        ->label('Partner commission')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->step(0.01)
                        ->suffix('%')
                        ->helperText('The percentage charged online as MyTerra’s platform fee for this partner.'),
                ]),
        ]);
    }
}

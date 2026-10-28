<?php

namespace App\Filament\Resources\CustomFields\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CustomFieldForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('field_key')
                    ->label('Field name')
                    ->required(),
                TextInput::make('label')
                    ->label('Label shown to the customer')
                    ->required(),
                Select::make('type')
                    ->label('Type')
                    ->options([
                        'text' => 'Text',
                        'select' => 'Dropdown',
                    ])
                    ->required(),
                Toggle::make('is_required')
                    ->label('Required')
                    ->required(),
                Toggle::make('is_email')
                    ->label('Email address')
                    ->required(),
                TextInput::make('popup_link_url')
                    ->label('Popup link')
                    ->url(),
                Textarea::make('select_options')
                    ->label('Dropdown options')
                    ->columnSpanFull(),
                TextInput::make('sort_order')
                    ->label('Sort order')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->required(),
            ]);
    }
}

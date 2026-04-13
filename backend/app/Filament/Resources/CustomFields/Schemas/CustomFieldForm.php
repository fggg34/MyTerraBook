<?php

namespace App\Filament\Resources\CustomFields\Schemas;

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
                    ->required(),
                TextInput::make('label')
                    ->required(),
                TextInput::make('type')
                    ->required(),
                Toggle::make('is_required')
                    ->required(),
                Toggle::make('is_email')
                    ->required(),
                TextInput::make('popup_link_url')
                    ->url(),
                Textarea::make('select_options')
                    ->columnSpanFull(),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}

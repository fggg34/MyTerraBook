<?php

namespace App\Filament\Resources\Characteristics\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CharacteristicForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('icon_path'),
                TextInput::make('display_text'),
                Toggle::make('is_search_filter')
                    ->required(),
            ]);
    }
}

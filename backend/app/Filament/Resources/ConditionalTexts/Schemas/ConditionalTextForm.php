<?php

namespace App\Filament\Resources\ConditionalTexts\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ConditionalTextForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Textarea::make('content')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('content_plain')
                    ->columnSpanFull(),
                Textarea::make('conditions')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('templates')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('placement')
                    ->required()
                    ->default('body'),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}

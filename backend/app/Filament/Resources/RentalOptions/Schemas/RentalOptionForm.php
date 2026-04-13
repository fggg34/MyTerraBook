<?php

namespace App\Filament\Resources\RentalOptions\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class RentalOptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('cost_cents')
                    ->required()
                    ->numeric(),
                Toggle::make('is_daily_cost')
                    ->required(),
                TextInput::make('max_cost_cap_cents')
                    ->numeric(),
                Select::make('tax_rate_id')
                    ->relationship('taxRate', 'name'),
                FileUpload::make('image_path')
                    ->image(),
                Toggle::make('has_quantity')
                    ->required(),
                Toggle::make('is_mandatory')
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}

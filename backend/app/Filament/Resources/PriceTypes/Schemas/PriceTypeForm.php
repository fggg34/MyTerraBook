<?php

namespace App\Filament\Resources\PriceTypes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PriceTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Price Name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('attribute_label')
                    ->label('Price Attributes')
                    ->maxLength(255)
                    ->hint('Optional')
                    ->hintIcon('heroicon-o-question-mark-circle')
                    ->hintIconTooltip(
                        'The attribute is an additional information you can pass to the Type of Price for any number of days of rental. '
                        .'It is NOT a mandatory field and it can be left empty. An example of attribute could be "Km Included". '
                        .'From the page Fares Table, you will be able to specify the value for the attribute for any number of days of rental. '
                        .'For example, from 1 to 7 days: "100Km/day". From 8 to 14 days: "150Km/day". '
                        .'The attribute will be visible to the customer during the reservation process.'
                    ),

                Select::make('tax_rate_id')
                    ->label('Tax Rate')
                    ->relationship('taxRate', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('- None -'),
            ]);
    }
}

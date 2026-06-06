<?php

namespace App\Filament\Resources\SearchPromotions\Schemas;

use App\Models\SearchPromotion;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SearchPromotionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('kicker')->maxLength(120),
            TextInput::make('title')->required()->maxLength(255),
            Textarea::make('text')->rows(3)->columnSpanFull(),
            TextInput::make('cta_label')->label('Button label')->maxLength(120),
            TextInput::make('cta_href')->label('Button link')->maxLength(255),
            Select::make('layout')
                ->options([
                    SearchPromotion::LAYOUT_CARD => 'Card (1 column)',
                    SearchPromotion::LAYOUT_LANDSCAPE => 'Landscape (full row)',
                ])
                ->required()
                ->default(SearchPromotion::LAYOUT_CARD)
                ->live(),
            Select::make('context')
                ->options([
                    SearchPromotion::CONTEXT_ALL => 'All search pages',
                    'campervan' => 'Campervans',
                    'car' => 'Cars',
                    'guesthouse' => 'Guesthouses',
                ])
                ->required()
                ->default(SearchPromotion::CONTEXT_ALL),
            TextInput::make('insert_after')
                ->label('Insert after card #')
                ->helperText('Card promos only — 0 = before first card, 2 = after two cards.')
                ->numeric()
                ->minValue(0)
                ->default(2)
                ->visible(fn (callable $get) => $get('layout') === SearchPromotion::LAYOUT_CARD),
            FileUpload::make('image_path')
                ->label('Image')
                ->disk('public')
                ->directory('search-promotions')
                ->image()
                ->maxSize(8192)
                ->visible(fn (callable $get) => $get('layout') === SearchPromotion::LAYOUT_LANDSCAPE),
            TextInput::make('image_alt')->maxLength(255),
            TextInput::make('sort_order')->numeric()->default(0),
            Toggle::make('is_active')->default(true),
        ]);
    }
}

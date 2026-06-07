<?php

namespace App\Filament\Resources\SubCategories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class SubCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Details')
                    ->schema([
                        Select::make('main_category_id')
                            ->label('Main Category')
                            ->relationship('mainCategory', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')
                            ->label('Sub Category Name')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Body type or segment used as a storefront filter, e.g. Hatchback or Sedan.'),
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(6)
                            ->helperText(new HtmlString('<span style="border-top:1px solid #e5e7eb;display:block;padding-top:8px">Optional summary shown in admin and filter tooltips.</span>')),
                    ]),
                Section::make('Settings')
                    ->schema([
                        Toggle::make('is_search_filter')
                            ->label('Use as search filter')
                            ->default(true)
                            ->required(),
                    ]),
            ]);
    }
}

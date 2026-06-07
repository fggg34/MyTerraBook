<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Details')
                    ->schema([
                        TextInput::make('name')
                            ->label('Main Category Name')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Top-level vehicle type, e.g. Car or Campervan.'),
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(6)
                            ->helperText(new HtmlString('<span style="border-top:1px solid #e5e7eb;display:block;padding-top:8px">Shown in admin and used to group sub-categories on the storefront.</span>')),
                    ]),
            ]);
    }
}

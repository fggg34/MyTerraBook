<?php

namespace App\Filament\Resources\SitePages\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SitePageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Page header')
                ->schema([
                    TextInput::make('slug')
                        ->required()
                        ->maxLength(120)
                        ->unique(ignoreRecord: true)
                        ->disabled(fn (?string $operation): bool => $operation === 'edit'),
                    TextInput::make('title')->required()->maxLength(255),
                    TextInput::make('eyebrow')->maxLength(120),
                    Textarea::make('lead')->rows(3)->columnSpanFull(),
                    Toggle::make('is_published')->default(true),
                    DateTimePicker::make('published_at'),
                ])
                ->columns(2),

            Section::make('Page body')
                ->schema([
                    RichEditor::make('body')
                        ->columnSpanFull()
                        ->visible(fn ($record): bool => self::usesBody(null, $record?->slug)),
                ]),

            Section::make('FAQ items')
                ->visible(fn ($record): bool => $record?->slug === 'faq')
                ->schema([
                    TextInput::make('content.phone')->label('Phone'),
                    TextInput::make('content.email')->label('Email')->email(),
                    Repeater::make('content.items')
                        ->schema([
                            TextInput::make('num')->label('Number')->maxLength(8),
                            TextInput::make('question')->required()->columnSpanFull(),
                            Textarea::make('answer')->required()->rows(3)->columnSpanFull(),
                            Toggle::make('open')->label('Open by default'),
                        ])
                        ->columns(2)
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['question'] ?? 'FAQ item'),
                ]),

            Section::make('Contact details')
                ->visible(fn ($record): bool => $record?->slug === 'contact')
                ->schema([
                    TextInput::make('content.phone')->label('Phone'),
                    TextInput::make('content.email')->label('Email')->email(),
                    Textarea::make('content.address')->label('Address')->rows(3),
                    TextInput::make('content.hours')->label('Hours'),
                    Toggle::make('content.show_form')->label('Show contact form')->default(true),
                ]),
        ]);
    }

    private static function usesBody(?string $slug, ?string $recordSlug): bool
    {
        $key = $slug ?? $recordSlug;

        return ! in_array($key, ['faq', 'contact'], true);
    }
}

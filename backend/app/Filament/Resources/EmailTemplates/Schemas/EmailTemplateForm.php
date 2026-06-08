<?php

namespace App\Filament\Resources\EmailTemplates\Schemas;

use App\Models\EmailTemplate;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmailTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Settings')
                ->columns(2)
                ->schema([
                    TextInput::make('key')
                        ->label('Template key')
                        ->required()
                        ->maxLength(120)
                        ->regex('/^[a-z][a-z0-9_]*$/')
                        ->unique(ignoreRecord: true)
                        ->helperText('Lowercase identifier used in code, e.g. order_received. Cannot be changed later.')
                        ->visible(fn (?EmailTemplate $record): bool => $record === null),
                    Placeholder::make('key_display')
                        ->label('Template key')
                        ->content(fn (?EmailTemplate $record): string => $record?->key ?? '—')
                        ->visible(fn (?EmailTemplate $record): bool => $record !== null),
                    TextInput::make('name')
                        ->label('Name')
                        ->required()
                        ->maxLength(255),
                    Select::make('category')
                        ->options(self::categoryOptions())
                        ->required()
                        ->default('general')
                        ->visible(fn (?EmailTemplate $record): bool => $record === null),
                    Placeholder::make('category_display')
                        ->label('Category')
                        ->content(fn (?EmailTemplate $record): string => self::categoryOptions()[$record?->category ?? ''] ?? ($record?->category ?? '—'))
                        ->visible(fn (?EmailTemplate $record): bool => $record !== null),
                    Select::make('audience')
                        ->options(self::audienceOptions())
                        ->required()
                        ->default('customer')
                        ->visible(fn (?EmailTemplate $record): bool => $record === null),
                    Placeholder::make('audience_display')
                        ->label('Audience')
                        ->content(fn (?EmailTemplate $record): string => self::audienceOptions()[$record?->audience ?? ''] ?? ($record?->audience ?? '—'))
                        ->visible(fn (?EmailTemplate $record): bool => $record !== null),
                    Toggle::make('is_enabled')
                        ->label('Enabled')
                        ->default(true)
                        ->helperText('When off, this email is not sent.'),
                    TagsInput::make('available_variables')
                        ->label('Available variables')
                        ->placeholder('customer_name')
                        ->helperText('Use these in subject/body as {{variable_name}}. Global variables like {{brand_name}} and {{frontend_url}} are always available.')
                        ->columnSpanFull(),
                ]),
            Section::make('Content')
                ->columns(2)
                ->schema([
                    TextInput::make('subject')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    TextInput::make('preheader')
                        ->label('Preview text')
                        ->helperText('Short text shown in the inbox preview.')
                        ->maxLength(255)
                        ->columnSpanFull(),
                    TextInput::make('heading')
                        ->maxLength(255),
                    TextInput::make('greeting')
                        ->maxLength(255),
                    RichEditor::make('body_html')
                        ->label('Body')
                        ->columnSpanFull(),
                    TextInput::make('cta_label')
                        ->label('Button label')
                        ->maxLength(120),
                    TextInput::make('cta_url_template')
                        ->label('Button link')
                        ->helperText('Supports variables, e.g. {{frontend_url}}/account.')
                        ->maxLength(500),
                    Textarea::make('footer_note')
                        ->label('Footer note')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    /**
     * @return array<string, string>
     */
    private static function categoryOptions(): array
    {
        return [
            'account' => 'Account',
            'orders' => 'Orders',
            'bookings' => 'Bookings',
            'listings' => 'Listings',
            'general' => 'General',
            'custom' => 'Custom',
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function audienceOptions(): array
    {
        return [
            'customer' => 'Customer',
            'host' => 'Host',
            'staff' => 'Staff',
        ];
    }
}

<?php

namespace App\Filament\Resources\EmailTemplates\Schemas;

use App\Models\EmailTemplate;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class EmailTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Settings')
                ->columns(2)
                ->schema([
                    Placeholder::make('name_display')
                        ->label('Template')
                        ->content(fn (?EmailTemplate $record): string => $record?->name ?? '—'),
                    Toggle::make('is_enabled')
                        ->label('Enabled')
                        ->helperText('When off, this email is not sent.'),
                    Placeholder::make('available_variables_display')
                        ->label('Available variables')
                        ->columnSpanFull()
                        ->content(function (?EmailTemplate $record): HtmlString {
                            $vars = $record?->available_variables ?? [];

                            if (empty($vars)) {
                                return new HtmlString('<span style="color:#6b7280;">No variables for this template.</span>');
                            }

                            $chips = collect($vars)
                                ->map(fn (string $var): string => '<code style="background:#f1f5f9;border-radius:4px;padding:2px 6px;margin:0 4px 4px 0;display:inline-block;font-size:12px;">{{ '.$var.' }}</code>')
                                ->implode('');

                            return new HtmlString('<div>'.$chips.'</div>');
                        }),
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
}

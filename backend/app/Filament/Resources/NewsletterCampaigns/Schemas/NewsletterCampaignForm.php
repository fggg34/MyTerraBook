<?php

namespace App\Filament\Resources\NewsletterCampaigns\Schemas;

use App\Models\NewsletterCampaign;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class NewsletterCampaignForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('subject')
                ->required()
                ->maxLength(255)
                ->disabled(fn (?NewsletterCampaign $record): bool => $record !== null && ! $record->isDraft())
                ->columnSpanFull(),
            RichEditor::make('body')
                ->required()
                ->disabled(fn (?NewsletterCampaign $record): bool => $record !== null && ! $record->isDraft())
                ->columnSpanFull(),
            Placeholder::make('status_display')
                ->label('Status')
                ->content(fn (?NewsletterCampaign $record): string => $record?->status?->value
                    ? ucfirst($record->status->value)
                    : 'Draft')
                ->visible(fn (?NewsletterCampaign $record): bool => $record !== null),
            Placeholder::make('recipient_count_display')
                ->label('Recipients')
                ->content(fn (?NewsletterCampaign $record): string => (string) ($record?->recipient_count ?? 0))
                ->visible(fn (?NewsletterCampaign $record): bool => $record !== null && ! $record->isDraft()),
            Placeholder::make('sent_at_display')
                ->label('Sent at')
                ->content(fn (?NewsletterCampaign $record): string => $record?->sent_at?->format('Y-m-d H:i') ?? '—')
                ->visible(fn (?NewsletterCampaign $record): bool => $record !== null && ! $record->isDraft()),
        ]);
    }
}

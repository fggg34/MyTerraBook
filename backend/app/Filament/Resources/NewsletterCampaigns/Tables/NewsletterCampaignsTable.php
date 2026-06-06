<?php

namespace App\Filament\Resources\NewsletterCampaigns\Tables;

use App\Enums\NewsletterCampaignStatus;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NewsletterCampaignsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subject')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (NewsletterCampaignStatus $state): string => match ($state) {
                        NewsletterCampaignStatus::Draft => 'gray',
                        NewsletterCampaignStatus::Sent => 'success',
                    }),
                TextColumn::make('recipient_count')
                    ->label('Recipients')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('sent_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('sender.name')
                    ->label('Sent by')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}

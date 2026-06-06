<?php

namespace App\Filament\Resources\NewsletterSubscribers\Tables;

use App\Models\NewsletterSubscriber;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class NewsletterSubscribersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                TextColumn::make('source')
                    ->badge()
                    ->toggleable(),
                TextColumn::make('subscribed_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('unsubscribed_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Active subscribers'),
            ])
            ->recordActions([
                Action::make('deactivate')
                    ->label('Unsubscribe')
                    ->color('warning')
                    ->icon('heroicon-o-x-circle')
                    ->requiresConfirmation()
                    ->visible(fn (NewsletterSubscriber $record): bool => $record->is_active)
                    ->action(function (NewsletterSubscriber $record): void {
                        $record->update([
                            'is_active' => false,
                            'unsubscribed_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Subscriber deactivated')
                            ->success()
                            ->send();
                    }),
                Action::make('reactivate')
                    ->label('Reactivate')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->visible(fn (NewsletterSubscriber $record): bool => ! $record->is_active)
                    ->action(function (NewsletterSubscriber $record): void {
                        $record->update([
                            'is_active' => true,
                            'subscribed_at' => now(),
                            'unsubscribed_at' => null,
                        ]);

                        Notification::make()
                            ->title('Subscriber reactivated')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('subscribed_at', 'desc');
    }
}

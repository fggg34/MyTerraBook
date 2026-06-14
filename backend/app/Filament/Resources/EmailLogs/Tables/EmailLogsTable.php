<?php

namespace App\Filament\Resources\EmailLogs\Tables;

use App\Support\AdminTableBadgeColors;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EmailLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('template_key')
                    ->label('Template')
                    ->badge()
                    ->color(fn (): string => AdminTableBadgeColors::neutral())
                    ->searchable(),
                TextColumn::make('recipient')
                    ->searchable(),
                TextColumn::make('subject')
                    ->limit(50)
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => AdminTableBadgeColors::emailLogStatus($state))
                    ->formatStateUsing(fn (string $state): string => AdminTableBadgeColors::humanize($state)),
                TextColumn::make('error')
                    ->limit(60)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'queued' => 'Queued',
                        'sent' => 'Sent',
                        'failed' => 'Failed',
                    ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}

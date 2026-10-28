<?php

namespace App\Filament\Resources\BlogPosts\Tables;

use App\Enums\BlogPostStatus;
use App\Models\BlogPost;
use App\Support\AdminTableBadgeColors;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BlogPostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('featured_image')->disk('public')->square(),
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('kicker')->toggleable(),
                IconColumn::make('is_featured')->boolean()->label('Featured'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (mixed $state): string => AdminTableBadgeColors::blogPostStatus($state))
                    ->formatStateUsing(fn (mixed $state): string => AdminTableBadgeColors::humanize($state)),
                TextColumn::make('published_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        BlogPostStatus::Published->value => 'Published',
                        BlogPostStatus::Draft->value => 'Draft',
                    ]),
                SelectFilter::make('kicker')
                    ->options(fn (): array => BlogPost::query()
                        ->whereNotNull('kicker')
                        ->where('kicker', '!=', '')
                        ->distinct()
                        ->orderBy('kicker')
                        ->pluck('kicker', 'kicker')
                        ->mapWithKeys(fn ($kicker): array => [(string) $kicker => AdminTableBadgeColors::humanize((string) $kicker)])
                        ->all()),
                TernaryFilter::make('is_featured')->label('Featured'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->view('filament.admin.good-to-know')
            ->defaultSort('sort_order')
            ->modifyQueryUsing(fn ($query) => $query->orderByDesc('is_featured'));
    }
}

<?php

namespace App\Filament\Resources\BlogPosts\Tables;

use App\Support\AdminTableBadgeColors;
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
            ->recordActions([
                EditAction::make(),
            ])
            ->defaultSort('sort_order')
            ->modifyQueryUsing(fn ($query) => $query->orderByDesc('is_featured'));
    }
}

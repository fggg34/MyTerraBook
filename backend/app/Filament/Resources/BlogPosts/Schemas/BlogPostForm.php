<?php

namespace App\Filament\Resources\BlogPosts\Schemas;

use App\Enums\BlogPostStatus;
use App\Models\BlogPost;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BlogPostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(function ($state, callable $set, ?BlogPost $record): void {
                    if ($record?->exists) {
                        return;
                    }
                    $set('slug', BlogPost::uniqueSlugFromTitle((string) $state));
                }),
            TextInput::make('slug')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true),
            TextInput::make('kicker')->maxLength(80),
            Textarea::make('excerpt')->rows(3)->columnSpanFull(),
            RichEditor::make('body')->columnSpanFull(),
            FileUpload::make('featured_image')
                ->label('Featured image')
                ->disk('public')
                ->directory('blog')
                ->image()
                ->maxSize(8192),
            TextInput::make('image_alt')->maxLength(255),
            TextInput::make('read_time')->label('Read time label')->placeholder('12 min read'),
            Toggle::make('is_featured')->label('Featured on homepage'),
            Toggle::make('aurora')->label('Aurora card style'),
            Section::make('SEO')
                ->schema([
                    TextInput::make('meta_title')
                        ->label('Meta title')
                        ->maxLength(255)
                        ->helperText('Leave empty to use the post title.'),
                    Textarea::make('meta_description')
                        ->label('Meta description')
                        ->rows(3)
                        ->helperText('Leave empty to use the excerpt.'),
                    FileUpload::make('og_image')
                        ->label('Share image (OG)')
                        ->disk('public')
                        ->directory('blog/og')
                        ->image()
                        ->maxSize(8192)
                        ->helperText('Leave empty to use the featured image.'),
                ])
                ->columns(1)
                ->collapsible(),
            Select::make('status')
                ->options(collect(BlogPostStatus::cases())->mapWithKeys(fn ($c) => [$c->value => ucfirst($c->value)]))
                ->required()
                ->default(BlogPostStatus::Published->value),
            DateTimePicker::make('published_at'),
            TextInput::make('sort_order')->numeric()->default(0),
        ]);
    }
}

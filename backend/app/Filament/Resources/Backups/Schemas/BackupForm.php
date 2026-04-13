<?php

namespace App\Filament\Resources\Backups\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BackupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('disk')
                    ->required()
                    ->default('local'),
                TextInput::make('path')
                    ->required(),
                TextInput::make('filename')
                    ->required(),
                TextInput::make('size_bytes')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('backup_type')
                    ->required(),
            ]);
    }
}

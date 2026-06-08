<?php

namespace App\Filament\Resources\EmailLogs;

use App\Filament\Resources\EmailLogs\Pages\ListEmailLogs;
use App\Filament\Resources\EmailLogs\Tables\EmailLogsTable;
use App\Models\EmailLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class EmailLogResource extends Resource
{
    protected static ?string $model = EmailLog::class;

    protected static ?string $navigationLabel = 'Email log';

    protected static ?string $modelLabel = 'email log entry';

    protected static ?string $pluralModelLabel = 'Email log';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInbox;

    protected static string|UnitEnum|null $navigationGroup = 'Email';

    protected static ?int $navigationSort = 4;

    public static function table(Table $table): Table
    {
        return EmailLogsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmailLogs::route('/'),
        ];
    }
}

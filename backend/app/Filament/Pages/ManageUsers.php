<?php

namespace App\Filament\Pages;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\Admin\ActiveLoginService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Password;

class ManageUsers extends Page implements HasTable
{
    use InteractsWithTable;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = 'Users';

    protected static ?string $slug = 'users';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    public static function canAccess(): bool
    {
        return auth()->user()?->isFullAdmin() ?? false;
    }

    public function mount(): void
    {
        $this->tableFilters ??= [];
        $this->tableFilters['role'] ??= ['value' => null];
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            EmbeddedTable::make(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(User::query())
            ->searchPlaceholder('Search by name or email')
            ->striped()
            ->emptyStateHeading('No users found')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->copyable()
                    ->sortable(),
                TextColumn::make('role')
                    ->badge()
                    ->formatStateUsing(fn (UserRole|string $state): string => \App\Support\AdminTableBadgeColors::humanize($state instanceof UserRole ? $state->value : (string) $state))
                    ->sortable(),
                TextColumn::make('phone')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Registered')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->options(collect(UserRole::cases())->mapWithKeys(
                        fn (UserRole $role): array => [$role->value => \App\Support\AdminTableBadgeColors::humanize($role->value)],
                    )->all()),
            ])
            ->view('filament.admin.users')
            ->recordActions([
                Action::make('changePassword')
                    ->label('Change password')
                    ->icon(Heroicon::OutlinedKey)
                    ->modalHeading('Change password')
                    ->modalDescription(fn (User $record): string => (int) $record->id === (int) auth()->id()
                        ? 'This replaces your password and signs you out of the admin panel.'
                        : 'This replaces the password for '.$record->email.' and signs them out of every device.')
                    ->schema([
                        TextInput::make('password')
                            ->label('New password')
                            ->password()
                            ->revealable()
                            ->required()
                            ->rule(Password::min(8))
                            ->same('password_confirmation')
                            ->autocomplete('new-password'),
                        TextInput::make('password_confirmation')
                            ->label('Confirm new password')
                            ->password()
                            ->revealable()
                            ->required()
                            ->autocomplete('new-password'),
                    ])
                    ->action(function (User $record, array $data, ActiveLoginService $logins): void {
                        $record->update(['password' => $data['password']]);
                        $logins->logout($record);

                        Notification::make()
                            ->title('Password updated')
                            ->body($record->email.' must sign in with the new password.')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('name');
    }
}

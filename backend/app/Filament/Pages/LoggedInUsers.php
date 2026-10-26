<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Services\Admin\ActiveLoginService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class LoggedInUsers extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = 'Logged-in users';

    protected static ?string $slug = 'logged-in-users';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected string $view = 'filament.pages.logged-in-users';

    public static function canAccess(): bool
    {
        return auth()->user()?->isFullAdmin() ?? false;
    }

    public function logout(int $userId, ActiveLoginService $logins): void
    {
        $user = User::query()->find($userId);
        if ($user === null) {
            return;
        }

        $logins->logout($user);

        Notification::make()
            ->title($user->name.' has been logged out')
            ->success()
            ->send();
    }
}

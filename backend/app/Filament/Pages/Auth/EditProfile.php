<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Password;

class EditProfile extends BaseEditProfile
{
    protected static ?string $title = 'Profile settings';

    public static function getLabel(): string
    {
        return 'Profile settings';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Account')
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                        $this->getCurrentPasswordFormComponent(),
                    ]),
                Section::make('Change password')
                    ->description('Leave both fields empty to keep your current password.')
                    ->schema([
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ]),
            ]);
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('New password')
            ->validationAttribute('new password')
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->rule(Password::default())
            ->showAllValidationMessages()
            ->autocomplete('new-password')
            ->dehydrated(fn ($state): bool => filled($state))
            ->live(debounce: 500)
            ->same('passwordConfirmation')
            ->required(fn (Get $get): bool => filled($get('passwordConfirmation')));
    }

    protected function getPasswordConfirmationFormComponent(): Component
    {
        return TextInput::make('passwordConfirmation')
            ->label('Confirm new password')
            ->validationAttribute('password confirmation')
            ->password()
            ->autocomplete('new-password')
            ->revealable(filament()->arePasswordsRevealable())
            ->dehydrated(false)
            ->live(debounce: 500)
            ->required(fn (Get $get): bool => filled($get('password')));
    }

    protected function getCurrentPasswordFormComponent(): Component
    {
        return TextInput::make('currentPassword')
            ->label('Current password')
            ->validationAttribute('current password')
            ->helperText('Required when changing your email or password.')
            ->password()
            ->autocomplete('current-password')
            ->currentPassword(guard: Filament::getAuthGuard())
            ->revealable(filament()->arePasswordsRevealable())
            ->dehydrated(false)
            ->live(debounce: 500)
            ->required(fn (Get $get): bool => $this->requiresCurrentPassword($get));
    }

    protected function requiresCurrentPassword(Get $get): bool
    {
        return filled($get('password'))
            || filled($get('passwordConfirmation'))
            || $get('email') !== $this->getUser()->getAttributeValue('email');
    }
}

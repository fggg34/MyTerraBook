<?php

namespace App\Support;

use Filament\Forms\Components\TextInput;

final class AdminInputs
{
    public static function isk(string $name, string $label): TextInput
    {
        return TextInput::make($name)
            ->label($label)
            ->numeric()
            ->minValue(0)
            ->prefix('ISK')
            ->afterStateHydrated(function (TextInput $component, mixed $state): void {
                if ($state === null || $state === '') {
                    return;
                }

                $component->state((int) intdiv((int) $state, 100));
            })
            ->dehydrateStateUsing(fn (mixed $state): ?int => $state === null || $state === '' ? null : (int) round(((float) $state) * 100));
    }

    public static function percentFromBips(string $name, string $label): TextInput
    {
        return TextInput::make($name)
            ->label($label)
            ->numeric()
            ->minValue(0)
            ->suffix('%')
            ->afterStateHydrated(function (TextInput $component, mixed $state): void {
                if ($state === null || $state === '') {
                    return;
                }

                $component->state(((int) $state) / 100);
            })
            ->dehydrateStateUsing(fn (mixed $state): ?int => $state === null || $state === '' ? null : (int) round(((float) $state) * 100));
    }
}

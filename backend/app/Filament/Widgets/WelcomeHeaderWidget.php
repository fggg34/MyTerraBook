<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class WelcomeHeaderWidget extends Widget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.welcome-header';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $user = auth()->user();
        $hour = (int) now()->format('G');

        $greeting = match (true) {
            $hour < 12 => 'Good morning',
            $hour < 17 => 'Good afternoon',
            default => 'Good evening',
        };

        return [
            'greeting' => $greeting,
            'name' => $user?->name ?? 'Admin',
            'date' => now()->format('l, F j, Y'),
        ];
    }
}

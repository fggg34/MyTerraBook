<?php

namespace App\Filament\Resources\EmailTemplates\Pages;

use App\Filament\Resources\EmailTemplates\EmailTemplateResource;
use App\Models\EmailTemplate;
use Database\Seeders\EmailTemplateSeeder;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListEmailTemplates extends ListRecords
{
    protected static string $resource = EmailTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->installDefaultsAction(),
            CreateAction::make()
                ->label('New email template'),
        ];
    }

    protected function getEmptyStateActions(): array
    {
        return [
            $this->installDefaultsAction(),
            CreateAction::make()
                ->label('New email template'),
        ];
    }

    private function installDefaultsAction(): Action
    {
        return Action::make('installDefaults')
            ->label('Install default templates')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('gray')
            ->requiresConfirmation()
            ->modalHeading('Install default email templates')
            ->modalDescription(function (): string {
                $total = EmailTemplateSeeder::defaultTemplateCount();

                return "This adds any of the {$total} built-in templates that are not in the database yet. Existing templates are not changed.";
            })
            ->action(function (): void {
                $created = EmailTemplateSeeder::seedMissing();
                $this->dispatch('$refresh');

                if ($created === 0) {
                    Notification::make()
                        ->title('All default templates are already installed')
                        ->success()
                        ->send();

                    return;
                }

                Notification::make()
                    ->title("Installed {$created} template(s)")
                    ->body('You can edit any template from this list.')
                    ->success()
                    ->send();
            });
    }

    public function toggleTemplate(int $id): void
    {
        $template = EmailTemplate::query()->findOrFail($id);
        $template->update(['is_enabled' => ! $template->is_enabled]);
    }

    public function getFooter(): ?View
    {
        $groups = EmailTemplate::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->groupBy(fn (EmailTemplate $template): string => (string) ($template->category ?: 'general'));

        return view('filament.email.templates-board', [
            'groups' => $groups,
        ]);
    }
}

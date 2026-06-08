<?php

namespace App\Filament\Resources\EmailTemplates\Pages;

use App\Filament\Resources\EmailTemplates\EmailTemplateResource;
use App\Mail\TemplatedMail;
use App\Services\Email\EmailSettingsService;
use App\Services\Email\EmailTestSampleData;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Mail;

class EditEmailTemplate extends EditRecord
{
    protected static string $resource = EmailTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sendTest')
                ->label('Send test email')
                ->icon('heroicon-o-paper-airplane')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Send a test email')
                ->modalDescription(function (): string {
                    $recipient = app(EmailSettingsService::class)->getTestRecipient()
                        ?: auth()->user()?->email
                        ?: 'your saved test address';

                    return 'A sample of this email will be sent to '.$recipient.'.';
                })
                ->action(function (): void {
                    $this->save(shouldRedirect: false);

                    $settings = app(EmailSettingsService::class);
                    $recipient = $settings->getTestRecipient() ?: auth()->user()?->email;

                    if (! $recipient) {
                        Notification::make()->title('Set a test email address on the Send test page first.')->danger()->send();

                        return;
                    }

                    try {
                        Mail::to($recipient)->send(new TemplatedMail(
                            templateKey: $this->record->key,
                            data: EmailTestSampleData::forTemplate($this->record),
                        ));

                        Notification::make()
                            ->title('Test email sent')
                            ->body("Sent to {$recipient}.")
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Could not send test email')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}

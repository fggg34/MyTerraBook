<?php

namespace App\Filament\Pages;

use App\Mail\TemplatedMail;
use App\Models\EmailTemplate;
use App\Services\Email\EmailSettingsService;
use App\Services\Email\EmailTestSampleData;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Mail;
use UnitEnum;

class EmailTesting extends Page
{
    protected string $view = 'filament.pages.email-testing';

    protected static ?string $title = 'Send test email';

    protected static ?string $navigationLabel = 'Send test';

    protected static ?string $slug = 'email-testing';

    protected static string|UnitEnum|null $navigationGroup = 'Email';

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaperAirplane;

    public string $testEmail = '';

    public ?string $templateKey = null;

    public function mount(EmailSettingsService $service): void
    {
        $saved = $service->getTestRecipient();
        $this->testEmail = $saved !== ''
            ? $saved
            : (string) (auth()->user()?->email ?? '');

        $this->templateKey = EmailTemplate::query()
            ->orderBy('sort_order')
            ->value('key');
    }

    /**
     * @return array<string, string>
     */
    public function getTemplateOptionsProperty(): array
    {
        return EmailTemplate::query()
            ->orderBy('sort_order')
            ->pluck('name', 'key')
            ->all();
    }

    public function saveTestEmail(EmailSettingsService $service): void
    {
        $email = trim($this->testEmail);

        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            Notification::make()
                ->title('Enter a valid email address')
                ->danger()
                ->send();

            return;
        }

        $service->saveTestRecipient($email);

        Notification::make()
            ->title('Test recipient saved')
            ->success()
            ->send();
    }

    public function sendTest(EmailSettingsService $service): void
    {
        $email = trim($this->testEmail);

        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            Notification::make()
                ->title('Enter a valid email address')
                ->danger()
                ->send();

            return;
        }

        if ($this->templateKey === null || $this->templateKey === '') {
            Notification::make()
                ->title('Select an email template')
                ->danger()
                ->send();

            return;
        }

        $template = EmailTemplate::findByKey($this->templateKey);

        if ($template === null) {
            Notification::make()
                ->title('Template not found')
                ->danger()
                ->send();

            return;
        }

        $service->saveTestRecipient($email);

        try {
            Mail::to($email)->send(new TemplatedMail(
                templateKey: $template->key,
                data: EmailTestSampleData::forTemplate($template),
            ));

            Notification::make()
                ->title('Test email sent')
                ->body("“{$template->name}” was sent to {$email}.")
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Could not send test email')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}

<?php

namespace App\Filament\Resources\NewsletterCampaigns\Pages;

use App\Filament\Resources\NewsletterCampaigns\NewsletterCampaignResource;
use App\Services\NewsletterService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditNewsletterCampaign extends EditRecord
{
    protected static string $resource = NewsletterCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sendCampaign')
                ->label('Send to subscribers')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->visible(fn (): bool => $this->record->isDraft())
                ->requiresConfirmation()
                ->modalHeading('Send marketing email')
                ->modalDescription(function (NewsletterService $newsletterService): string {
                    $count = $newsletterService->activeSubscriberCount();

                    return $count > 0
                        ? "This will email {$count} active subscriber(s). This cannot be undone."
                        : 'There are no active subscribers to email.';
                })
                ->disabled(fn (NewsletterService $newsletterService): bool => $newsletterService->activeSubscriberCount() === 0)
                ->action(function (NewsletterService $newsletterService): void {
                    $this->form->validate();

                    $this->record->update($this->form->getState());

                    $sentCount = $newsletterService->sendCampaign(
                        campaign: $this->record->fresh(),
                        sentByUserId: auth()->id(),
                    );

                    Notification::make()
                        ->title('Campaign sent')
                        ->body("Delivered to {$sentCount} subscriber(s).")
                        ->success()
                        ->send();

                    $this->refreshFormData(['status', 'recipient_count', 'sent_at']);
                }),
            DeleteAction::make()
                ->visible(fn (): bool => $this->record->isDraft()),
        ];
    }
}

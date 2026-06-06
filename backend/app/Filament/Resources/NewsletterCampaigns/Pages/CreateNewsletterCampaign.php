<?php

namespace App\Filament\Resources\NewsletterCampaigns\Pages;

use App\Enums\NewsletterCampaignStatus;
use App\Filament\Resources\NewsletterCampaigns\NewsletterCampaignResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNewsletterCampaign extends CreateRecord
{
    protected static string $resource = NewsletterCampaignResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = NewsletterCampaignStatus::Draft->value;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}

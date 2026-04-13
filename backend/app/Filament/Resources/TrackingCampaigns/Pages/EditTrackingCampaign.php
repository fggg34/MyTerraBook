<?php

namespace App\Filament\Resources\TrackingCampaigns\Pages;

use App\Filament\Resources\TrackingCampaigns\TrackingCampaignResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTrackingCampaign extends EditRecord
{
    protected static string $resource = TrackingCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

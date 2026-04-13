<?php

namespace App\Filament\Resources\TrackingCampaigns\Pages;

use App\Filament\Resources\TrackingCampaigns\TrackingCampaignResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTrackingCampaigns extends ListRecords
{
    protected static string $resource = TrackingCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\TrackingCampaigns\Pages;

use App\Filament\Concerns\EmbedsCatalogTable;
use App\Filament\Resources\TrackingCampaigns\TrackingCampaignResource;
use App\Models\TrackingCampaign;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTrackingCampaigns extends ListRecords
{
    use EmbedsCatalogTable;

    protected static string $resource = TrackingCampaignResource::class;

    public function toggleCampaign(int $id): void
    {
        $campaign = TrackingCampaign::query()->find($id);
        if ($campaign) {
            $campaign->update(['is_active' => ! $campaign->is_active]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

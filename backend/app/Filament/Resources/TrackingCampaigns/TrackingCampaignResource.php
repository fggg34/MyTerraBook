<?php

namespace App\Filament\Resources\TrackingCampaigns;

use App\Filament\Resources\TrackingCampaigns\Pages\CreateTrackingCampaign;
use App\Filament\Resources\TrackingCampaigns\Pages\EditTrackingCampaign;
use App\Filament\Resources\TrackingCampaigns\Pages\ListTrackingCampaigns;
use App\Filament\Resources\TrackingCampaigns\Schemas\TrackingCampaignForm;
use App\Filament\Resources\TrackingCampaigns\Tables\TrackingCampaignsTable;
use App\Models\TrackingCampaign;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TrackingCampaignResource extends Resource
{
    protected static ?string $model = TrackingCampaign::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Reports & tracking';

    public static function form(Schema $schema): Schema
    {
        return TrackingCampaignForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TrackingCampaignsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTrackingCampaigns::route('/'),
            'create' => CreateTrackingCampaign::route('/create'),
            'edit' => EditTrackingCampaign::route('/{record}/edit'),
        ];
    }
}

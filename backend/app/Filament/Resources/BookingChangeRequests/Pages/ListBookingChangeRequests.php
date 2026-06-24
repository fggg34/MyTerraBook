<?php

namespace App\Filament\Resources\BookingChangeRequests\Pages;

use App\Filament\Resources\BookingChangeRequests\BookingChangeRequestResource;
use Filament\Resources\Pages\ListRecords;

class ListBookingChangeRequests extends ListRecords
{
    protected static string $resource = BookingChangeRequestResource::class;
}

<?php

namespace App\Enums;

enum GuestHouseAvailabilityBlockReason: string
{
    case Maintenance = 'maintenance';
    case OwnerUse = 'owner_use';
    case ExternalBooking = 'external_booking';
    case Other = 'other';
}

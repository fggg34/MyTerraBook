<?php

namespace App\Enums;

enum GuestHouseAvailabilityBlockSource: string
{
    case Manual = 'manual';
    case Ical = 'ical';
}

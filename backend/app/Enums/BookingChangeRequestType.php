<?php

namespace App\Enums;

enum BookingChangeRequestType: string
{
    case Modification = 'modification';
    case Cancellation = 'cancellation';
}

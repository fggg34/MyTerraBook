<?php

namespace App\Enums;

enum GuestHouseCancellationPolicy: string
{
    case Flexible = 'flexible';
    case Moderate = 'moderate';
    case Strict = 'strict';
}

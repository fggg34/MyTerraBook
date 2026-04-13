<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case StandBy = 'stand_by';
    case Cancelled = 'cancelled';
}

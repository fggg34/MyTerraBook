<?php

namespace App\Enums;

enum RentalStatus: string
{
    case Upcoming = 'upcoming';
    case Started = 'started';
    case Terminated = 'terminated';
    case NoShow = 'no_show';
}

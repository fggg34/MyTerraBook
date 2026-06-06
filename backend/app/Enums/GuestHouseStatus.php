<?php

namespace App\Enums;

enum GuestHouseStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Draft = 'draft';
    case PendingReview = 'pending_review';
    case Rejected = 'rejected';
}

<?php

namespace App\Enums;

enum NewsletterCampaignStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
}

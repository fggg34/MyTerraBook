<?php

namespace App\Models;

use App\Enums\NewsletterCampaignStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsletterCampaign extends Model
{
    protected $fillable = [
        'subject',
        'body',
        'status',
        'recipient_count',
        'sent_at',
        'sent_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => NewsletterCampaignStatus::class,
            'recipient_count' => 'integer',
            'sent_at' => 'datetime',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function isDraft(): bool
    {
        return $this->status === NewsletterCampaignStatus::Draft;
    }
}

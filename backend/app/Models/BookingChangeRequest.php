<?php

namespace App\Models;

use App\Enums\BookingChangeRequestStatus;
use App\Enums\BookingChangeRequestType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BookingChangeRequest extends Model
{
    protected $fillable = [
        'bookable_type',
        'bookable_id',
        'user_id',
        'type',
        'status',
        'customer_message',
        'requested_changes',
        'admin_response',
        'reviewed_by_id',
        'reviewed_at',
        'applied_at',
        'pricing_before',
        'pricing_after',
        'price_delta_cents',
    ];

    protected function casts(): array
    {
        return [
            'type' => BookingChangeRequestType::class,
            'status' => BookingChangeRequestStatus::class,
            'requested_changes' => 'array',
            'pricing_before' => 'array',
            'pricing_after' => 'array',
            'reviewed_at' => 'datetime',
            'applied_at' => 'datetime',
            'price_delta_cents' => 'integer',
        ];
    }

    public function bookable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_id');
    }
}

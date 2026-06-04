<?php

namespace App\Models;

use App\Enums\GuestHouseAvailabilityBlockReason;
use App\Enums\GuestHouseAvailabilityBlockSource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestHouseAvailabilityBlock extends Model
{
    protected $fillable = [
        'guest_house_id',
        'blocked_from',
        'blocked_to',
        'reason',
        'note',
        'source',
        'ical_uid',
    ];

    protected function casts(): array
    {
        return [
            'blocked_from' => 'date',
            'blocked_to' => 'date',
            'reason' => GuestHouseAvailabilityBlockReason::class,
            'source' => GuestHouseAvailabilityBlockSource::class,
        ];
    }

    public function guestHouse(): BelongsTo
    {
        return $this->belongsTo(GuestHouse::class);
    }
}

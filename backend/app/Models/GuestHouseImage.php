<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestHouseImage extends Model
{
    protected $fillable = [
        'guest_house_id',
        'path',
        'caption',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function guestHouse(): BelongsTo
    {
        return $this->belongsTo(GuestHouse::class);
    }
}

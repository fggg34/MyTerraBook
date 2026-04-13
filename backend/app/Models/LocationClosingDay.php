<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocationClosingDay extends Model
{
    protected $fillable = [
        'location_id',
        'specific_date',
        'recurring_weekday',
    ];

    protected function casts(): array
    {
        return [
            'specific_date' => 'date',
            'recurring_weekday' => 'integer',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}

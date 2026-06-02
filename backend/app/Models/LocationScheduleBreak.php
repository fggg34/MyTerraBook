<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocationScheduleBreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_schedule_id',
        'break_start',
        'break_end',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(LocationSchedule::class, 'location_schedule_id');
    }
}

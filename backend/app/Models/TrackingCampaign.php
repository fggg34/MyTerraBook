<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrackingCampaign extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function events(): HasMany
    {
        return $this->hasMany(TrackingEvent::class);
    }
}

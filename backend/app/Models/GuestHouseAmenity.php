<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GuestHouseAmenity extends Model
{
    protected $fillable = [
        'name',
        'icon',
        'group',
    ];

    public function guestHouses(): BelongsToMany
    {
        return $this->belongsToMany(GuestHouse::class, 'guest_house_amenity', 'amenity_id', 'guest_house_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'address',
        'city',
        'country',
        'phone',
        'latitude',
        'longitude',
        'allows_pickup',
        'allows_dropoff',
        'opening_hours',
        'closed_days',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'allows_pickup' => 'boolean',
        'allows_dropoff' => 'boolean',
        'opening_hours' => 'array',
        'closed_days' => 'array',
        'is_active' => 'boolean',
    ];

    public function pickupBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'pickup_location_id');
    }

    public function dropoffBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'dropoff_location_id');
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }
}

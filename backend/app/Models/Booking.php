<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'user_id',
        'car_id',
        'pickup_location_id',
        'dropoff_location_id',
        'pickup_at',
        'dropoff_at',
        'status',
        'customer_name',
        'customer_email',
        'customer_phone',
        'rental_subtotal',
        'extras_subtotal',
        'discount_amount',
        'tax_amount',
        'total',
        'coupon_id',
        'currency',
        'pricing_snapshot',
        'notes',
        'created_by_admin_id',
    ];

    protected $casts = [
        'pickup_at' => 'datetime',
        'dropoff_at' => 'datetime',
        'status' => BookingStatus::class,
        'rental_subtotal' => 'decimal:2',
        'extras_subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'pricing_snapshot' => 'array',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creatorAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_admin_id');
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    public function pickupLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'pickup_location_id');
    }

    public function dropoffLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'dropoff_location_id');
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function extras(): HasMany
    {
        return $this->hasMany(BookingExtra::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}

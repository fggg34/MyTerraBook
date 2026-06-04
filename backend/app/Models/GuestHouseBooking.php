<?php

namespace App\Models;

use App\Enums\GuestHouseBookingStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class GuestHouseBooking extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'guest_house_id',
        'user_id',
        'booking_reference',
        'status',
        'guest_name',
        'guest_email',
        'guest_phone',
        'check_in',
        'check_out',
        'nights',
        'guests_count',
        'base_total',
        'cleaning_fee',
        'security_deposit',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'coupon_code',
        'coupon_id',
        'special_requests',
        'cancellation_reason',
        'cancelled_at',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => GuestHouseBookingStatus::class,
            'check_in' => 'date',
            'check_out' => 'date',
            'nights' => 'integer',
            'guests_count' => 'integer',
            'base_total' => 'integer',
            'cleaning_fee' => 'integer',
            'security_deposit' => 'integer',
            'discount_amount' => 'integer',
            'tax_amount' => 'integer',
            'total_amount' => 'integer',
            'cancelled_at' => 'datetime',
            'confirmed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (GuestHouseBooking $booking): void {
            if (empty($booking->booking_reference)) {
                $booking->booking_reference = static::generateReference();
            }

            if ($booking->check_in && $booking->check_out && ! $booking->nights) {
                $booking->nights = max(
                    1,
                    (int) Carbon::parse($booking->check_in)->diffInDays(Carbon::parse($booking->check_out)),
                );
            }
        });
    }

    public static function generateReference(): string
    {
        do {
            $ref = 'GH-'.now()->format('Ymd').'-'.strtoupper(Str::random(5));
        } while (static::withTrashed()->where('booking_reference', $ref)->exists());

        return $ref;
    }

    public function guestHouse(): BelongsTo
    {
        return $this->belongsTo(GuestHouse::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(GuestHouseBookingPayment::class);
    }

    public function review(): HasMany
    {
        return $this->hasMany(GuestHouseReview::class, 'guest_house_booking_id');
    }
}

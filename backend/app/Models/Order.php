<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\RentalStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = [
        'reference',
        'user_id',
        'car_id',
        'car_unit_id',
        'price_type_id',
        'pickup_location_id',
        'dropoff_location_id',
        'pickup_at',
        'dropoff_at',
        'order_status',
        'rental_status',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_country',
        'base_rental_cents',
        'extras_cents',
        'fees_cents',
        'discount_cents',
        'tax_cents',
        'total_cents',
        'currency',
        'coupon_id',
        'pricing_snapshot',
        'custom_field_values',
        'notes',
        'admin_internal_note',
        'created_by_admin_id',
        'payment_lock_expires_at',
    ];

    protected function casts(): array
    {
        return [
            'pickup_at' => 'datetime',
            'dropoff_at' => 'datetime',
            'order_status' => OrderStatus::class,
            'rental_status' => RentalStatus::class,
            'base_rental_cents' => 'integer',
            'extras_cents' => 'integer',
            'fees_cents' => 'integer',
            'discount_cents' => 'integer',
            'tax_cents' => 'integer',
            'total_cents' => 'integer',
            'pricing_snapshot' => 'array',
            'custom_field_values' => 'array',
            'payment_lock_expires_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order): void {
            if ($order->reference === null || $order->reference === '') {
                $order->reference = static::generateUniqueReference();
            }
        });
    }

    public static function generateUniqueReference(): string
    {
        do {
            $reference = 'ORD-'.strtoupper(Str::random(10));
        } while (static::query()->where('reference', $reference)->exists());

        return $reference;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    public function carUnit(): BelongsTo
    {
        return $this->belongsTo(CarUnit::class, 'car_unit_id');
    }

    public function priceType(): BelongsTo
    {
        return $this->belongsTo(PriceType::class);
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

    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_admin_id');
    }

    public function lineItems(): HasMany
    {
        return $this->hasMany(OrderLineItem::class);
    }

    public function rentalOptions(): HasMany
    {
        return $this->hasMany(OrderRentalOption::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(OrderPayment::class);
    }

    public function couponRedemptions(): HasMany
    {
        return $this->hasMany(CouponRedemption::class);
    }

    public function transitionOrderStatus(OrderStatus $to): void
    {
        $from = $this->order_status;
        if ($from === $to) {
            return;
        }

        if (! static::isAllowedOrderTransition($from, $to)) {
            throw new \InvalidArgumentException(
                "Invalid order status transition from {$from->value} to {$to->value}."
            );
        }

        $this->order_status = $to;

        if ($to === OrderStatus::Confirmed) {
            $this->rental_status = RentalStatus::Upcoming;
        }

        if ($to === OrderStatus::Cancelled) {
            $this->rental_status = null;
        }

        if ($to === OrderStatus::Pending || $to === OrderStatus::StandBy) {
            $this->rental_status = null;
        }

        $this->save();
    }

    public static function isAllowedOrderTransition(OrderStatus $from, OrderStatus $to): bool
    {
        return match ($from) {
            OrderStatus::Pending => in_array($to, [OrderStatus::Confirmed, OrderStatus::StandBy, OrderStatus::Cancelled], true),
            OrderStatus::StandBy => in_array($to, [OrderStatus::Confirmed, OrderStatus::Cancelled, OrderStatus::Pending], true),
            OrderStatus::Confirmed => $to === OrderStatus::Cancelled,
            OrderStatus::Cancelled => false,
        };
    }

    public function transitionRentalStatus(RentalStatus $to, ?string $internalNoteAppend = null): void
    {
        if ($this->order_status !== OrderStatus::Confirmed) {
            throw new \InvalidArgumentException('Rental status can only change when order is confirmed.');
        }

        $from = $this->rental_status;
        if ($from === $to) {
            return;
        }

        if (! static::isAllowedRentalTransition($from, $to)) {
            throw new \InvalidArgumentException(
                'Invalid rental status transition from '.($from?->value ?? 'null')." to {$to->value}."
            );
        }

        $this->rental_status = $to;
        if ($internalNoteAppend !== null && $internalNoteAppend !== '') {
            $this->admin_internal_note = trim((string) $this->admin_internal_note."\n".$internalNoteAppend);
        }
        $this->save();
    }

    public static function isAllowedRentalTransition(?RentalStatus $from, RentalStatus $to): bool
    {
        if ($from === null) {
            return $to === RentalStatus::Upcoming;
        }

        return match ($from) {
            RentalStatus::Upcoming => in_array($to, [RentalStatus::Started, RentalStatus::NoShow], true),
            RentalStatus::Started => $to === RentalStatus::Terminated,
            RentalStatus::Terminated, RentalStatus::NoShow => false,
        };
    }
}

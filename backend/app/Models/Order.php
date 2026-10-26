<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\OrderStatus;
use App\Enums\RentalStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Support\BookingConfirmationUrl;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'confirmation_token',
        'confirmation_url',
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
        'customer_date_of_birth',
        'external_provider',
        'external_reference',
        'partner_checkout_token',
        'partner_sync_state',
        'partner_sync_error',
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
        'rental_terms_html',
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
            'customer_date_of_birth' => 'date',
            'payment_lock_expires_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (Order $order): void {
            if ($order->getOriginal('external_provider') === 'caren') {
                if ($order->isDirty(['car_id', 'pickup_location_id', 'dropoff_location_id', 'pickup_at', 'dropoff_at',
                    'customer_name', 'customer_email', 'customer_phone', 'customer_date_of_birth', 'price_type_id',
                    'base_rental_cents', 'extras_cents', 'fees_cents', 'discount_cents', 'tax_cents', 'total_cents', 'currency', 'external_provider'])) {
                    throw new \InvalidArgumentException('Caren reservation amendments must be synchronized with the provider; direct changes are disabled.');
                }
                if ($order->isDirty('order_status') && (($order->order_status === OrderStatus::Confirmed && $order->partner_sync_state !== 'confirmed')
                    || ($order->order_status === OrderStatus::Cancelled && $order->partner_sync_state !== 'cancelled'))) {
                    throw new \InvalidArgumentException('Caren must acknowledge this status change first.');
                }

                return;
            }
            if ($order->external_provider === 'foxie'
                && $order->isDirty('order_status')
                && $order->order_status === OrderStatus::Cancelled
                && $order->getOriginal('partner_sync_state') !== 'cancelled'
                && filled($order->external_reference)) {
                app(\App\Services\Partners\FoxieBookingService::class)->cancel($order);
                $order->partner_sync_state = 'cancelled';
            }
        });
        static::deleting(function (Order $order): void {
            if ($order->external_provider === 'caren') {
                throw new \InvalidArgumentException('Keep Caren orders for reservation and payment reconciliation; cancel them instead.');
            }
        });
        static::creating(function (Order $order): void {
            if ($order->reference === null || $order->reference === '') {
                $order->reference = static::generateUniqueReference();
            }
            BookingConfirmationUrl::assignToModel($order);
            $order->snapshotRentalTerms();
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

    public function snapshotRentalTerms(): void
    {
        if (filled($this->rental_terms_html) || ! $this->car_id) {
            return;
        }

        $hostId = Car::query()->whereKey($this->car_id)->value('user_id');
        if (! $hostId) {
            return;
        }

        $this->rental_terms_html = User::query()->whereKey($hostId)->value('rental_terms_html');
    }

    public function resolvedRentalTermsHtml(): ?string
    {
        if (filled($this->rental_terms_html)) {
            return $this->rental_terms_html;
        }

        $this->loadMissing('car.host');

        return $this->car?->host?->rental_terms_html;
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

    public function changeRequests(): MorphMany
    {
        return $this->morphMany(BookingChangeRequest::class, 'bookable')->latest();
    }

    public function couponRedemptions(): HasMany
    {
        return $this->hasMany(CouponRedemption::class);
    }

    public function transitionOrderStatus(OrderStatus $to): void
    {
        if ($this->external_provider === 'caren' && $to === OrderStatus::Cancelled) {
            app(\App\Services\Partners\CarenBookingService::class)->cancel($this);

            return;
        }
        if ($this->external_provider === 'caren' && $to === OrderStatus::Confirmed
            && $this->partner_sync_state !== 'confirmed') {
            throw new \InvalidArgumentException('Caren must confirm this reservation after verified payment first.');
        }
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

        if ($to === OrderStatus::Cancelled) {
            app(\App\Services\Partners\GreenlightBookingService::class)->cancelIfNeeded($this);
        }
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

<?php

namespace App\Models;

use App\Enums\GuestHouseCancellationPolicy;
use App\Enums\GuestHouseStatus;
use App\Enums\GuestHouseType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class GuestHouse extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'short_description',
        'type',
        'status',
        'address',
        'city',
        'country',
        'latitude',
        'longitude',
        'max_guests',
        'bedrooms',
        'bathrooms',
        'beds',
        'min_nights',
        'max_nights',
        'base_price_per_night',
        'cleaning_fee',
        'security_deposit',
        'check_in_time',
        'check_out_time',
        'cancellation_policy',
        'thumbnail',
        'tax_rate_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => GuestHouseType::class,
            'status' => GuestHouseStatus::class,
            'cancellation_policy' => GuestHouseCancellationPolicy::class,
            'max_guests' => 'integer',
            'bedrooms' => 'integer',
            'bathrooms' => 'integer',
            'beds' => 'integer',
            'min_nights' => 'integer',
            'max_nights' => 'integer',
            'base_price_per_night' => 'integer',
            'cleaning_fee' => 'integer',
            'security_deposit' => 'integer',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (GuestHouse $house): void {
            if (empty($house->slug)) {
                $house->slug = static::uniqueSlugFromName($house->name);
            }
        });
    }

    public static function uniqueSlugFromName(string $name, ?int $exceptId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 0;

        while (true) {
            $query = static::withTrashed()->where('slug', $slug);
            if ($exceptId !== null) {
                $query->whereKeyNot($exceptId);
            }
            if (! $query->exists()) {
                return $slug;
            }
            $i++;
            $slug = $base.'-'.$i;
        }
    }

    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(GuestHouseImage::class)->orderBy('sort_order');
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(GuestHouseAmenity::class, 'guest_house_amenity', 'guest_house_id', 'amenity_id');
    }

    public function seasonalPrices(): HasMany
    {
        return $this->hasMany(GuestHouseSeasonalPrice::class);
    }

    public function availabilityBlocks(): HasMany
    {
        return $this->hasMany(GuestHouseAvailabilityBlock::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(GuestHouseBooking::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(GuestHouseReview::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', GuestHouseStatus::Active);
    }

    /** Filter to houses available for the given stay (use with active scope). */
    public function scopeAvailable(Builder $query, string $checkIn, string $checkOut): Builder
    {
        $service = app(\App\Services\GuestHouseAvailabilityService::class);
        $ids = (clone $query)->pluck('id')->filter(
            fn (int $id) => $service->isAvailable(
                GuestHouse::query()->find($id),
                $checkIn,
                $checkOut,
            ),
        );

        return $query->whereIn('id', $ids->all() ?: [-1]);
    }

    /**
     * @return array{nights: int, nightly_breakdown: list<array{date: string, price_cents: int}>}
     */
    public function getPriceForPeriod(string $checkIn, string $checkOut): array
    {
        $start = Carbon::parse($checkIn)->startOfDay();
        $end = Carbon::parse($checkOut)->startOfDay();
        $nights = max(1, (int) $start->diffInDays($end));

        $seasonal = $this->seasonalPrices()->get();
        $breakdown = [];
        $cursor = $start->copy();

        for ($i = 0; $i < $nights; $i++) {
            $dateStr = $cursor->toDateString();
            $price = $this->base_price_per_night;

            foreach ($seasonal as $rule) {
                if ($dateStr >= $rule->date_from->toDateString()
                    && $dateStr <= $rule->date_to->toDateString()) {
                    $price = $rule->price_per_night;
                    break;
                }
            }

            $breakdown[] = ['date' => $dateStr, 'price_cents' => $price];
            $cursor->addDay();
        }

        return ['nights' => $nights, 'nightly_breakdown' => $breakdown];
    }
}

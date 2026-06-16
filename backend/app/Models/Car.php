<?php

namespace App\Models;

use App\Enums\DriveType;
use App\Enums\ListingApprovalStatus;
use App\Models\Concerns\HasListingReviews;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Car extends Model
{
    use HasFactory;
    use HasListingReviews;

    protected $fillable = [
        'user_id',
        'sub_category_id',
        'name',
        'meta_title',
        'meta_description',
        'og_image',
        'slug',
        'description',
        'transmission',
        'fuel_type',
        'drive_type',
        'seats',
        'sleeps',
        'bags',
        'year',
        'main_image_path',
        'details_image_paths',
        'units_available',
        'ical_import_url',
        'pickup_time_from',
        'pickup_time_to',
        'dropoff_time_from',
        'dropoff_time_to',
        'is_active',
        'listing_status',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'units_available' => 'integer',
            'seats' => 'integer',
            'sleeps' => 'integer',
            'bags' => 'integer',
            'year' => 'integer',
            'drive_type' => DriveType::class,
            'details_image_paths' => 'array',
            'is_active' => 'boolean',
            'listing_status' => ListingApprovalStatus::class,
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(function (Builder $builder): void {
                $builder
                    ->whereNull('user_id')
                    ->orWhere('listing_status', ListingApprovalStatus::Approved);
            });
    }

    public function isOwnedByHost(): bool
    {
        return $this->user_id !== null;
    }

    public function isPubliclyVisible(): bool
    {
        return $this->is_active
            && (! $this->isOwnedByHost() || $this->listing_status === ListingApprovalStatus::Approved);
    }

    protected static function booted(): void
    {
        static::creating(function (Car $car): void {
            if ($car->slug === null || $car->slug === '') {
                $car->slug = static::uniqueSlugFromName($car->name);
            }
        });

        static::updating(function (Car $car): void {
            if ($car->slug === null || $car->slug === '') {
                $car->slug = static::uniqueSlugFromName($car->name, $car->getKey());
            }
        });
    }

    public static function uniqueSlugFromName(string $name, ?int $exceptId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 0;

        while (true) {
            $query = static::query()->where('slug', $slug);
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

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'car_location')
            ->withPivot(['allows_pickup', 'allows_dropoff'])
            ->withTimestamps();
    }

    public function characteristics(): BelongsToMany
    {
        return $this->belongsToMany(Characteristic::class, 'car_characteristic')
            ->withTimestamps();
    }

    public function rentalOptions(): BelongsToMany
    {
        return $this->belongsToMany(RentalOption::class, 'car_rental_option')
            ->withTimestamps();
    }

    public function rentalConditions(): BelongsToMany
    {
        return $this->belongsToMany(RentalCondition::class, 'car_rental_condition')
            ->withTimestamps()
            ->orderBy('rental_conditions.sort_order');
    }

    public function distinctiveFeatureDefinitions(): HasMany
    {
        return $this->hasMany(CarDistinctiveFeatureDefinition::class);
    }

    public function carUnits(): HasMany
    {
        return $this->hasMany(CarUnit::class);
    }

    public function dailyFares(): HasMany
    {
        return $this->hasMany(DailyFare::class);
    }

    public function hourlyFares(): HasMany
    {
        return $this->hasMany(HourlyFare::class);
    }

    public function extraHourFares(): HasMany
    {
        return $this->hasMany(ExtraHourFare::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function availabilityBlocks(): HasMany
    {
        return $this->hasMany(AvailabilityBlock::class);
    }

    public function locationFees(): HasMany
    {
        return $this->hasMany(LocationFee::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Car extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'transmission',
        'fuel_type',
        'main_image_path',
        'units_available',
        'ical_import_url',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'units_available' => 'integer',
            'is_active' => 'boolean',
        ];
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

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
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
}

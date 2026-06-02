<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'address',
        'latitude',
        'longitude',
        'tax_rate_id',
        'description',
        'default_opening_time',
        'default_closing_time',
        'suggested_preselected_time',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Location $location): void {
            if ($location->slug === null || $location->slug === '') {
                $location->slug = static::uniqueSlugFromName($location->name);
            }
        });
    }

    public static function uniqueSlugFromName(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 0;
        while (static::query()->where('slug', $slug)->exists()) {
            $i++;
            $slug = $base.'-'.$i;
        }

        return $slug;
    }

    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(LocationSchedule::class);
    }

    public function closingDays(): HasMany
    {
        return $this->hasMany(LocationClosingDay::class);
    }

    public function cars(): BelongsToMany
    {
        return $this->belongsToMany(Car::class, 'car_location')
            ->withPivot(['allows_pickup', 'allows_dropoff'])
            ->withTimestamps();
    }

    public function dropoffCombinations(): BelongsToMany
    {
        return $this->belongsToMany(
            Location::class,
            'location_dropoff_combinations',
            'pickup_location_id',
            'dropoff_location_id',
        );
    }
}

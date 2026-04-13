<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PriceType extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'attribute_label',
        'attribute_value_per_day',
        'tax_rate_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (PriceType $priceType): void {
            if ($priceType->slug === null || $priceType->slug === '') {
                $priceType->slug = static::uniqueSlugFromName($priceType->name);
            }
        });

        static::updating(function (PriceType $priceType): void {
            if ($priceType->slug === null || $priceType->slug === '') {
                $priceType->slug = static::uniqueSlugFromName($priceType->name, $priceType->getKey());
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

    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class);
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
}

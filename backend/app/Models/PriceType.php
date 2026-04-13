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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class RentalOption extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'cost_cents',
        'is_daily_cost',
        'max_cost_cap_cents',
        'tax_rate_id',
        'image_path',
        'has_quantity',
        'is_mandatory',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'cost_cents' => 'integer',
            'is_daily_cost' => 'boolean',
            'max_cost_cap_cents' => 'integer',
            'has_quantity' => 'boolean',
            'is_mandatory' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (RentalOption $option): void {
            if ($option->slug === null || $option->slug === '') {
                $option->slug = static::uniqueSlugFromName($option->name);
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

    public function cars(): BelongsToMany
    {
        return $this->belongsToMany(Car::class, 'car_rental_option')
            ->withTimestamps();
    }
}

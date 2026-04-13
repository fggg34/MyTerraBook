<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Characteristic extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'icon_path',
        'display_text',
        'is_search_filter',
    ];

    protected function casts(): array
    {
        return [
            'is_search_filter' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Characteristic $characteristic): void {
            if ($characteristic->slug === null || $characteristic->slug === '') {
                $characteristic->slug = static::uniqueSlugFromName($characteristic->name);
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

    public function cars(): BelongsToMany
    {
        return $this->belongsToMany(Car::class, 'car_characteristic')
            ->withTimestamps();
    }
}

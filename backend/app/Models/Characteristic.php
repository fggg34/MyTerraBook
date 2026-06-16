<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Characteristic extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'icon_path',
        'display_text',
        'group',
        'sort_order',
        'is_search_filter',
    ];

    /**
     * Canonical groups used to organise the characteristic catalogue in the
     * admin panel and host editor. Free-text values are still allowed.
     *
     * @var list<string>
     */
    public const GROUPS = [
        'Comfort & Convenience',
        'Safety & Driver Assistance',
        'Technology & Connectivity',
        'Winter & Iceland',
        'Capacity & Practicality',
        'Drivetrain & Performance',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
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

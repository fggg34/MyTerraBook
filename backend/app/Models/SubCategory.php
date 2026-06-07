<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SubCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'main_category_id',
        'name',
        'slug',
        'description',
        'sort_order',
        'is_active',
        'is_search_filter',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_search_filter' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (SubCategory $category): void {
            if ($category->slug === null || $category->slug === '') {
                $category->slug = static::uniqueSlugFromName($category->name);
            }
        });

        static::updating(function (SubCategory $category): void {
            if ($category->slug === null || $category->slug === '') {
                $category->slug = static::uniqueSlugFromName($category->name, $category->getKey());
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

    public function mainCategory(): BelongsTo
    {
        return $this->belongsTo(MainCategory::class);
    }

    public function cars(): HasMany
    {
        return $this->hasMany(Car::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class MainCategory extends Model
{
    use HasFactory, SoftDeletes;

    /** @var list<string> */
    public const CORE_SLUGS = ['car', 'campervan'];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (MainCategory $category): void {
            if ($category->slug === null || $category->slug === '') {
                $category->slug = static::uniqueSlugFromName($category->name);
            }
        });

        static::updating(function (MainCategory $category): void {
            if ($category->slug === null || $category->slug === '') {
                $category->slug = static::uniqueSlugFromName($category->name, $category->getKey());
            }
        });
    }

    public function isCore(): bool
    {
        return in_array($this->slug, self::CORE_SLUGS, true);
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

    /**
     * @param  array<string, mixed>  $values
     */
    public static function ensureBySlug(string $slug, array $values = []): static
    {
        $existing = static::withTrashed()->where('slug', $slug)->first();

        if ($existing !== null) {
            if ($existing->trashed()) {
                $existing->restore();
            }

            $existing->update(array_merge($values, ['is_active' => true]));

            return $existing;
        }

        return static::create(array_merge($values, [
            'slug' => $slug,
            'is_active' => true,
        ]));
    }

    public function subCategories(): HasMany
    {
        return $this->hasMany(SubCategory::class);
    }
}

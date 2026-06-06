<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SearchPromotion extends Model
{
    public const LAYOUT_CARD = 'card';

    public const LAYOUT_LANDSCAPE = 'landscape';

    public const CONTEXT_ALL = 'all';

    protected $fillable = [
        'kicker',
        'title',
        'text',
        'cta_label',
        'cta_href',
        'layout',
        'context',
        'insert_after',
        'image_path',
        'image_alt',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'insert_after' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForContext(Builder $query, string $context): Builder
    {
        return $query->where(function (Builder $builder) use ($context) {
            $builder->where('context', self::CONTEXT_ALL)
                ->orWhere('context', $context);
        });
    }
}

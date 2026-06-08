<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = [
        'key',
        'name',
        'category',
        'audience',
        'is_enabled',
        'subject',
        'preheader',
        'heading',
        'greeting',
        'body_html',
        'cta_label',
        'cta_url_template',
        'footer_note',
        'available_variables',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'available_variables' => 'array',
            'sort_order' => 'integer',
        ];
    }

    public static function findByKey(string $key): ?self
    {
        return static::query()->where('key', $key)->first();
    }
}

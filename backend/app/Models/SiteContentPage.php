<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteContentPage extends Model
{
    protected $fillable = [
        'page_key',
        'label',
        'content',
        'is_published',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'is_published' => 'boolean',
        ];
    }
}

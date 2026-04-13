<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConditionalText extends Model
{
    protected $fillable = [
        'name',
        'content',
        'content_plain',
        'conditions',
        'templates',
        'placement',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'conditions' => 'array',
            'templates' => 'array',
            'is_active' => 'boolean',
        ];
    }
}

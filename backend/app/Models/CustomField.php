<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomField extends Model
{
    protected $fillable = [
        'field_key',
        'label',
        'type',
        'is_required',
        'is_email',
        'popup_link_url',
        'select_options',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'is_email' => 'boolean',
            'select_options' => 'array',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}

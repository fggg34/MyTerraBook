<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RapydWebhookLog extends Model
{
    protected $table = 'rapyd_webhooks_log';

    protected $fillable = [
        'event_type',
        'checkout_id',
        'payment_id',
        'payload',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'processed_at' => 'datetime',
        ];
    }
}

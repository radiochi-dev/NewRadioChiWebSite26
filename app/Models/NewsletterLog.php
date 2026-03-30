<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsletterLog extends Model
{
    protected $fillable = [
        'campaign_id',
        'subscriber_id',
        'status',
        'error_message',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'processed_at' => 'datetime',
        ];
    }
}

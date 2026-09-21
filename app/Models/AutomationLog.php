<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AutomationLog extends Model
{
    protected $fillable = [
        'integration',
        'event',
        'status',
        'direction',
        'reference_type',
        'reference_id',
        'request_payload',
        'response_payload',
        'error_message',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'request_payload' => 'array',
            'response_payload' => 'array',
            'processed_at' => 'datetime',
        ];
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}

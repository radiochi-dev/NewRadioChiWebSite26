<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RedirectRule extends Model
{
    protected $fillable = [
        'source_path',
        'destination_url',
        'http_status',
        'locale',
        'is_active',
        'notes',
        'hit_count',
    ];

    protected function casts(): array
    {
        return [
            'http_status' => 'integer',
            'is_active' => 'boolean',
            'hit_count' => 'integer',
        ];
    }
}

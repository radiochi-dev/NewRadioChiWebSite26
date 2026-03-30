<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'excerpt',
        'body',
        'event_starts_at',
        'event_ends_at',
        'location',
        'external_url',
        'is_featured',
        'is_published',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'event_starts_at' => 'datetime',
            'event_ends_at' => 'datetime',
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
        ];
    }
}

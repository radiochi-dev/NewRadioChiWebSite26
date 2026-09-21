<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MusicTrack extends Model
{
    protected $fillable = [
        'slug',
        'platform',
        'label_image_path',
        'cover_image_path',
        'stream_url',
        'external_url',
        'genre',
        'year',
        'position',
        'is_featured',
        'is_published',
        'published_at',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'position' => 'integer',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'settings' => 'array',
        ];
    }

    public function translations(): HasMany
    {
        return $this->hasMany(MusicTrackTranslation::class);
    }
}

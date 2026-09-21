<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DownloadableFile extends Model
{
    protected $fillable = [
        'slug',
        'display_name',
        'description',
        'disk',
        'file_path',
        'file_name',
        'mime_type',
        'size',
        'external_url',
        'collection',
        'attachable_type',
        'attachable_id',
        'position',
        'is_active',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'position' => 'integer',
            'is_active' => 'boolean',
            'settings' => 'array',
        ];
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }
}

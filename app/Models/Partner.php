<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'partner_type',
        'website_url',
        'logo_path',
        'position',
        'is_active',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'is_active' => 'boolean',
            'settings' => 'array',
        ];
    }
}

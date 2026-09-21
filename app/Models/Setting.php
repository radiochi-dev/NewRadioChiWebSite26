<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Setting extends Model
{
    protected $fillable = [
        'group',
        'key',
        'type',
        'value',
        'is_translatable',
        'is_public',
        'position',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'array',
            'is_translatable' => 'boolean',
            'is_public' => 'boolean',
            'position' => 'integer',
            'settings' => 'array',
        ];
    }

    public function translations(): HasMany
    {
        return $this->hasMany(SettingTranslation::class);
    }
}

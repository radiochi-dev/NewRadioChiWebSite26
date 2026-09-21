<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LegalDocument extends Model
{
    protected $fillable = [
        'slug',
        'document_type',
        'version',
        'position',
        'is_published',
        'published_at',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'settings' => 'array',
        ];
    }

    public function translations(): HasMany
    {
        return $this->hasMany(LegalDocumentTranslation::class);
    }
}

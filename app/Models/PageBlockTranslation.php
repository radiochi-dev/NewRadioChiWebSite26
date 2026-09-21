<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageBlockTranslation extends Model
{
    protected $fillable = [
        'page_block_id',
        'locale',
        'content',
    ];

    protected function casts(): array
    {
        return [
            'content' => 'array',
        ];
    }

    public function pageBlock(): BelongsTo
    {
        return $this->belongsTo(PageBlock::class);
    }
}

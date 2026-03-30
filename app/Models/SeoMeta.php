<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoMeta extends Model
{
    protected $table = 'seo_meta';

    protected $fillable = [
        'entity_type',
        'entity_id',
        'locale',
        'meta_title',
        'meta_description',
        'canonical_url',
        'open_graph',
        'twitter_card',
        'json_ld',
    ];

    protected function casts(): array
    {
        return [
            'open_graph' => 'array',
            'twitter_card' => 'array',
            'json_ld' => 'array',
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NewsletterCampaign extends Model
{
    protected $fillable = [
        'name',
        'subject',
        'html_body',
        'status',
        'scheduled_at',
        'sent_at',
        'sent_count',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function logs(): HasMany
    {
        return $this->hasMany(NewsletterLog::class, 'campaign_id');
    }
}

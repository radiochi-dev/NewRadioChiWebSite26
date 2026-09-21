<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MusicTrackTranslation extends Model
{
    protected $fillable = [
        'music_track_id',
        'locale',
        'artist_name',
        'title',
        'hero_title',
        'subtitle',
        'description',
        'cta_primary_label',
        'cta_secondary_label',
    ];

    public function musicTrack(): BelongsTo
    {
        return $this->belongsTo(MusicTrack::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LegalDocumentTranslation extends Model
{
    protected $fillable = [
        'legal_document_id',
        'locale',
        'title',
        'summary',
        'content',
        'cta_label',
    ];

    public function legalDocument(): BelongsTo
    {
        return $this->belongsTo(LegalDocument::class);
    }
}

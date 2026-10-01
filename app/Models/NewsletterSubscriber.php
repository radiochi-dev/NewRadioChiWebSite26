<?php

namespace App\Models;

use App\Support\NewsletterLegalConsent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class NewsletterSubscriber extends Model
{
    protected $fillable = [
        'email',
        'name',
        'is_active',
        'subscribed_at',
        'unsubscribed_at',
        'confirmation_token',
        'unsubscribe_token',
        'ip_address',
        'user_agent',
        'consent_text_version',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'subscribed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $subscriber): void {
            $subscriber->email = Str::lower(trim((string) $subscriber->email));
            $subscriber->is_active ??= false;
            $subscriber->unsubscribe_token ??= Str::random(64);
            $subscriber->consent_text_version ??= NewsletterLegalConsent::consentVersion();
        });

        static::updating(function (self $subscriber): void {
            $subscriber->email = Str::lower(trim((string) $subscriber->email));
            $subscriber->is_active ??= false;
            $subscriber->unsubscribe_token ??= Str::random(64);
            $subscriber->consent_text_version ??= NewsletterLegalConsent::consentVersion();
        });
    }

    public function logs(): HasMany
    {
        return $this->hasMany(NewsletterLog::class, 'subscriber_id');
    }
}

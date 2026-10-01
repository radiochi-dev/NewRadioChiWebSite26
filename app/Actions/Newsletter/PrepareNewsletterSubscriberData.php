<?php

namespace App\Actions\Newsletter;

use App\Models\NewsletterSubscriber;
use Illuminate\Support\Str;

class PrepareNewsletterSubscriberData
{
    public function execute(array $data, ?NewsletterSubscriber $subscriber = null): array
    {
        if (array_key_exists('email', $data)) {
            $data['email'] = Str::lower(trim((string) $data['email']));
        }

        if ($subscriber === null) {
            $data['subscribed_at'] = ($data['is_active'] ?? false) ? now() : null;
            $data['unsubscribed_at'] = null;

            return $data;
        }

        if (array_key_exists('is_active', $data) && $data['is_active'] === false && $subscriber->unsubscribed_at === null) {
            $data['unsubscribed_at'] = now();
            $data['subscribed_at'] = $subscriber->subscribed_at;
        }

        if (array_key_exists('is_active', $data) && $data['is_active'] === true) {
            $data['unsubscribed_at'] = null;
            $data['subscribed_at'] = $subscriber->subscribed_at ?? now();
        }

        return $data;
    }
}

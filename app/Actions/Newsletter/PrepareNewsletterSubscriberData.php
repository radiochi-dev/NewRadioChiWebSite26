<?php

namespace App\Actions\Newsletter;

use App\Models\NewsletterSubscriber;

class PrepareNewsletterSubscriberData
{
    public function execute(array $data, ?NewsletterSubscriber $subscriber = null): array
    {
        if ($subscriber === null) {
            $data['subscribed_at'] = now();

            return $data;
        }

        if (array_key_exists('is_active', $data) && $data['is_active'] === false && $subscriber->unsubscribed_at === null) {
            $data['unsubscribed_at'] = now();
        }

        if (array_key_exists('is_active', $data) && $data['is_active'] === true) {
            $data['unsubscribed_at'] = null;
            $data['subscribed_at'] = $subscriber->subscribed_at ?? now();
        }

        return $data;
    }
}

<?php

namespace App\Actions\Newsletter;

use App\Models\NewsletterSubscriber;
use Illuminate\Support\Facades\DB;

class ManageNewsletterSubscriberBackofficeAction
{
    public function __construct(
        private readonly PrepareNewsletterSubscriberData $prepareNewsletterSubscriberData,
    ) {
    }

    public function execute(NewsletterSubscriber $subscriber, string $operation): void
    {
        DB::transaction(function () use ($subscriber, $operation): void {
            match ($operation) {
                'unsubscribe' => $this->unsubscribe($subscriber),
                'reactivate' => $this->reactivate($subscriber),
                'forget' => $this->forget($subscriber),
                default => abort(404),
            };
        });
    }

    private function unsubscribe(NewsletterSubscriber $subscriber): void
    {
        if (! $subscriber->is_active && $subscriber->unsubscribed_at !== null) {
            return;
        }

        $payload = $this->prepareNewsletterSubscriberData->execute([
            'is_active' => false,
            'confirmation_token' => null,
        ], $subscriber);

        $subscriber->fill($payload);
        $subscriber->save();
    }

    private function reactivate(NewsletterSubscriber $subscriber): void
    {
        if ($subscriber->is_active && $subscriber->unsubscribed_at === null) {
            return;
        }

        $payload = $this->prepareNewsletterSubscriberData->execute([
            'is_active' => true,
            'confirmation_token' => null,
        ], $subscriber);

        $subscriber->fill($payload);
        $subscriber->save();
    }

    private function forget(NewsletterSubscriber $subscriber): void
    {
        $subscriber->delete();
    }
}

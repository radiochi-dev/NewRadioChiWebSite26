<?php

namespace App\Mail;

use App\Actions\Newsletter\ResolveNewsletterMailContextAction;
use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

abstract class BaseNewsletterMailable extends Mailable
{
    use Queueable;
    use SerializesModels;

    private ?array $newsletterContextCache = null;

    public function __construct(
        protected NewsletterSubscriber $subscriber,
        protected string $mailLocale,
    ) {
    }

    public function headers(): Headers
    {
        $context = $this->newsletterContext();

        return new Headers(
            text: [
                'List-Unsubscribe' => sprintf(
                    '<%s>, <%s>',
                    $context['unsubscribeUrl'],
                    $context['unsubscribeMailto'],
                ),
                'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
            ],
        );
    }

    protected function newsletterContext(): array
    {
        return $this->newsletterContextCache ??= app(ResolveNewsletterMailContextAction::class)
            ->execute($this->mailLocale, $this->subscriber);
    }
}

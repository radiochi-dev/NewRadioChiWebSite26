<?php

namespace App\Mail;

use App\Models\NewsletterSubscriber;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Facades\URL;

class NewsletterDoubleOptIn extends BaseNewsletterMailable implements ShouldQueue
{
    public function __construct(NewsletterSubscriber $subscriber, string $locale)
    {
        parent::__construct($subscriber, $locale);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('newsletter.mail.double_opt_in.subject', locale: $this->mailLocale),
            tags: ['newsletter', 'double-opt-in'],
            metadata: [
                'subscriber_id' => (string) $this->subscriber->getKey(),
                'message_type' => 'double_opt_in',
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.newsletter.double-opt-in',
            with: [
                'subscriber' => $this->subscriber,
                'context' => $this->newsletterContext(),
                'confirmUrl' => URL::temporarySignedRoute(
                    'newsletter.confirm',
                    now()->addDays(7),
                    [
                        'token' => $this->subscriber->confirmation_token,
                        'locale' => $this->mailLocale,
                    ],
                ),
                'locale' => $this->mailLocale,
            ],
        );
    }
}

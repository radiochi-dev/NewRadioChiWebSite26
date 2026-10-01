<?php

namespace App\Mail;

use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class NewsletterCampaignMail extends BaseNewsletterMailable
{
    public function __construct(
        protected NewsletterCampaign $campaign,
        NewsletterSubscriber $subscriber,
        string $locale,
    ) {
        parent::__construct($subscriber, $locale);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->campaign->subject,
            tags: ['newsletter', 'campaign'],
            metadata: [
                'campaign_id' => (string) $this->campaign->getKey(),
                'subscriber_id' => (string) $this->subscriber->getKey(),
                'message_type' => 'campaign',
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.newsletter.campaign',
            with: [
                'campaign' => $this->campaign,
                'subscriber' => $this->subscriber,
                'context' => $this->newsletterContext(),
                'locale' => $this->mailLocale,
            ],
        );
    }
}

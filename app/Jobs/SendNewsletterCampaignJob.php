<?php

namespace App\Jobs;

use App\Models\NewsletterCampaign;
use App\Models\NewsletterLog;
use App\Models\NewsletterSubscriber;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendNewsletterCampaignJob implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly int $campaignId)
    {
        $this->onQueue('newsletter');
    }

    public function handle(): void
    {
        $campaign = NewsletterCampaign::query()->findOrFail($this->campaignId);

        $subscribers = NewsletterSubscriber::query()
            ->where('is_active', true)
            ->get(['id', 'email', 'name']);

        $sentCount = 0;

        foreach ($subscribers as $subscriber) {
            try {
                Mail::html($campaign->html_body, function ($message) use ($campaign, $subscriber) {
                    $message->to($subscriber->email, $subscriber->name ?: null);
                    $message->subject($campaign->subject);
                });

                NewsletterLog::query()->create([
                    'campaign_id' => $campaign->id,
                    'subscriber_id' => $subscriber->id,
                    'status' => 'sent',
                    'processed_at' => now(),
                ]);

                $sentCount++;
            } catch (\Throwable $throwable) {
                NewsletterLog::query()->create([
                    'campaign_id' => $campaign->id,
                    'subscriber_id' => $subscriber->id,
                    'status' => 'failed',
                    'error_message' => $throwable->getMessage(),
                    'processed_at' => now(),
                ]);
            }
        }

        $campaign->update([
            'status' => 'sent',
            'sent_at' => now(),
            'sent_count' => $sentCount,
        ]);
    }
}

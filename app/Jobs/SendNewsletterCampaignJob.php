<?php

namespace App\Jobs;

use App\Mail\NewsletterCampaignMail;
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
            ->get(['id', 'email', 'name', 'unsubscribe_token']);

        $sentCount = 0;

        foreach ($subscribers as $subscriber) {
            try {
                Mail::to($subscriber->email, $subscriber->name ?: null)
                    ->send(new NewsletterCampaignMail($campaign, $subscriber, config('app.locale', 'es')));

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

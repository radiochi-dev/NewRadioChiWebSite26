<?php

use App\Jobs\SendNewsletterCampaignJob;
use App\Jobs\SyncInstagramFeedJob;
use App\Models\NewsletterCampaign;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('newsletter:send-campaign {campaignId}', function (int $campaignId) {
    $campaign = NewsletterCampaign::query()->findOrFail($campaignId);

    $campaign->update(['status' => 'queued']);
    SendNewsletterCampaignJob::dispatch($campaignId)->onQueue('newsletter');

    $this->info('Campaign queued: '.$campaignId);
})->purpose('Queue newsletter campaign delivery');

Artisan::command('instagram:sync', function () {
    SyncInstagramFeedJob::dispatch();

    $this->info('Instagram sync queued');
})->purpose('Queue Instagram feed synchronization');

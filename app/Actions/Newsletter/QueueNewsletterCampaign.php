<?php

namespace App\Actions\Newsletter;

use App\Jobs\SendNewsletterCampaignJob;
use App\Models\NewsletterCampaign;

class QueueNewsletterCampaign
{
    public function execute(NewsletterCampaign $campaign): NewsletterCampaign
    {
        $campaign->update(['status' => 'queued']);

        SendNewsletterCampaignJob::dispatch($campaign->id)->onQueue('newsletter');

        return $campaign->fresh();
    }
}

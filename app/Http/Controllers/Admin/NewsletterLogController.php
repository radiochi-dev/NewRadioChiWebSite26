<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterLog;
use Illuminate\Http\JsonResponse;

class NewsletterLogController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            NewsletterLog::query()->latest('processed_at')->paginate(100),
        );
    }

    public function byCampaign(NewsletterCampaign $campaign): JsonResponse
    {
        return response()->json(
            NewsletterLog::query()
                ->where('campaign_id', $campaign->id)
                ->latest('processed_at')
                ->paginate(100),
        );
    }
}

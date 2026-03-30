<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendNewsletterCampaignJob;
use App\Models\NewsletterCampaign;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsletterCampaignController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(NewsletterCampaign::query()->latest()->paginate(25));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'html_body' => ['required', 'string'],
            'scheduled_at' => ['nullable', 'date'],
        ]);

        $campaign = NewsletterCampaign::query()->create([
            ...$data,
            'status' => 'draft',
        ]);

        return response()->json($campaign, 201);
    }

    public function show(NewsletterCampaign $campaign): JsonResponse
    {
        return response()->json($campaign);
    }

    public function update(Request $request, NewsletterCampaign $campaign): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'subject' => ['sometimes', 'string', 'max:255'],
            'html_body' => ['sometimes', 'string'],
            'scheduled_at' => ['nullable', 'date'],
            'status' => ['sometimes', 'in:draft,queued,sent,cancelled'],
        ]);

        $campaign->update($data);

        return response()->json($campaign->fresh());
    }

    public function destroy(NewsletterCampaign $campaign): JsonResponse
    {
        $campaign->delete();

        return response()->json(null, 204);
    }

    public function queue(NewsletterCampaign $campaign): JsonResponse
    {
        $campaign->update(['status' => 'queued']);
        SendNewsletterCampaignJob::dispatch($campaign->id)->onQueue('newsletter');

        return response()->json([
            'message' => 'Campaign queued',
            'campaign_id' => $campaign->id,
        ]);
    }
}

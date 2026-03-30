<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\MediaAsset;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use App\Models\Page;
use App\Models\SeoMeta;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Dashboard/Index', [
            'stats' => [
                'events' => Event::count(),
                'pages' => Page::count(),
                'media' => MediaAsset::count(),
                'seo' => SeoMeta::count(),
                'subscribers' => NewsletterSubscriber::count(),
                'campaigns' => NewsletterCampaign::count(),
            ],
            'latestEvents' => Event::query()->latest()->take(8)->get(['id', 'title', 'event_starts_at', 'event_ends_at', 'location', 'is_published']),
            'latestPages' => Page::query()->latest()->take(8)->get(['id', 'slug', 'template', 'is_published']),
            'latestMedia' => MediaAsset::query()->latest()->take(8)->get(['id', 'disk', 'path', 'filename', 'mime_type']),
            'latestCampaigns' => NewsletterCampaign::latest()->take(8)->get(['id', 'subject', 'status', 'scheduled_at', 'sent_at']),
        ]);
    }
}

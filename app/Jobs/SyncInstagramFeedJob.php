<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SyncInstagramFeedJob implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        $endpoint = config('services.instagram.endpoint');
        $token = config('services.instagram.token');

        if (! is_string($endpoint) || $endpoint === '' || ! is_string($token) || $token === '') {
            return;
        }

        $response = Http::timeout(20)->get($endpoint, [
            'access_token' => $token,
            'fields' => 'id,caption,media_type,media_url,permalink,timestamp',
            'limit' => 12,
        ]);

        if (! $response->successful()) {
            return;
        }

        $payload = $response->json('data', []);

        Cache::put('radiochi.instagram.feed', $payload, now()->addMinutes(30));
    }
}

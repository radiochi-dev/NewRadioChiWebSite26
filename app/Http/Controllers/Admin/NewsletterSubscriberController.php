<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NewsletterSubscriberController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(NewsletterSubscriber::query()->latest()->paginate(50));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email:rfc,dns', 'max:255', Rule::unique('newsletter_subscribers', 'email')],
            'name' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $subscriber = NewsletterSubscriber::query()->create([
            ...$data,
            'subscribed_at' => now(),
        ]);

        return response()->json($subscriber, 201);
    }

    public function show(NewsletterSubscriber $subscriber): JsonResponse
    {
        return response()->json($subscriber);
    }

    public function update(Request $request, NewsletterSubscriber $subscriber): JsonResponse
    {
        $data = $request->validate([
            'email' => ['sometimes', 'email:rfc,dns', 'max:255', Rule::unique('newsletter_subscribers', 'email')->ignore($subscriber->id)],
            'name' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if (array_key_exists('is_active', $data) && $data['is_active'] === false && $subscriber->unsubscribed_at === null) {
            $data['unsubscribed_at'] = now();
        }

        if (array_key_exists('is_active', $data) && $data['is_active'] === true) {
            $data['unsubscribed_at'] = null;
            $data['subscribed_at'] = $subscriber->subscribed_at ?? now();
        }

        $subscriber->update($data);

        return response()->json($subscriber->fresh());
    }

    public function destroy(NewsletterSubscriber $subscriber): JsonResponse
    {
        $subscriber->delete();

        return response()->json(null, 204);
    }
}

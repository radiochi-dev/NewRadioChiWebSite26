<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoMeta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SeoMetaController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(SeoMeta::query()->latest()->paginate(25));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'entity_type' => ['required', 'string', 'max:255'],
            'entity_id' => ['required', 'integer', 'min:1'],
            'locale' => ['required', 'string', 'max:5', Rule::in(['es', 'en', 'ca', 'fr', 'it', 'de'])],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'canonical_url' => ['nullable', 'url', 'max:255'],
            'open_graph' => ['nullable', 'array'],
            'twitter_card' => ['nullable', 'array'],
            'json_ld' => ['nullable', 'array'],
        ]);

        $meta = SeoMeta::query()->updateOrCreate(
            [
                'entity_type' => $data['entity_type'],
                'entity_id' => $data['entity_id'],
                'locale' => $data['locale'],
            ],
            [
                'meta_title' => $data['meta_title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
                'canonical_url' => $data['canonical_url'] ?? null,
                'open_graph' => $data['open_graph'] ?? null,
                'twitter_card' => $data['twitter_card'] ?? null,
                'json_ld' => $data['json_ld'] ?? null,
            ],
        );

        return response()->json($meta, 201);
    }

    public function show(SeoMeta $seoMetum): JsonResponse
    {
        return response()->json($seoMetum);
    }

    public function update(Request $request, SeoMeta $seoMetum): JsonResponse
    {
        $data = $request->validate([
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'canonical_url' => ['nullable', 'url', 'max:255'],
            'open_graph' => ['nullable', 'array'],
            'twitter_card' => ['nullable', 'array'],
            'json_ld' => ['nullable', 'array'],
        ]);

        $seoMetum->update($data);

        return response()->json($seoMetum->fresh());
    }

    public function destroy(SeoMeta $seoMetum): JsonResponse
    {
        $seoMetum->delete();

        return response()->json(null, 204);
    }
}

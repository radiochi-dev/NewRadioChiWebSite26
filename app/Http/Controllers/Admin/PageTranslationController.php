<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageTranslation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PageTranslationController extends Controller
{
    public function store(Request $request, Page $page): JsonResponse
    {
        $data = $request->validate([
            'locale' => ['required', 'string', 'max:5', Rule::in(['es', 'en', 'ca', 'fr', 'it', 'de'])],
            'title' => ['required', 'string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'content' => ['nullable', 'array'],
        ]);

        $translation = PageTranslation::query()->updateOrCreate(
            ['page_id' => $page->id, 'locale' => $data['locale']],
            [
                'title' => $data['title'],
                'meta_title' => $data['meta_title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
                'content' => $data['content'] ?? null,
            ],
        );

        return response()->json($translation, 201);
    }

    public function show(Page $page, PageTranslation $translation): JsonResponse
    {
        abort_unless($translation->page_id === $page->id, 404);

        return response()->json($translation);
    }

    public function update(Request $request, Page $page, PageTranslation $translation): JsonResponse
    {
        abort_unless($translation->page_id === $page->id, 404);

        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'content' => ['nullable', 'array'],
        ]);

        $translation->update($data);

        return response()->json($translation->fresh());
    }

    public function destroy(Page $page, PageTranslation $translation): JsonResponse
    {
        abort_unless($translation->page_id === $page->id, 404);

        $translation->delete();

        return response()->json(null, 204);
    }
}

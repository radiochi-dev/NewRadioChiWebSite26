<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PageController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Page::query()->with('translations')->latest()->paginate(25),
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'slug' => ['required', 'string', 'max:255', Rule::unique('pages', 'slug')],
            'template' => ['required', 'string', 'max:120'],
            'is_published' => ['sometimes', 'boolean'],
            'published_at' => ['nullable', 'date'],
        ]);

        $page = Page::query()->create($data);

        return response()->json($page->load('translations'), 201);
    }

    public function show(Page $page): JsonResponse
    {
        return response()->json($page->load('translations'));
    }

    public function update(Request $request, Page $page): JsonResponse
    {
        $data = $request->validate([
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('pages', 'slug')->ignore($page->id)],
            'template' => ['sometimes', 'string', 'max:120'],
            'is_published' => ['sometimes', 'boolean'],
            'published_at' => ['nullable', 'date'],
        ]);

        $page->update($data);

        return response()->json($page->fresh()->load('translations'));
    }

    public function destroy(Page $page): JsonResponse
    {
        $page->delete();

        return response()->json(null, 204);
    }
}

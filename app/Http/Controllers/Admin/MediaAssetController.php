<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaAsset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MediaAssetController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(MediaAsset::query()->latest()->paginate(25));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'disk' => ['required', 'string', 'max:60'],
            'path' => ['required', 'string', 'max:255'],
            'filename' => ['required', 'string', 'max:255'],
            'mime_type' => ['nullable', 'string', 'max:120'],
            'size' => ['nullable', 'integer', 'min:0'],
            'width' => ['nullable', 'integer', 'min:0'],
            'height' => ['nullable', 'integer', 'min:0'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'metadata' => ['nullable', 'array'],
        ]);

        $asset = MediaAsset::query()->create($data);

        return response()->json($asset, 201);
    }

    public function show(MediaAsset $media): JsonResponse
    {
        return response()->json($media);
    }

    public function update(Request $request, MediaAsset $media): JsonResponse
    {
        $data = $request->validate([
            'disk' => ['sometimes', 'string', 'max:60'],
            'path' => ['sometimes', 'string', 'max:255'],
            'filename' => ['sometimes', 'string', 'max:255'],
            'mime_type' => ['nullable', 'string', 'max:120'],
            'size' => ['nullable', 'integer', 'min:0'],
            'width' => ['nullable', 'integer', 'min:0'],
            'height' => ['nullable', 'integer', 'min:0'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'metadata' => ['nullable', 'array'],
        ]);

        $media->update($data);

        return response()->json($media->fresh());
    }

    public function destroy(MediaAsset $media): JsonResponse
    {
        $media->delete();

        return response()->json(null, 204);
    }
}

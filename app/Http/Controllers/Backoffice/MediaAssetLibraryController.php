<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\MediaAsset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;

class MediaAssetLibraryController extends Controller
{
    public function uploadImage(Request $request): JsonResponse
    {
        abort_unless($request->user()?->canManageBackofficeContent(), 403);

        $validated = $request->validate([
            'image' => ['required', File::image()->max(10240)],
        ]);

        /** @var UploadedFile $image */
        $image = $validated['image'];

        $directory = 'backoffice/media/'.now()->format('Y/m');
        $baseName = Str::slug(pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME));
        $extension = $image->extension() ?: $image->getClientOriginalExtension() ?: 'bin';
        $storedFileName = trim(Str::uuid()->toString().'-'.$baseName, '-').'.'.$extension;
        $storedPath = $image->storeAs($directory, $storedFileName, 'public');

        [$width, $height] = $this->imageDimensions($image);

        $asset = MediaAsset::query()->create([
            'disk' => 'public',
            'path' => Storage::disk('public')->url($storedPath),
            'filename' => basename($storedPath),
            'mime_type' => $image->getMimeType(),
            'size' => $image->getSize(),
            'width' => $width,
            'height' => $height,
            'alt_text' => Str::headline(pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME)),
            'metadata' => [
                'source' => 'backoffice_upload',
                'original_name' => $image->getClientOriginalName(),
            ],
        ]);

        return response()->json([
            'asset' => [
                'id' => (string) $asset->getKey(),
                'path' => (string) $asset->path,
                'previewUrl' => (string) $asset->path,
                'filename' => (string) $asset->filename,
                'altText' => (string) ($asset->alt_text ?? ''),
                'mimeType' => (string) ($asset->mime_type ?? ''),
                'width' => $asset->width,
                'height' => $asset->height,
            ],
        ], 201);
    }

    /**
     * @return array{0: int|null, 1: int|null}
     */
    private function imageDimensions(UploadedFile $image): array
    {
        $dimensions = @getimagesize($image->getPathname());

        if (! is_array($dimensions)) {
            return [null, null];
        }

        return [
            isset($dimensions[0]) ? (int) $dimensions[0] : null,
            isset($dimensions[1]) ? (int) $dimensions[1] : null,
        ];
    }
}

<?php

namespace App\Http\Controllers\Automation;

use App\Actions\PublicSite\BuildPublicHomePayloadAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AutomationPublicHomePayloadController extends Controller
{
    private const LOCALES = ['es', 'en', 'ca', 'fr', 'it', 'de'];

    public function __invoke(string $locale, Request $request, BuildPublicHomePayloadAction $payload): JsonResponse
    {
        abort_unless(in_array($locale, self::LOCALES, true), 404);

        return response()->json(
            $payload->execute($locale, rtrim(config('app.url', $request->getSchemeAndHttpHost()), '/'))
        );
    }
}

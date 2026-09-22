<?php

namespace App\Http\Controllers;

use App\Actions\PublicSite\BuildPublicHomePayloadAction;
use App\Actions\PublicSite\ResolvePublicAnalyticsConfigAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Inertia\Inertia;
use Inertia\Response;

class PublicHomeController extends Controller
{
    private const LOCALES = ['es', 'en', 'ca', 'fr', 'it', 'de'];

    public function index(
        Request $request,
        BuildPublicHomePayloadAction $payload,
        ResolvePublicAnalyticsConfigAction $analyticsConfig,
    ): Response|RedirectResponse
    {
        $preferredLocale = $request->cookie('radiochi_locale');

        if (is_string($preferredLocale) && in_array($preferredLocale, self::LOCALES, true) && $preferredLocale !== 'es') {
            return redirect('/'.$preferredLocale);
        }

        return $this->renderHome('es', $request, $payload, $analyticsConfig);
    }

    public function localized(
        string $locale,
        Request $request,
        BuildPublicHomePayloadAction $payload,
        ResolvePublicAnalyticsConfigAction $analyticsConfig,
    ): Response
    {
        abort_unless(in_array($locale, self::LOCALES, true), 404);

        return $this->renderHome($locale, $request, $payload, $analyticsConfig);
    }

    private function renderHome(
        string $locale,
        Request $request,
        BuildPublicHomePayloadAction $payload,
        ResolvePublicAnalyticsConfigAction $analyticsConfig,
    ): Response
    {
        app()->setLocale($locale);
        $baseUrl = rtrim(config('app.url', $request->getSchemeAndHttpHost()), '/');
        $homePayload = $payload->execute($locale, $baseUrl);

        Cookie::queue(Cookie::forever('radiochi_locale', $locale));

        return Inertia::render('Home', [
            'locale' => $locale,
            'locales' => self::LOCALES,
            'currentPath' => $request->getPathInfo(),
            'content' => $homePayload['content'],
            'calendarData' => $homePayload['calendarData'],
            'mediaData' => $homePayload['mediaData'],
            'contactData' => $homePayload['contactData'],
            'events' => $homePayload['events'],
            'seo' => $homePayload['seo'],
            'analytics' => $analyticsConfig->execute(),
        ]);
    }
}

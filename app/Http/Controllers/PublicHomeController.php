<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Inertia\Inertia;
use Inertia\Response;

class PublicHomeController extends Controller
{
    private const LOCALES = ['es', 'en', 'ca', 'fr', 'it', 'de'];

    public function index(Request $request): Response|RedirectResponse
    {
        $preferredLocale = $request->cookie('radiochi_locale');

        if (is_string($preferredLocale) && in_array($preferredLocale, self::LOCALES, true) && $preferredLocale !== 'es') {
            return redirect('/'.$preferredLocale);
        }

        return $this->renderHome('es', $request);
    }

    public function localized(string $locale, Request $request): Response
    {
        abort_unless(in_array($locale, self::LOCALES, true), 404);

        return $this->renderHome($locale, $request);
    }

    private function renderHome(string $locale, Request $request): Response
    {
        app()->setLocale($locale);

        $events = Event::query()
            ->where('is_published', true)
            ->orderBy('event_starts_at')
            ->get()
            ->map(fn (Event $event) => [
                'slug' => $event->slug,
                'title' => $event->title,
                'location' => $event->location,
                'external_url' => $event->external_url,
                'start_label' => optional($event->event_starts_at)->format('Y-m-d') ?? 'TBD',
            ])
            ->values();

        $description = match ($locale) {
            'en' => 'International DJ and producer. House, Tech House and global events.',
            'ca' => 'DJ i productor internacional. House, Tech House i esdeveniments globals.',
            'fr' => 'DJ et producteur international. House, Tech House et événements mondiaux.',
            'it' => 'DJ e producer internazionale. House, Tech House ed eventi globali.',
            'de' => 'Internationaler DJ und Produzent. House, Tech House und globale Events.',
            default => 'DJ y productor internacional. House, Tech House y eventos globales.',
        };

        $canonicalPath = $locale === 'es' ? '/' : '/'.$locale;
        $canonical = rtrim(config('app.url', $request->getSchemeAndHttpHost()), '/').$canonicalPath;

        $alternates = collect(self::LOCALES)
            ->mapWithKeys(fn (string $supportedLocale) => [
                $supportedLocale => rtrim(config('app.url', $request->getSchemeAndHttpHost()), '/').($supportedLocale === 'es' ? '/' : '/'.$supportedLocale),
            ])
            ->all();

        Cookie::queue(Cookie::forever('radiochi_locale', $locale));

        return Inertia::render('Home', [
            'locale' => $locale,
            'locales' => self::LOCALES,
            'currentPath' => $request->getPathInfo(),
            'events' => $events,
            'seo' => [
                'title' => 'RadioChi',
                'description' => $description,
                'canonical' => $canonical,
                'alternates' => $alternates,
                'xDefault' => $alternates['es'],
                'ogType' => 'website',
                'ogImage' => rtrim(config('app.url', $request->getSchemeAndHttpHost()), '/').'/favicon.ico',
                'jsonLd' => [
                    '@context' => 'https://schema.org',
                    '@type' => 'MusicGroup',
                    'name' => 'RadioChi',
                    'url' => $canonical,
                    'inLanguage' => $locale,
                    'sameAs' => [
                        'https://www.instagram.com/mrchiloveyou/',
                        'https://www.youtube.com/@cardonatoro',
                        'https://www.facebook.com/fernandocardonatoro',
                    ],
                ],
            ],
        ]);
    }
}

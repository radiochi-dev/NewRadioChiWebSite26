<?php

namespace App\Actions\PublicSite;

use App\Models\Event;
use App\Models\LegalDocument;
use App\Models\MediaAsset;
use App\Models\MusicTrack;
use App\Models\Page;
use App\Models\Partner;
use App\Models\SeoMeta;
use App\Models\Setting;
use App\Models\SocialLink;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class BuildPublicHomePayloadAction
{
    private const DEFAULT_LOCALE = 'es';

    private const LOCALES = ['es', 'en', 'ca', 'fr', 'it', 'de'];

    public function execute(string $locale, string $baseUrl): array
    {
        $locale = in_array($locale, self::LOCALES, true) ? $locale : self::DEFAULT_LOCALE;
        $analytics = app(ResolvePublicAnalyticsConfigAction::class)->execute();

        $pages = Page::query()
            ->whereIn('slug', ['home', 'about', 'music', 'calendar', 'media', 'contact'])
            ->where('is_published', true)
            ->with([
                'translations' => fn ($query) => $query->whereIn('locale', [$locale, self::DEFAULT_LOCALE]),
                'blocks' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('position')
                    ->with([
                        'translations' => fn ($translationsQuery) => $translationsQuery->whereIn('locale', [$locale, self::DEFAULT_LOCALE]),
                    ]),
            ])
            ->get()
            ->keyBy('slug');

        $settings = Setting::query()
            ->where('is_public', true)
            ->where(function ($query) {
                $query->whereIn('group', ['header', 'intro', 'footer', 'legal', 'media'])
                    ->orWhere(function ($nestedQuery) {
                        $nestedQuery->where('group', 'contact')
                            ->where('key', 'marquee_rows');
                    });
            })
            ->with([
                'translations' => fn ($query) => $query->whereIn('locale', [$locale, self::DEFAULT_LOCALE]),
            ])
            ->get()
            ->keyBy(fn (Setting $setting) => "{$setting->group}.{$setting->key}");

        $musicTracks = MusicTrack::query()
            ->where('is_published', true)
            ->orderBy('position')
            ->with([
                'translations' => fn ($query) => $query->whereIn('locale', [$locale, self::DEFAULT_LOCALE]),
            ])
            ->get();

        $mediaAssets = MediaAsset::query()
            ->get()
            ->sortBy(fn (MediaAsset $asset) => (int) data_get($asset->metadata, 'position', PHP_INT_MAX))
            ->values();

        $partners = Partner::query()
            ->where('is_active', true)
            ->orderBy('position')
            ->get();

        $socialLinks = SocialLink::query()
            ->where('is_active', true)
            ->where('location', 'global')
            ->orderBy('position')
            ->get();

        $legalDocuments = LegalDocument::query()
            ->where('is_published', true)
            ->orderBy('position')
            ->with([
                'translations' => fn ($query) => $query->whereIn('locale', [$locale, self::DEFAULT_LOCALE]),
            ])
            ->get()
            ->keyBy('document_type');

        $events = Event::query()
            ->where('is_published', true)
            ->get()
            ->sortBy(fn (Event $event) => $event->event_starts_at?->getTimestamp() ?? PHP_INT_MAX)
            ->values();

        $content = [
            'home' => $this->buildHomeContent($pages->get('home'), $locale),
            'about' => $this->buildAboutContent($pages->get('about'), $locale),
            'music' => $this->buildMusicContent($pages->get('music'), $musicTracks, $locale),
            'contact' => $this->pageTranslationContent($pages->get('contact'), $locale),
            'media' => $this->pageTranslationContent($pages->get('media'), $locale),
            'header' => [
                'openMenu' => data_get($this->settingValue($settings, 'header', 'open_menu', $locale), 'label', 'Open menu'),
                'menu' => data_get($this->settingValue($settings, 'header', 'menu', $locale), 'items', []),
            ],
            'intro' => [
                'welcome' => data_get($this->settingValue($settings, 'intro', 'welcome', $locale), 'label', 'Bienvenido a'),
            ],
            'footer' => [
                'copyright' => data_get($this->settingValue($settings, 'footer', 'credits', $locale), 'copyright', '© 2025 Copyright.'),
                'rights' => data_get($this->settingValue($settings, 'footer', 'credits', $locale), 'rights', ''),
            ],
            'termsPolicyCookies' => $this->buildLegalContent($legalDocuments, $settings, $locale, $analytics),
        ];

        $calendarData = [
            'events' => $this->buildCalendarEvents($events),
            'translations' => $this->pageTranslationContent($pages->get('calendar'), $locale),
        ];

        $content['media']['youtubeChannelUrl'] = data_get(
            $this->settingValue($settings, 'media', 'youtube_channel_url', $locale),
            'value',
            'https://www.youtube.com/channel/TUCANALAQUI',
        );

        $mediaData = [
            'photos' => $this->buildPhotos($mediaAssets),
            'videos' => $this->buildVideos($mediaAssets),
        ];

        $contactData = [
            'socialLinks' => $this->buildSocialLinks($socialLinks),
            'footerSocialLinks' => $this->buildSocialLinks($socialLinks),
            'sponsorLogos' => $this->buildPartners($partners),
            'marqueeRows' => $this->buildContactMarqueeRows($pages->get('contact'), $settings, $locale),
        ];

        $seo = $this->buildSeo(
            page: $pages->get('home'),
            locale: $locale,
            baseUrl: $baseUrl,
            fallbackTitle: $content['home']['name'] ?? 'RadioChi',
            socialLinks: $socialLinks,
        );

        return [
            'content' => $content,
            'calendarData' => $calendarData,
            'mediaData' => $mediaData,
            'contactData' => $contactData,
            'events' => $this->buildCompactEvents($events),
            'seo' => $seo,
        ];
    }

    private function buildHomeContent(?Page $page, string $locale): array
    {
        $content = $this->pageTranslationContent($page, $locale);
        $content['slides'] = $page?->blocks
            ->where('type', 'hero_slide')
            ->values()
            ->map(function ($block) use ($locale): array {
                $translation = $this->blockTranslationContent($block->translations, $locale);

                return [
                    'logo' => data_get($block->settings, 'logo'),
                    'logoPosition' => data_get($block->settings, 'logoPosition'),
                    'title' => data_get($translation, 'title'),
                    'subtitle' => data_get($translation, 'subtitle'),
                    'description' => data_get($translation, 'description'),
                    'personImage' => data_get($block->settings, 'personImage'),
                    'elipseImage' => data_get($block->settings, 'elipseImage'),
                    'buttonText' => data_get($translation, 'buttonText'),
                    'link' => data_get($translation, 'link'),
                    'event' => data_get($translation, 'event'),
                ];
            })
            ->all() ?? [];

        return $content;
    }

    private function buildAboutContent(?Page $page, string $locale): array
    {
        $content = $this->pageTranslationContent($page, $locale);
        $content['scrollytelling']['steps'] = $page?->blocks
            ->where('type', 'about_step')
            ->values()
            ->map(function ($block) use ($locale): array {
                $translation = $this->blockTranslationContent($block->translations, $locale);

                return [
                    'id' => $block->id,
                    'title' => data_get($translation, 'title'),
                    'subtitle' => data_get($translation, 'subtitle'),
                    'content' => data_get($translation, 'content'),
                    'image' => data_get($block->settings, 'image'),
                    'image2' => data_get($block->settings, 'image2'),
                    'chartTitle' => data_get($translation, 'chartTitle'),
                    'chartSubtitle' => data_get($translation, 'chartSubtitle'),
                ];
            })
            ->all() ?? [];

        return $content;
    }

    private function buildMusicContent(?Page $page, Collection $musicTracks, string $locale): array
    {
        $content = $this->pageTranslationContent($page, $locale);
        $content['tracks'] = $musicTracks
            ->map(function (MusicTrack $track) use ($locale): array {
                $translation = $this->translatedRecord($track->translations, $locale);

                return [
                    'id' => data_get($track->settings, 'legacy_id', $track->id),
                    'label-img' => $track->label_image_path,
                    'title' => $translation?->artist_name ?? $translation?->title ?? $track->slug,
                    'heroTitle' => $translation?->hero_title,
                    'subtitle' => $translation?->subtitle ?? $translation?->title,
                    'description' => $translation?->description,
                    'image' => $track->cover_image_path,
                    'soundcloudUrl' => $track->stream_url,
                    'year' => $track->year ? (string) $track->year : null,
                    'genre' => $track->genre,
                ];
            })
            ->values()
            ->all();

        return $content;
    }

    private function buildCalendarEvents(Collection $events): array
    {
        return $events
            ->map(function (Event $event): array {
                $country = $event->country;
                $location = $event->location;

                if (is_string($country) && is_string($location) && str_ends_with($location, ', '.$country)) {
                    $location = substr($location, 0, -strlen(', '.$country));
                }

                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'dateStart' => $event->event_starts_at?->format('d-m-y') ?? ' ',
                    'dateEnd' => $event->event_ends_at?->format('d-m-y') ?? ' ',
                    'location' => $location,
                    'country' => $country,
                    'logo' => $event->poster_path,
                    'linkEvent' => $event->external_url,
                ];
            })
            ->values()
            ->all();
    }

    private function buildCompactEvents(Collection $events): array
    {
        return $events
            ->map(fn (Event $event): array => [
                'slug' => $event->slug,
                'title' => $event->title,
                'location' => $event->location,
                'external_url' => $event->external_url,
                'start_label' => $event->event_starts_at?->format('Y-m-d') ?? 'TBD',
            ])
            ->values()
            ->all();
    }

    private function buildPhotos(Collection $mediaAssets): array
    {
        return $mediaAssets
            ->filter(fn (MediaAsset $asset): bool => data_get($asset->metadata, 'kind') === 'photo')
            ->map(fn (MediaAsset $asset): array => [
                'src' => $asset->path,
                'alt' => $asset->alt_text,
                'caption' => data_get($asset->metadata, 'caption'),
                'date' => data_get($asset->metadata, 'date'),
            ])
            ->values()
            ->all();
    }

    private function buildVideos(Collection $mediaAssets): array
    {
        return $mediaAssets
            ->filter(fn (MediaAsset $asset): bool => data_get($asset->metadata, 'kind') === 'video')
            ->map(fn (MediaAsset $asset): array => [
                'id' => data_get($asset->metadata, 'youtube_id', $asset->filename),
                'thumbnail' => data_get($asset->metadata, 'thumbnail'),
                'title' => data_get($asset->metadata, 'title', $asset->alt_text),
                'date' => data_get($asset->metadata, 'date'),
                'duration' => data_get($asset->metadata, 'duration'),
            ])
            ->values()
            ->all();
    }

    private function buildPartners(Collection $partners): array
    {
        return $partners
            ->map(fn (Partner $partner): array => [
                'name' => $partner->name,
                'url' => $partner->website_url,
                'imgSrc' => $partner->logo_path,
            ])
            ->values()
            ->all();
    }

    private function buildSocialLinks(Collection $socialLinks): array
    {
        return $socialLinks
            ->map(fn (SocialLink $socialLink): array => [
                'platform' => $socialLink->platform,
                'label' => $socialLink->label,
                'url' => $socialLink->url,
                'iconKey' => $socialLink->icon_key,
            ])
            ->values()
            ->all();
    }

    private function buildContactMarqueeRows(?Page $contactPage, Collection $settings, string $locale): array
    {
        $rowsFromBlocks = $contactPage?->blocks
            ->where('type', 'contact_marquee')
            ->values()
            ->map(fn ($block): array => data_get($this->blockTranslationContent($block->translations, $locale), 'words', []))
            ->filter(fn (array $words): bool => $words !== [])
            ->values()
            ->all() ?? [];

        if ($rowsFromBlocks !== []) {
            return $rowsFromBlocks;
        }

        return data_get($this->settingValue($settings, 'contact', 'marquee_rows', $locale), 'rows', []);
    }

    private function buildLegalContent(Collection $legalDocuments, Collection $settings, string $locale, array $analytics): array
    {
        $buttons = $this->settingValue($settings, 'legal', 'buttons', $locale);

        return [
            'terms_button' => data_get($buttons, 'terms_button', 'Términos y Condiciones'),
            'privacy_button' => data_get($buttons, 'privacy_button', 'Política de Privacidad'),
            'cookies_button' => data_get($buttons, 'cookies_button', 'Política de Cookies'),
            'terms' => $this->buildLegalDocumentPayload($legalDocuments->get('terms'), 'terms', $locale, $analytics),
            'privacy' => $this->buildLegalDocumentPayload($legalDocuments->get('privacy'), 'privacy', $locale, $analytics),
            'cookies' => $this->buildLegalDocumentPayload($legalDocuments->get('cookies'), 'cookies', $locale, $analytics),
        ];
    }

    private function buildLegalDocumentPayload(?LegalDocument $document, string $documentType, string $locale, array $analytics): array
    {
        $translation = $document ? $this->translatedRecord($document->translations, $locale) : null;

        return [
            'title' => $translation?->title,
            'content' => $this->alignLegalDocumentContent($documentType, $locale, $translation?->content, $analytics),
        ];
    }

    private function alignLegalDocumentContent(string $documentType, string $locale, ?string $content, array $analytics): ?string
    {
        if (! is_string($content) || trim($content) === '') {
            return $content;
        }

        if (! data_get($analytics, 'ga4.loadScript', false)) {
            return $content;
        }

        $replacements = $this->legalAnalyticsReplacements($locale);

        if (! isset($replacements[$documentType])) {
            return $content;
        }

        foreach ($replacements[$documentType] as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }

        return $content;
    }

    private function legalAnalyticsReplacements(string $locale): array
    {
        return match ($locale) {
            'en' => [
                'terms' => [
                    '<li>No cookies or tracking technologies that may collect personal information are used.</li>' => '<li>Google Analytics 4 may be used, once legally approved and activated in production, to measure navigation and public conversion events as described in the Cookie Policy and Privacy Policy.</li>',
                ],
                'privacy' => [
                    '<p>This Website does not use cookies of any type, neither own nor third-party, for analytical, advertising or tracking purposes. Navigation is completely free of trackers, ensuring that your activity is not monitored.</p>' => '<p>This Website may use Google Analytics 4, once legally approved and activated in production, to measure aggregated navigation and public conversion events. The analytics setup is limited to the site measurement described in the Cookie Policy and does not enable the script until that activation has been formally approved.</p>',
                ],
                'cookies' => [
                    '<p><strong>This Website DOES NOT USE any type of cookie, neither own nor third-party.</strong></p>' => '<p><strong>This Website may use Google Analytics 4 analytics cookies once legal approval has been completed and the production activation is explicitly enabled.</strong></p>',
                    '<p>We do not use any technology that stores information in your browser for tracking, analysis, advertising, or operational purposes. Your visit is completely anonymous and private from our website\'s perspective.</p>' => '<p>When analytics is activated, Google Analytics 4 is used only to measure page views and public navigation/conversion events such as hero CTA clicks, ticket clicks, YouTube clicks and social link clicks. Outside that approved activation, the analytics script remains disabled.</p>',
                ],
            ],
            'ca' => [
                'terms' => [
                    '<li>No s\'utilitzen galetes (cookies) ni tecnologies de seguiment que puguin recopilar informació personal.</li>' => '<li>Es pot utilitzar Google Analytics 4, un cop aprovat legalment i activat en producció, per mesurar la navegació i els esdeveniments públics de conversió descrits a la Política de Cookies i a la Política de Privacitat.</li>',
                ],
                'privacy' => [
                    '<p>Aquest Lloc Web no utilitza galetes (cookies) de cap tipus, ni pròpies ni de tercers, per a finalitats analítiques, publicitàries o de seguiment. La navegació és completament lliure de rastrejadors, garantint que la teva activitat no és monitorada.</p>' => '<p>Aquest Lloc Web pot utilitzar Google Analytics 4, un cop aprovat legalment i activat en producció, per mesurar de forma agregada la navegació i els esdeveniments públics de conversió. La configuració analítica queda limitada al mesurament descrit a la Política de Cookies i el script no s\'activa fins que aquesta activació ha estat aprovada formalment.</p>',
                ],
                'cookies' => [
                    '<p><strong>Aquest Lloc Web NO UTILITZA cap tipus de galeta (cookie), ni pròpia ni de tercers.</strong></p>' => '<p><strong>Aquest Lloc Web pot utilitzar cookies analítiques de Google Analytics 4 un cop completada l\'aprovació legal i habilitada explícitament l\'activació en producció.</strong></p>',
                    '<p>No fem servir cap tecnologia que emmagatzemi informació al teu navegador per a finalitats de seguiment, anàlisi, publicitat o funcionament. La teva visita és completament anònima i privada des del punt de vista del nostre lloc web.</p>' => '<p>Quan l\'analítica està activada, Google Analytics 4 s\'utilitza només per mesurar pàgines vistes i esdeveniments públics de navegació/conversió com clics en la CTA principal, entrades, YouTube i enllaços socials. Fora d\'aquesta activació aprovada, el script d\'analítica roman desactivat.</p>',
                ],
            ],
            'fr' => [
                'terms' => [
                    '<li>Aucun cookie ni technologie de suivi pouvant collecter des informations personnelles n\'est utilisé.</li>' => '<li>Google Analytics 4 peut être utilisé, une fois l\'approbation légale obtenue et l\'activation en production réalisée, afin de mesurer la navigation et les événements publics de conversion décrits dans la Politique de Cookies et la Politique de Confidentialité.</li>',
                ],
                'privacy' => [
                    '<p>Ce Site Web n\'utilise aucun cookie, ni propre ni tiers, à des fins analytiques, publicitaires ou de suivi. La navigation est complètement exempte de traceurs, garantissant que votre activité n\'est pas surveillée.</p>' => '<p>Ce Site Web peut utiliser Google Analytics 4, une fois l\'approbation légale obtenue et l\'activation en production réalisée, afin de mesurer de manière agrégée la navigation et les événements publics de conversion. La configuration analytique est limitée à la mesure décrite dans la Politique de Cookies et le script ne s\'active pas tant que cette activation n\'a pas été formellement approuvée.</p>',
                ],
                'cookies' => [
                    '<p><strong>Ce Site Web N\'UTILISE AUCUN type de cookie, ni propre ni tiers.</strong></p>' => '<p><strong>Ce Site Web peut utiliser des cookies analytiques Google Analytics 4 une fois l\'approbation légale finalisée et l\'activation en production explicitement activée.</strong></p>',
                    '<p>Nous n\'utilisons aucune technologie qui stocke des informations dans votre navigateur à des fins de suivi, d\'analyse, de publicité ou de fonctionnement. Votre visite est complètement anonyme et privée du point de vue de notre site web.</p>' => '<p>Lorsque l\'analytique est activée, Google Analytics 4 est utilisé uniquement pour mesurer les pages vues et les événements publics de navigation/conversion comme les clics sur la CTA principale, les billets, YouTube et les liens sociaux. En dehors de cette activation approuvée, le script analytique reste désactivé.</p>',
                ],
            ],
            'it' => [
                'terms' => [
                    '<li>Non si utilizzano cookie né tecnologie di tracciamento che possano raccogliere informazioni personali.</li>' => '<li>Google Analytics 4 può essere utilizzato, una volta ottenuta l\'approvazione legale e attivata la produzione, per misurare la navigazione e gli eventi pubblici di conversione descritti nella Politica sui Cookie e nella Privacy Policy.</li>',
                ],
                'privacy' => [
                    '<p>Questo Sito Web non utilizza cookie di alcun tipo, né propri né di terze parti, per finalità analitiche, pubblicitarie o di tracciamento. La navigazione è completamente libera da tracker, garantendo che la tua attività non venga monitorata.</p>' => '<p>Questo Sito Web può utilizzare Google Analytics 4, una volta ottenuta l\'approvazione legale e attivata la produzione, per misurare in forma aggregata la navigazione e gli eventi pubblici di conversione. La configurazione analitica è limitata alla misurazione descritta nella Politica sui Cookie e lo script non viene attivato finché tale attivazione non è stata formalmente approvata.</p>',
                ],
                'cookies' => [
                    '<p><strong>Questo Sito Web NON UTILIZZA alcun tipo di cookie, né proprio né di terze parti.</strong></p>' => '<p><strong>Questo Sito Web può utilizzare cookie analitici di Google Analytics 4 una volta completata l\'approvazione legale e abilitata esplicitamente l\'attivazione in produzione.</strong></p>',
                    '<p>Non utilizziamo alcuna tecnologia che memorizzi informazioni nel vostro browser per finalità di tracciamento, analisi, pubblicità o funzionamento. La vostra visita è completamente anonima e privata dal punto di vista del nostro sito web.</p>' => '<p>Quando l\'analitica è attiva, Google Analytics 4 viene utilizzato solo per misurare page view ed eventi pubblici di navigazione/conversione come clic sulla CTA principale, biglietti, YouTube e link social. Al di fuori di tale attivazione approvata, lo script analitico resta disattivato.</p>',
                ],
            ],
            'de' => [
                'terms' => [
                    '<li>Es werden keine Cookies oder Tracking-Technologien verwendet, die personenbezogene Informationen sammeln könnten.</li>' => '<li>Google Analytics 4 kann, nachdem eine rechtliche Freigabe erteilt und die Aktivierung in Produktion vorgenommen wurde, zur Messung der Navigation und der in der Cookie-Richtlinie sowie Datenschutzerklärung beschriebenen öffentlichen Conversion-Ereignisse verwendet werden.</li>',
                ],
                'privacy' => [
                    '<p>Diese Website verwendet keinerlei Cookies, weder eigene noch von Dritten, für Analyse-, Werbe- oder Trackingzwecke. Das Surfen ist vollständig frei von Trackern, sodass Ihre Aktivität nicht überwacht wird.</p>' => '<p>Diese Website kann Google Analytics 4 verwenden, nachdem eine rechtliche Freigabe erteilt und die Aktivierung in Produktion vorgenommen wurde, um Navigation und öffentliche Conversion-Ereignisse in aggregierter Form zu messen. Die Analysekonfiguration ist auf die in der Cookie-Richtlinie beschriebene Messung begrenzt, und das Skript wird erst nach formaler Freigabe aktiviert.</p>',
                ],
                'cookies' => [
                    '<p><strong>Diese Website KEINE Art von Cookie verwendet, weder eigene noch von Dritten.</strong></p>' => '<p><strong>Diese Website kann analytische Google-Analytics-4-Cookies verwenden, sobald die rechtliche Freigabe abgeschlossen und die Aktivierung in Produktion ausdrücklich eingeschaltet wurde.</strong></p>',
                    '<p>Wir verwenden keine Technologie, die Informationen in Ihrem Browser für Tracking-, Analyse-, Werbe- oder Betriebszwecke speichert. Ihr Besuch ist aus Sicht unserer Website völlig anonym und privat.</p>' => '<p>Wenn die Analytik aktiviert ist, wird Google Analytics 4 nur verwendet, um Seitenaufrufe sowie öffentliche Navigations-/Conversion-Ereignisse wie Klicks auf die Haupt-CTA, Tickets, YouTube und Social-Links zu messen. Außerhalb dieser freigegebenen Aktivierung bleibt das Analytik-Skript deaktiviert.</p>',
                ],
            ],
            default => [
                'terms' => [
                    '<li>No se utilizan cookies ni tecnologías de seguimiento que puedan recopilar información personal.</li>' => '<li>Puede utilizarse Google Analytics 4, una vez exista aprobación legal y activación expresa en producción, para medir la navegación y los eventos públicos de conversión descritos en la Política de Cookies y en la Política de Privacidad.</li>',
                ],
                'privacy' => [
                    '<p>Este Sitio Web no utiliza cookies de ningún tipo, ni propias ni de terceros, para finalidades analíticas, publicitarias o de seguimiento. La navegación es completamente libre de rastreadores, garantizando que tu actividad no es monitoreada.</p>' => '<p>Este Sitio Web puede utilizar Google Analytics 4, una vez exista aprobación legal y activación expresa en producción, para medir de forma agregada la navegación y los eventos públicos de conversión. La configuración analítica queda limitada a la medición descrita en la Política de Cookies y el script no se activa hasta que esa activación haya sido aprobada formalmente.</p>',
                ],
                'cookies' => [
                    '<p><strong>Este Sitio Web NO UTILIZA ningún tipo de cookie, ni propia ni de terceros.</strong></p>' => '<p><strong>Este Sitio Web puede utilizar cookies analíticas de Google Analytics 4 una vez se complete la aprobación legal y se habilite explícitamente la activación en producción.</strong></p>',
                    '<p>No utilizamos ninguna tecnología que almacene información en tu navegador para finalidades de seguimiento, análisis, publicidad o funcionamiento. Tu visita es completamente anónima y privada desde el punto de vista de nuestro sitio web.</p>' => '<p>Cuando la analítica está activada, Google Analytics 4 se utiliza únicamente para medir páginas vistas y eventos públicos de navegación/conversión como clics en la CTA principal, tickets, YouTube y enlaces sociales. Fuera de esa activación aprobada, el script de analítica permanece desactivado.</p>',
                ],
            ],
        };
    }

    private function buildSeo(?Page $page, string $locale, string $baseUrl, string $fallbackTitle, Collection $socialLinks): array
    {
        $seo = null;

        if ($page instanceof Page) {
            $seo = SeoMeta::query()
                ->where('entity_type', Page::class)
                ->where('entity_id', $page->id)
                ->whereIn('locale', [$locale, self::DEFAULT_LOCALE])
                ->get()
                ->sortBy(fn (SeoMeta $row) => $row->locale === $locale ? 0 : 1)
                ->first();
        }

        $canonicalPath = $seo?->canonical_url ?: ($locale === self::DEFAULT_LOCALE ? '/' : '/'.$locale);
        $sameAs = $socialLinks
            ->flatten(1)
            ->pluck('url')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $alternates = collect(self::LOCALES)
            ->mapWithKeys(fn (string $supportedLocale) => [
                $supportedLocale => rtrim($baseUrl, '/').($supportedLocale === self::DEFAULT_LOCALE ? '/' : '/'.$supportedLocale),
            ])
            ->all();

        return [
            'title' => $seo?->meta_title ?? $fallbackTitle,
            'description' => $seo?->meta_description,
            'canonical' => rtrim($baseUrl, '/').$canonicalPath,
            'alternates' => $alternates,
            'xDefault' => $alternates[self::DEFAULT_LOCALE],
            'ogType' => data_get($seo?->open_graph, 'type', 'website'),
            'ogImage' => data_get($seo?->open_graph, 'image', rtrim($baseUrl, '/').'/favicon.ico'),
            'jsonLd' => $seo?->json_ld ?: [
                '@context' => 'https://schema.org',
                '@type' => 'MusicGroup',
                'name' => $fallbackTitle,
                'url' => rtrim($baseUrl, '/').$canonicalPath,
                'inLanguage' => $locale,
                'sameAs' => $sameAs,
            ],
        ];
    }

    private function pageTranslationContent(?Page $page, string $locale): array
    {
        if (! $page instanceof Page) {
            return [];
        }

        $translation = $this->translatedRecord($page->translations, $locale);

        return $translation?->content ?? [];
    }

    private function settingValue(Collection $settings, string $group, string $key, string $locale): array
    {
        /** @var Setting|null $setting */
        $setting = $settings->get("{$group}.{$key}");

        if (! $setting instanceof Setting) {
            return [];
        }

        if ($setting->is_translatable) {
            return $this->translatedRecord($setting->translations, $locale)?->value ?? [];
        }

        return is_array($setting->value) ? $setting->value : [];
    }

    private function blockTranslationContent(Collection $translations, string $locale): array
    {
        $record = $this->translatedRecord($translations, $locale);

        return $record?->content ?? [];
    }

    private function translatedRecord(Collection $translations, string $locale): ?Model
    {
        return $translations->firstWhere('locale', $locale)
            ?? $translations->firstWhere('locale', self::DEFAULT_LOCALE)
            ?? $translations->first();
    }
}

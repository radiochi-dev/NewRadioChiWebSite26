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
            ->whereIn('location', ['contact', 'footer'])
            ->orderBy('location')
            ->orderBy('position')
            ->get()
            ->groupBy('location');

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
            'termsPolicyCookies' => $this->buildLegalContent($legalDocuments, $settings, $locale),
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
            'socialLinks' => $this->buildSocialLinks($socialLinks->get('contact', collect())),
            'footerSocialLinks' => $this->buildSocialLinks($socialLinks->get('footer', collect())),
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

    private function buildLegalContent(Collection $legalDocuments, Collection $settings, string $locale): array
    {
        $buttons = $this->settingValue($settings, 'legal', 'buttons', $locale);

        return [
            'terms_button' => data_get($buttons, 'terms_button', 'Términos y Condiciones'),
            'privacy_button' => data_get($buttons, 'privacy_button', 'Política de Privacidad'),
            'cookies_button' => data_get($buttons, 'cookies_button', 'Política de Cookies'),
            'terms' => $this->buildLegalDocumentPayload($legalDocuments->get('terms'), $locale),
            'privacy' => $this->buildLegalDocumentPayload($legalDocuments->get('privacy'), $locale),
            'cookies' => $this->buildLegalDocumentPayload($legalDocuments->get('cookies'), $locale),
        ];
    }

    private function buildLegalDocumentPayload(?LegalDocument $document, string $locale): array
    {
        $translation = $document ? $this->translatedRecord($document->translations, $locale) : null;

        return [
            'title' => $translation?->title,
            'content' => $translation?->content,
        ];
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

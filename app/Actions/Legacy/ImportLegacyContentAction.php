<?php

namespace App\Actions\Legacy;

use App\Models\DownloadableFile;
use App\Models\Event;
use App\Models\LegalDocument;
use App\Models\MediaAsset;
use App\Models\MusicTrack;
use App\Models\Page;
use App\Models\PageBlock;
use App\Models\Partner;
use App\Models\SeoMeta;
use App\Models\Setting;
use App\Models\SocialLink;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class ImportLegacyContentAction
{
    public const LOCALES = ['es', 'en', 'ca', 'fr', 'it', 'de'];

    public const PLAN_FILES = [
        'home.json',
        'about.json',
        'music.json',
        'calendarEvents.json',
        'contact.json',
        'media.json',
        'introwebsite.json',
        'footer.json',
        'terms-policy-cookies.json',
        'videos.json',
        'events.json',
    ];

    public const USED_LOCALE_FILES = [
        'home.json',
        'about.json',
        'music.json',
        'header.json',
        'calendarEvents.json',
        'contact.json',
        'media.json',
        'introwebsite.json',
        'footer.json',
        'terms-policy-cookies.json',
        'seo.json',
    ];

    public const DATA_FILES = [
        'resources/js/legacy/data/calendarevents.json',
        'resources/js/legacy/data/events.json',
        'resources/js/legacy/data/videos.json',
    ];

    public const CODE_SOURCES = [
        'resources/js/legacy/content.js',
        'resources/js/Pages/Home.jsx',
    ];

    private ?array $localizedPayload = null;

    public function inventory(): array
    {
        $missingLocaleFiles = [];

        foreach (self::LOCALES as $locale) {
            foreach (self::USED_LOCALE_FILES as $file) {
                $relativePath = "resources/js/legacy/i18n/{$locale}/{$file}";

                if (! File::exists(base_path($relativePath))) {
                    $missingLocaleFiles[] = $relativePath;
                }
            }
        }

        $missingDataFiles = [];
        $emptyDataFiles = [];

        foreach (self::DATA_FILES as $relativePath) {
            $absolutePath = base_path($relativePath);

            if (! File::exists($absolutePath)) {
                $missingDataFiles[] = $relativePath;
                continue;
            }

            if (File::size($absolutePath) === 0) {
                $emptyDataFiles[] = $relativePath;
            }
        }

        $missingCodeSources = [];

        foreach (self::CODE_SOURCES as $relativePath) {
            if (! File::exists(base_path($relativePath))) {
                $missingCodeSources[] = $relativePath;
            }
        }

        return [
            'locales' => self::LOCALES,
            'plan_files' => self::PLAN_FILES,
            'used_locale_files' => self::USED_LOCALE_FILES,
            'data_files' => self::DATA_FILES,
            'code_sources' => self::CODE_SOURCES,
            'missing_locale_files' => $missingLocaleFiles,
            'missing_data_files' => $missingDataFiles,
            'missing_code_sources' => $missingCodeSources,
            'empty_data_files' => $emptyDataFiles,
        ];
    }

    public function import(): array
    {
        $inventory = $this->inventory();

        if ($inventory['missing_locale_files'] !== [] || $inventory['missing_data_files'] !== [] || $inventory['missing_code_sources'] !== []) {
            throw new RuntimeException('Legacy inventory is incomplete and cannot be imported safely.');
        }

        return DB::transaction(function () use ($inventory): array {
            $localized = $this->localizedPayload();
            $calendarData = $this->readJsonFile('resources/js/legacy/data/calendarevents.json');
            $legacyEventLinks = $this->readJsonFile('resources/js/legacy/data/events.json');
            $pages = $this->importPages($localized, $calendarData);

            $summary = [
                'inventory' => $inventory,
                'pages' => count($pages),
                'page_translations' => collect($pages)->sum(fn (Page $page) => $page->translations()->count()),
                'page_blocks' => $this->importPageBlocks($pages, $localized),
                'music_tracks' => $this->importMusicTracks($localized),
                'events' => $this->importEvents($calendarData),
                'media_assets' => $this->importMediaAssets(),
                'partners' => $this->importPartners(),
                'social_links' => $this->importSocialLinks(),
                'legal_documents' => $this->importLegalDocuments($localized),
                'settings' => $this->importSettings($localized, $legacyEventLinks, $inventory),
                'seo_meta' => $this->importSeoMeta($pages['home'], $localized),
                'downloadable_files' => DownloadableFile::query()->count(),
            ];

            $summary['page_block_translations'] = PageBlock::query()->withCount('translations')->get()->sum('translations_count');
            $summary['music_track_translations'] = MusicTrack::query()->withCount('translations')->get()->sum('translations_count');
            $summary['legal_document_translations'] = LegalDocument::query()->withCount('translations')->get()->sum('translations_count');
            $summary['settings_translations'] = Setting::query()->withCount('translations')->get()->sum('translations_count');

            return $summary;
        });
    }

    private function importPages(array $localized, array $calendarData): array
    {
        $pages = [];

        foreach (['home', 'about', 'music', 'calendar', 'media', 'contact'] as $slug) {
            $page = Page::query()->updateOrCreate(
                ['slug' => $slug],
                ['template' => $slug, 'is_published' => true, 'published_at' => now()],
            );

            foreach (self::LOCALES as $locale) {
                $page->translations()->updateOrCreate(
                    ['locale' => $locale],
                    [
                        'title' => $this->pageTitleFor($slug, $locale, $localized, $calendarData),
                        'meta_title' => $localized[$locale]['home']['name'] ?? 'RadioChi',
                        'meta_description' => $localized[$locale]['seo']['seo']['description'] ?? null,
                        'content' => match ($slug) {
                            'home' => $localized[$locale]['home'],
                            'about' => $localized[$locale]['about'],
                            'music' => $localized[$locale]['music'],
                            'calendar' => $calendarData['translations'][$locale] ?? [],
                            'media' => $localized[$locale]['media'],
                            'contact' => $localized[$locale]['contact'],
                        },
                    ],
                );
            }

            $pages[$slug] = $page->fresh(['translations']);
        }

        return $pages;
    }

    private function importPageBlocks(array $pages, array $localized): int
    {
        foreach (($localized['es']['home']['slides'] ?? []) as $index => $slide) {
            $position = $index + 1;
            $block = $pages['home']->blocks()->updateOrCreate(
                ['key' => sprintf('hero-slide-%02d', $position)],
                [
                    'type' => 'hero_slide',
                    'position' => $position,
                    'is_active' => true,
                    'settings' => [
                        'logo' => $slide['logo'] ?? null,
                        'logoPosition' => $slide['logoPosition'] ?? null,
                        'personImage' => $slide['personImage'] ?? null,
                        'elipseImage' => $slide['elipseImage'] ?? null,
                    ],
                ],
            );

            foreach (self::LOCALES as $locale) {
                $localizedSlide = $localized[$locale]['home']['slides'][$index] ?? [];
                $block->translations()->updateOrCreate(
                    ['locale' => $locale],
                    ['content' => [
                        'title' => $localizedSlide['title'] ?? null,
                        'subtitle' => $localizedSlide['subtitle'] ?? null,
                        'description' => $localizedSlide['description'] ?? null,
                        'buttonText' => $localizedSlide['buttonText'] ?? null,
                        'link' => $localizedSlide['link'] ?? null,
                        'event' => $localizedSlide['event'] ?? null,
                    ]],
                );
            }
        }

        foreach (($localized['es']['about']['scrollytelling']['steps'] ?? []) as $index => $step) {
            $position = $index + 1;
            $block = $pages['about']->blocks()->updateOrCreate(
                ['key' => sprintf('about-step-%02d', $position)],
                [
                    'type' => 'about_step',
                    'position' => $position,
                    'is_active' => true,
                    'settings' => [
                        'image' => $step['image'] ?? null,
                        'image2' => $step['image2'] ?? null,
                    ],
                ],
            );

            foreach (self::LOCALES as $locale) {
                $localizedStep = $localized[$locale]['about']['scrollytelling']['steps'][$index] ?? [];
                $block->translations()->updateOrCreate(
                    ['locale' => $locale],
                    ['content' => [
                        'title' => $localizedStep['title'] ?? null,
                        'subtitle' => $localizedStep['subtitle'] ?? null,
                        'content' => $localizedStep['content'] ?? null,
                        'chartTitle' => $localizedStep['chartTitle'] ?? null,
                        'chartSubtitle' => $localizedStep['chartSubtitle'] ?? null,
                    ]],
                );
            }
        }

        foreach (LegacyImportStaticData::marqueeRows() as $index => $words) {
            $position = $index + 1;
            $block = $pages['contact']->blocks()->updateOrCreate(
                ['key' => sprintf('contact-marquee-%02d', $position)],
                [
                    'type' => 'contact_marquee',
                    'position' => $position,
                    'is_active' => true,
                    'settings' => ['direction' => $position === 2 ? 'right-to-left' : 'left-to-right'],
                ],
            );

            foreach (self::LOCALES as $locale) {
                $block->translations()->updateOrCreate(
                    ['locale' => $locale],
                    ['content' => ['words' => $words]],
                );
            }
        }

        return PageBlock::query()->count();
    }

    private function importMusicTracks(array $localized): int
    {
        foreach (($localized['es']['music']['tracks'] ?? []) as $index => $track) {
            $position = $index + 1;
            $slug = sprintf('legacy-track-%02d-%s', $position, Str::slug((string) ($track['title'] ?? 'track')));
            $musicTrack = MusicTrack::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'platform' => 'soundcloud',
                    'label_image_path' => $track['label-img'] ?? null,
                    'cover_image_path' => $track['image'] ?? null,
                    'stream_url' => $track['soundcloudUrl'] ?? null,
                    'external_url' => $track['soundcloudUrl'] ?? null,
                    'genre' => $track['genre'] ?? null,
                    'year' => (int) ($track['year'] ?? 0) ?: null,
                    'position' => $position,
                    'is_featured' => $position === 1,
                    'is_published' => true,
                    'published_at' => now(),
                    'settings' => ['legacy_id' => $track['id'] ?? $position],
                ],
            );

            foreach (self::LOCALES as $locale) {
                $localizedTrack = $localized[$locale]['music']['tracks'][$index] ?? [];
                $musicTrack->translations()->updateOrCreate(
                    ['locale' => $locale],
                    [
                        'artist_name' => $localizedTrack['title'] ?? null,
                        'title' => $localizedTrack['subtitle'] ?? ($localizedTrack['title'] ?? 'Untitled'),
                        'hero_title' => $localizedTrack['heroTitle'] ?? null,
                        'subtitle' => $localizedTrack['subtitle'] ?? null,
                        'description' => $localizedTrack['description'] ?? null,
                        'cta_primary_label' => 'SoundCloud',
                        'cta_secondary_label' => 'Follow',
                    ],
                );
            }
        }

        return MusicTrack::query()->count();
    }

    private function importEvents(array $calendarData): int
    {
        foreach (($calendarData['events'] ?? []) as $index => $event) {
            $position = $index + 1;

            Event::query()->updateOrCreate(
                ['slug' => Str::slug((string) ($event['title'] ?? "event-{$position}"))],
                [
                    'title' => $event['title'] ?? "Event {$position}",
                    'excerpt' => trim(($event['location'] ?? '').' '.($event['country'] ?? '')) ?: null,
                    'event_starts_at' => $this->parseLegacyDate($event['dateStart'] ?? null),
                    'event_ends_at' => $this->parseLegacyDate($event['dateEnd'] ?? null),
                    'location' => trim((string) ($event['location'] ?? '')) ?: null,
                    'country' => trim((string) ($event['country'] ?? '')) ?: null,
                    'poster_path' => trim((string) ($event['logo'] ?? '')) ?: null,
                    'external_url' => $event['linkEvent'] ?? null,
                    'is_featured' => $position === 1,
                    'is_published' => true,
                    'published_at' => now(),
                ],
            );
        }

        return Event::query()->count();
    }

    private function importMediaAssets(): int
    {
        foreach (LegacyImportStaticData::mediaPhotos() as $index => $photo) {
            MediaAsset::query()->updateOrCreate(
                ['disk' => 'public', 'path' => $photo['src']],
                [
                    'filename' => pathinfo($photo['src'], PATHINFO_BASENAME),
                    'mime_type' => $this->mimeTypeFromPath($photo['src']),
                    'alt_text' => $photo['alt'],
                    'metadata' => [
                        'kind' => 'photo',
                        'collection' => 'legacy-media',
                        'position' => $index + 1,
                        'caption' => $photo['caption'],
                        'date' => $photo['date'],
                        'source' => 'resources/js/legacy/content.js',
                    ],
                ],
            );
        }

        foreach (LegacyImportStaticData::mediaVideos() as $index => $video) {
            MediaAsset::query()->updateOrCreate(
                ['disk' => 'external', 'path' => 'youtube:'.$video['id']],
                [
                    'filename' => $video['id'],
                    'mime_type' => 'video/youtube',
                    'alt_text' => $video['title'],
                    'metadata' => [
                        'kind' => 'video',
                        'collection' => 'legacy-media',
                        'position' => $index + 1,
                        'youtube_id' => $video['id'],
                        'thumbnail' => $video['thumbnail'],
                        'title' => $video['title'],
                        'date' => $video['date'],
                        'duration' => $video['duration'],
                        'source' => 'resources/js/legacy/content.js',
                    ],
                ],
            );
        }

        return MediaAsset::query()->count();
    }

    private function importPartners(): int
    {
        foreach (LegacyImportStaticData::partners() as $index => $partner) {
            Partner::query()->updateOrCreate(
                ['slug' => $partner['slug']],
                [
                    'name' => $partner['name'],
                    'partner_type' => 'sponsor',
                    'website_url' => $partner['url'],
                    'logo_path' => $partner['logo_path'],
                    'position' => $index + 1,
                    'is_active' => true,
                    'settings' => ['source' => 'resources/js/Pages/Home.jsx'],
                ],
            );
        }

        return Partner::query()->count();
    }

    private function importSocialLinks(): int
    {
        foreach (LegacyImportStaticData::socialLinks() as $index => $socialLink) {
            SocialLink::query()->updateOrCreate(
                ['platform' => $socialLink['platform'], 'location' => $socialLink['location']],
                [
                    'label' => $socialLink['label'],
                    'url' => $socialLink['url'],
                    'icon_key' => $socialLink['icon_key'],
                    'position' => $index + 1,
                    'is_active' => true,
                    'settings' => ['source' => 'resources/js/Pages/Home.jsx'],
                ],
            );
        }

        return SocialLink::query()->count();
    }

    private function importLegalDocuments(array $localized): int
    {
        foreach (['terms', 'privacy', 'cookies'] as $position => $documentType) {
            $document = LegalDocument::query()->updateOrCreate(
                ['slug' => $documentType],
                [
                    'document_type' => $documentType,
                    'version' => '2025',
                    'position' => $position + 1,
                    'is_published' => true,
                    'published_at' => now(),
                    'settings' => ['source' => 'resources/js/legacy/i18n/*/terms-policy-cookies.json'],
                ],
            );

            foreach (self::LOCALES as $locale) {
                $payload = $localized[$locale]['terms-policy-cookies'][$documentType] ?? [];
                $document->translations()->updateOrCreate(
                    ['locale' => $locale],
                    [
                        'title' => $payload['title'] ?? Str::headline($documentType),
                        'summary' => null,
                        'content' => $payload['content'] ?? '',
                        'cta_label' => $localized[$locale]['terms-policy-cookies'][$documentType.'_button'] ?? null,
                    ],
                );
            }
        }

        return LegalDocument::query()->count();
    }

    private function importSettings(array $localized, array $legacyEventLinks, array $inventory): int
    {
        $this->upsertSetting('localization', 'active_locales', 'json', ['locales' => self::LOCALES], false, true, 1);
        $this->upsertSetting('localization', 'default_locale', 'string', ['value' => 'es'], false, true, 2);
        $this->upsertTranslatedSetting('header', 'open_menu', 1, collect(self::LOCALES)->mapWithKeys(fn (string $locale) => [$locale => ['label' => $localized[$locale]['header']['openMenu'] ?? null]])->all());
        $this->upsertTranslatedSetting('header', 'menu', 2, collect(self::LOCALES)->mapWithKeys(fn (string $locale) => [$locale => ['items' => $localized[$locale]['header']['menu'] ?? []]])->all());
        $this->upsertTranslatedSetting('intro', 'welcome', 1, collect(self::LOCALES)->mapWithKeys(fn (string $locale) => [$locale => ['label' => $localized[$locale]['introwebsite']['welcome'] ?? null]])->all());
        $this->upsertTranslatedSetting('footer', 'credits', 1, collect(self::LOCALES)->mapWithKeys(fn (string $locale) => [$locale => ['copyright' => $localized[$locale]['footer']['copyright'] ?? null, 'rights' => $localized[$locale]['footer']['rights'] ?? null]])->all());
        $this->upsertTranslatedSetting('legal', 'buttons', 1, collect(self::LOCALES)->mapWithKeys(fn (string $locale) => [$locale => ['terms_button' => $localized[$locale]['terms-policy-cookies']['terms_button'] ?? null, 'privacy_button' => $localized[$locale]['terms-policy-cookies']['privacy_button'] ?? null, 'cookies_button' => $localized[$locale]['terms-policy-cookies']['cookies_button'] ?? null]])->all());
        $this->upsertSetting('media', 'youtube_channel_url', 'string', ['value' => 'https://www.youtube.com/channel/TUCANALAQUI'], false, true, 1);
        $this->upsertTranslatedSetting('contact', 'marquee_rows', 1, collect(self::LOCALES)->mapWithKeys(fn (string $locale) => [$locale => ['rows' => LegacyImportStaticData::marqueeRows()]])->all());
        $this->upsertSetting('legacy', 'events_links', 'json', $legacyEventLinks, false, false, 1);
        $this->upsertSetting('legacy', 'inventory', 'json', ['plan_files' => $inventory['plan_files'], 'used_locale_files' => $inventory['used_locale_files'], 'data_files' => $inventory['data_files'], 'code_sources' => $inventory['code_sources'], 'empty_data_files' => $inventory['empty_data_files']], false, false, 2);

        return Setting::query()->count();
    }

    private function importSeoMeta(Page $homePage, array $localized): int
    {
        $socialLinks = array_values(array_unique(array_column(LegacyImportStaticData::socialLinks(), 'url')));

        foreach (self::LOCALES as $locale) {
            $seo = $localized[$locale]['seo']['seo'] ?? [];
            $home = $localized[$locale]['home'] ?? [];

            SeoMeta::query()->updateOrCreate(
                ['entity_type' => Page::class, 'entity_id' => $homePage->id, 'locale' => $locale],
                [
                    'meta_title' => $home['name'] ?? 'RadioChi',
                    'meta_description' => $seo['description'] ?? null,
                    'canonical_url' => $locale === 'es' ? '/' : '/'.$locale,
                    'open_graph' => ['type' => 'website', 'site_name' => $home['name'] ?? 'RadioChi', 'locale' => $locale],
                    'twitter_card' => ['card' => 'summary', 'keywords' => $seo['keywords'] ?? null],
                    'json_ld' => ['@context' => 'https://schema.org', '@type' => 'MusicGroup', 'name' => $home['name'] ?? 'RadioChi', 'inLanguage' => $locale, 'sameAs' => $socialLinks],
                ],
            );
        }

        return SeoMeta::query()->count();
    }

    private function upsertSetting(string $group, string $key, string $type, array $value, bool $isTranslatable, bool $isPublic, int $position): Setting
    {
        return Setting::query()->updateOrCreate(
            ['group' => $group, 'key' => $key],
            ['type' => $type, 'value' => $value, 'is_translatable' => $isTranslatable, 'is_public' => $isPublic, 'position' => $position, 'settings' => ['source' => 'legacy-import']],
        );
    }

    private function upsertTranslatedSetting(string $group, string $key, int $position, array $translations): Setting
    {
        $setting = $this->upsertSetting($group, $key, 'json', ['default' => $translations['es'] ?? []], true, true, $position);

        foreach ($translations as $locale => $value) {
            $setting->translations()->updateOrCreate(['locale' => $locale], ['value' => $value]);
        }

        return $setting;
    }

    private function localizedPayload(): array
    {
        if ($this->localizedPayload !== null) {
            return $this->localizedPayload;
        }

        foreach (self::LOCALES as $locale) {
            foreach (self::USED_LOCALE_FILES as $file) {
                $key = str_replace('.json', '', $file);
                $this->localizedPayload[$locale][$key] = $this->readJsonFile("resources/js/legacy/i18n/{$locale}/{$file}");
            }
        }

        return $this->localizedPayload;
    }

    private function readJsonFile(string $relativePath): array
    {
        $absolutePath = base_path($relativePath);

        if (! File::exists($absolutePath)) {
            throw new RuntimeException("Missing legacy file: {$relativePath}");
        }

        $raw = File::get($absolutePath);

        if (trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        if (! is_array($decoded)) {
            throw new RuntimeException("Invalid JSON payload: {$relativePath}");
        }

        return $decoded;
    }

    private function pageTitleFor(string $slug, string $locale, array $localized, array $calendarData): string
    {
        return match ($slug) {
            'home' => $localized[$locale]['home']['name'] ?? 'RadioChi',
            'about' => $localized[$locale]['about']['scrollytelling']['steps'][0]['title'] ?? ($localized[$locale]['header']['menu']['about'] ?? 'About'),
            'music' => $localized[$locale]['music']['title'] ?? 'Music',
            'calendar' => $calendarData['translations'][$locale]['title'] ?? 'Calendar',
            'media' => $localized[$locale]['media']['title'] ?? 'Media',
            'contact' => $localized[$locale]['contact']['title'] ?? 'Contact',
            default => Str::headline($slug),
        };
    }

    private function parseLegacyDate(?string $value): ?Carbon
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        foreach (['d-m-y', 'd-m-Y', 'Y-m-j', 'Y-m-d'] as $format) {
            try {
                return Carbon::createFromFormat($format, trim($value))->startOfDay();
            } catch (\Throwable) {
            }
        }

        return null;
    }

    private function mimeTypeFromPath(string $path): ?string
    {
        return match (strtolower((string) pathinfo($path, PATHINFO_EXTENSION))) {
            'webp' => 'image/webp',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'svg' => 'image/svg+xml',
            default => null,
        };
    }
}

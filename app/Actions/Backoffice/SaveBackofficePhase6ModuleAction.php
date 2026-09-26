<?php

namespace App\Actions\Backoffice;

use App\Actions\Newsletter\PrepareNewsletterSubscriberData;
use App\Models\DownloadableFile;
use App\Models\Event;
use App\Models\LegalDocument;
use App\Models\LegalDocumentTranslation;
use App\Models\MediaAsset;
use App\Models\MusicTrack;
use App\Models\MusicTrackTranslation;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use App\Models\Page;
use App\Models\PageBlock;
use App\Models\PageBlockTranslation;
use App\Models\PageTranslation;
use App\Models\Partner;
use App\Models\RedirectRule;
use App\Models\SeoMeta;
use App\Models\Setting;
use App\Models\SettingTranslation;
use App\Models\SocialLink;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class SaveBackofficePhase6ModuleAction
{
    public function __construct(
        private readonly PrepareNewsletterSubscriberData $prepareNewsletterSubscriberData,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(string $module, array $data, ?string $record = null): Model
    {
        return match ($module) {
            'events' => $this->saveEvent($data, $record),
            'legal-documents' => $this->saveLegalDocument($data, $record),
            'music-tracks' => $this->saveMusicTrack($data, $record),
            'media-assets' => $this->saveMediaAsset($data, $record),
            'newsletter-campaigns' => $this->saveNewsletterCampaign($data, $record),
            'newsletter-subscribers' => $this->saveNewsletterSubscriber($data, $record),
            'pages' => $this->savePage($data, $record),
            'page-blocks' => $this->savePageBlock($data, $record),
            'partners' => $this->savePartner($data, $record),
            'redirect-rules' => $this->saveRedirectRule($data, $record),
            'social-links' => $this->saveSocialLink($data, $record),
            'settings' => $this->saveSetting($data, $record),
            'downloadable-files' => $this->saveDownloadableFile($data, $record),
            'seo-metas' => $this->saveSeoMeta($data, $record),
            default => abort(404),
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function saveMusicTrackTranslation(MusicTrack $track, array $data, ?MusicTrackTranslation $translation = null): MusicTrackTranslation
    {
        if ($translation instanceof MusicTrackTranslation) {
            $translation->update(Arr::except($data, ['locale']));

            /** @var MusicTrackTranslation $translation */
            return $translation->fresh();
        }

        /** @var MusicTrackTranslation $translation */
        $translation = $track->translations()->updateOrCreate(
            ['locale' => $data['locale']],
            Arr::except($data, ['locale']),
        );

        return $translation;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function savePageTranslation(Page $page, array $data, ?PageTranslation $translation = null): PageTranslation
    {
        if ($translation instanceof PageTranslation) {
            $translation->update(Arr::except($data, ['locale']));

            /** @var PageTranslation $translation */
            return $translation->fresh();
        }

        /** @var PageTranslation $translation */
        $translation = $page->translations()->updateOrCreate(
            ['locale' => $data['locale']],
            Arr::except($data, ['locale']),
        );

        return $translation;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function savePageBlockTranslation(PageBlock $block, array $data, ?PageBlockTranslation $translation = null): PageBlockTranslation
    {
        if (array_key_exists('settings', $data) && is_array($data['settings'])) {
            $block->update([
                'settings' => $data['settings'],
            ]);
        }

        if ($translation instanceof PageBlockTranslation) {
            $translation->update(Arr::except($data, ['locale', 'settings']));

            /** @var PageBlockTranslation $translation */
            return $translation->fresh();
        }

        /** @var PageBlockTranslation $translation */
        $translation = $block->translations()->updateOrCreate(
            ['locale' => $data['locale']],
            Arr::except($data, ['locale', 'settings']),
        );

        return $translation;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function saveSettingTranslation(Setting $setting, array $data, ?SettingTranslation $translation = null): SettingTranslation
    {
        if ($translation instanceof SettingTranslation) {
            $translation->update(Arr::except($data, ['locale']));

            /** @var SettingTranslation $translation */
            return $translation->fresh();
        }

        /** @var SettingTranslation $translation */
        $translation = $setting->translations()->updateOrCreate(
            ['locale' => $data['locale']],
            Arr::except($data, ['locale']),
        );

        return $translation;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function saveLegalDocumentTranslation(LegalDocument $document, array $data, ?LegalDocumentTranslation $translation = null): LegalDocumentTranslation
    {
        if ($translation instanceof LegalDocumentTranslation) {
            $translation->update(Arr::except($data, ['locale']));

            /** @var LegalDocumentTranslation $translation */
            return $translation->fresh();
        }

        /** @var LegalDocumentTranslation $translation */
        $translation = $document->translations()->updateOrCreate(
            ['locale' => $data['locale']],
            Arr::except($data, ['locale']),
        );

        return $translation;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function saveEvent(array $data, ?string $record): Event
    {
        $event = $record ? Event::query()->findOrFail($record) : new Event();
        $event->fill($data);
        $event->save();

        return $event->fresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function saveLegalDocument(array $data, ?string $record): LegalDocument
    {
        $document = $record ? LegalDocument::query()->findOrFail($record) : new LegalDocument();
        $document->fill(Arr::only($data, [
            'slug',
            'document_type',
            'version',
            'position',
            'is_published',
            'published_at',
            'settings',
        ]));
        $document->save();

        $translationPayload = Arr::only($data, ['locale', 'title', 'summary', 'content', 'cta_label']);

        if (($translationPayload['locale'] ?? null) && ($translationPayload['title'] ?? null) && ($translationPayload['content'] ?? null)) {
            $this->saveLegalDocumentTranslation($document, $translationPayload);
        }

        return $document->fresh(['translations']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function saveMusicTrack(array $data, ?string $record): MusicTrack
    {
        $track = $record ? MusicTrack::query()->findOrFail($record) : new MusicTrack();
        $track->fill(Arr::only($data, [
            'slug',
            'platform',
            'label_image_path',
            'cover_image_path',
            'stream_url',
            'external_url',
            'genre',
            'year',
            'position',
            'is_featured',
            'is_published',
            'published_at',
            'settings',
        ]));
        $track->save();

        $translationPayload = Arr::only($data, [
            'locale',
            'artist_name',
            'title',
            'hero_title',
            'subtitle',
            'description',
            'cta_primary_label',
            'cta_secondary_label',
        ]);

        if (($translationPayload['locale'] ?? null) && ($translationPayload['title'] ?? null)) {
            $this->saveMusicTrackTranslation($track, $translationPayload);
        }

        return $track->fresh(['translations']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function saveMediaAsset(array $data, ?string $record): MediaAsset
    {
        $asset = $record ? MediaAsset::query()->findOrFail($record) : new MediaAsset();
        $asset->fill($data);
        $asset->save();

        return $asset->fresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function saveNewsletterCampaign(array $data, ?string $record): NewsletterCampaign
    {
        $campaign = $record ? NewsletterCampaign::query()->findOrFail($record) : new NewsletterCampaign();

        if (! $record) {
            $data['status'] = 'draft';
        }

        $campaign->fill($data);
        $campaign->save();

        return $campaign->fresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function saveNewsletterSubscriber(array $data, ?string $record): NewsletterSubscriber
    {
        $subscriber = $record ? NewsletterSubscriber::query()->findOrFail($record) : null;
        $payload = $this->prepareNewsletterSubscriberData->execute($data, $subscriber);

        $subscriber ??= new NewsletterSubscriber();
        $subscriber->fill($payload);
        $subscriber->save();

        return $subscriber->fresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function savePage(array $data, ?string $record): Page
    {
        $page = $record ? Page::query()->findOrFail($record) : new Page();
        $page->fill($data);
        $page->save();

        return $page->fresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function savePageBlock(array $data, ?string $record): PageBlock
    {
        $block = $record ? PageBlock::query()->findOrFail($record) : new PageBlock();
        $block->fill($data);
        $block->save();

        return $block->fresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function saveSetting(array $data, ?string $record): Setting
    {
        $setting = $record ? Setting::query()->findOrFail($record) : new Setting();
        $payload = Arr::only($data, [
            'group',
            'key',
            'type',
            'is_translatable',
            'is_public',
            'position',
            'settings',
        ]);

        $payload['value'] = ($data['is_translatable'] ?? false)
            ? null
            : ($data['value'] ?? null);

        $setting->fill($payload);
        $setting->save();

        if (($data['is_translatable'] ?? false) && isset($data['locale'])) {
            $this->saveSettingTranslation($setting, [
                'locale' => $data['locale'],
                'value' => is_array($data['value'] ?? null) ? $data['value'] : [],
            ]);
        }

        return $setting->fresh(['translations']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function savePartner(array $data, ?string $record): Partner
    {
        $partner = $record ? Partner::query()->findOrFail($record) : new Partner();
        $partner->fill($data);
        $partner->save();

        return $partner->fresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function saveRedirectRule(array $data, ?string $record): RedirectRule
    {
        $rule = $record ? RedirectRule::query()->findOrFail($record) : new RedirectRule();
        $rule->fill($data);
        $rule->save();

        return $rule->fresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function saveSocialLink(array $data, ?string $record): SocialLink
    {
        $socialLink = $record ? SocialLink::query()->findOrFail($record) : new SocialLink();
        $data['platform'] = Str::lower(trim((string) ($data['platform'] ?? '')));
        $data['location'] = 'global';
        $socialLink->fill($data);
        $socialLink->save();

        return $socialLink->fresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function saveDownloadableFile(array $data, ?string $record): DownloadableFile
    {
        $file = $record ? DownloadableFile::query()->findOrFail($record) : new DownloadableFile();
        $file->fill($data);
        $file->save();

        return $file->fresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function saveSeoMeta(array $data, ?string $record): SeoMeta
    {
        if ($record) {
            $meta = SeoMeta::query()->findOrFail($record);
            $meta->update(Arr::except($data, ['entity_type', 'entity_id', 'locale']));

            return $meta->fresh();
        }

        /** @var SeoMeta $meta */
        $meta = SeoMeta::query()->updateOrCreate(
            [
                'entity_type' => $data['entity_type'],
                'entity_id' => $data['entity_id'],
                'locale' => $data['locale'],
            ],
            Arr::except($data, ['entity_type', 'entity_id', 'locale']),
        );

        return $meta;
    }
}

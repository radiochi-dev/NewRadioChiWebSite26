<?php

namespace App\Http\Requests\Backoffice;

use App\Models\DownloadableFile;
use App\Models\Event;
use App\Models\LegalDocument;
use App\Models\MediaAsset;
use App\Models\MusicTrack;
use App\Models\PageBlock;
use App\Models\Partner;
use App\Models\SocialLink;
use App\Support\Backoffice\CrudModuleBlueprintFactory;
use App\Support\Backoffice\Phase6ModuleCatalog;
use App\Support\BackofficeLocales;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class BackofficePreviewDraftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageBackofficeContent() === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $module = (string) $this->route('module');

        if (! Phase6ModuleCatalog::supports($module)) {
            return CrudModuleBlueprintFactory::validationRules($module);
        }

        $record = (string) $this->route('record');

        return match ($module) {
            'events' => [
                'slug' => ['required', 'string', 'max:255', Rule::unique('events', 'slug')->ignore($record)],
                'title' => ['required', 'string', 'max:255'],
                'excerpt' => ['nullable', 'string'],
                'body' => ['nullable', 'string'],
                'event_starts_at' => ['nullable', 'date'],
                'event_ends_at' => ['nullable', 'date'],
                'location' => ['nullable', 'string', 'max:255'],
                'external_url' => ['nullable', 'url', 'max:255'],
                'is_featured' => ['nullable', 'boolean'],
                'is_published' => ['nullable', 'boolean'],
                'published_at' => ['nullable', 'date'],
            ],
            'legal-documents' => [
                'slug' => ['required', 'string', 'max:255', Rule::unique('legal_documents', 'slug')->ignore($record)],
                'document_type' => ['required', 'string', Rule::in(array_column(Phase6ModuleCatalog::legalDocumentTypeOptions(), 'value'))],
                'version' => ['nullable', 'string', 'max:120'],
                'position' => ['nullable', 'integer', 'min:0'],
                'is_published' => ['nullable', 'boolean'],
                'published_at' => ['nullable', 'date'],
                'settings' => ['nullable', 'array'],
            ],
            'music-tracks' => [
                'slug' => ['required', 'string', 'max:255', Rule::unique('music_tracks', 'slug')->ignore($record)],
                'platform' => ['required', 'string', Rule::in(array_column(Phase6ModuleCatalog::musicPlatformOptions(), 'value'))],
                'label_image_path' => ['nullable', 'string', 'max:255'],
                'cover_image_path' => ['nullable', 'string', 'max:255'],
                'stream_url' => ['nullable', 'url', 'max:65535'],
                'external_url' => ['nullable', 'url', 'max:65535'],
                'genre' => ['nullable', 'string', 'max:120'],
                'year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
                'position' => ['nullable', 'integer', 'min:0'],
                'is_featured' => ['nullable', 'boolean'],
                'is_published' => ['nullable', 'boolean'],
                'published_at' => ['nullable', 'date'],
                'settings' => ['nullable', 'array'],
            ],
            'media-assets' => [
                'disk' => ['required', 'string', 'max:60'],
                'path' => ['required', 'string', 'max:255'],
                'filename' => ['required', 'string', 'max:255'],
                'mime_type' => ['nullable', 'string', 'max:120'],
                'size' => ['nullable', 'integer', 'min:0'],
                'width' => ['nullable', 'integer', 'min:0'],
                'height' => ['nullable', 'integer', 'min:0'],
                'alt_text' => ['nullable', 'string', 'max:255'],
                'metadata' => ['nullable', 'array'],
            ],
            'newsletter-campaigns' => array_filter([
                'name' => ['required', 'string', 'max:255'],
                'subject' => ['required', 'string', 'max:255'],
                'html_body' => ['required', 'string'],
                'scheduled_at' => ['nullable', 'date'],
                'status' => $record !== '' ? ['required', 'string', Rule::in(array_column(Phase6ModuleCatalog::newsletterCampaignStatusOptions(), 'value'))] : null,
            ]),
            'newsletter-subscribers' => [
                'email' => ['required', 'email:rfc,dns', 'max:255', Rule::unique('newsletter_subscribers', 'email')->ignore($record)],
                'name' => ['nullable', 'string', 'max:255'],
                'is_active' => ['nullable', 'boolean'],
            ],
            'pages' => [
                'slug' => ['required', 'string', 'max:255', Rule::unique('pages', 'slug')->ignore($record)],
                'template' => ['required', 'string', 'max:120'],
                'is_published' => ['nullable', 'boolean'],
                'published_at' => ['nullable', 'date'],
            ],
            'page-blocks' => [
                'page_id' => ['required', 'integer', Rule::exists('pages', 'id')],
                'key' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('page_blocks', 'key')
                        ->where(fn ($query) => $query->where('page_id', $this->input('page_id')))
                        ->ignore($record),
                ],
                'type' => ['required', 'string', 'max:120'],
                'position' => ['nullable', 'integer', 'min:0'],
                'is_active' => ['nullable', 'boolean'],
                'settings' => ['nullable', 'array'],
            ],
            'settings' => [
                'group' => ['required', 'string', 'max:120'],
                'key' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('settings', 'key')
                        ->where(fn ($query) => $query->where('group', $this->input('group')))
                        ->ignore($record),
                ],
                'type' => ['required', 'string', Rule::in(['string', 'json', 'boolean', 'number', 'url', 'html'])],
                'value' => ['nullable', 'array'],
                'is_translatable' => ['nullable', 'boolean'],
                'is_public' => ['nullable', 'boolean'],
                'position' => ['nullable', 'integer', 'min:0'],
                'settings' => ['nullable', 'array'],
            ],
            'partners' => [
                'slug' => ['required', 'string', 'max:255', Rule::unique('partners', 'slug')->ignore($record)],
                'name' => ['required', 'string', 'max:255'],
                'partner_type' => ['required', 'string', Rule::in(array_column(Phase6ModuleCatalog::partnerTypeOptions(), 'value'))],
                'website_url' => ['nullable', 'url', 'max:65535'],
                'logo_path' => ['nullable', 'string', 'max:255'],
                'position' => ['nullable', 'integer', 'min:0'],
                'is_active' => ['nullable', 'boolean'],
                'settings' => ['nullable', 'array'],
            ],
            'redirect-rules' => [
                'source_path' => ['required', 'string', 'max:255', Rule::unique('redirect_rules', 'source_path')->ignore($record)],
                'destination_url' => ['required', 'string', 'max:65535'],
                'http_status' => ['required', 'integer', Rule::in(array_map('intval', array_column(Phase6ModuleCatalog::redirectStatusOptions(), 'value')))],
                'locale' => ['nullable', 'string', 'max:5', Rule::in(BackofficeLocales::values())],
                'is_active' => ['nullable', 'boolean'],
                'hit_count' => ['nullable', 'integer', 'min:0'],
                'notes' => ['nullable', 'string'],
            ],
            'social-links' => [
                'platform' => ['required', 'string', 'max:120'],
                'label' => ['nullable', 'string', 'max:255'],
                'url' => ['required', 'url', 'max:65535'],
                'icon_key' => ['nullable', 'string', 'max:120'],
                'location' => ['required', 'string', Rule::in(array_column(Phase6ModuleCatalog::socialLocationOptions(), 'value'))],
                'position' => ['nullable', 'integer', 'min:0'],
                'is_active' => ['nullable', 'boolean'],
                'settings' => ['nullable', 'array'],
            ],
            'downloadable-files' => [
                'slug' => ['required', 'string', 'max:255', Rule::unique('downloadable_files', 'slug')->ignore($record)],
                'display_name' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'disk' => ['required', 'string', 'max:60'],
                'file_path' => ['nullable', 'string', 'max:255'],
                'file_name' => ['nullable', 'string', 'max:255'],
                'mime_type' => ['nullable', 'string', 'max:120'],
                'size' => ['nullable', 'integer', 'min:0'],
                'external_url' => ['nullable', 'url', 'max:65535'],
                'collection' => ['nullable', 'string', 'max:120'],
                'attachable_type' => ['nullable', 'string', Rule::in(array_column(Phase6ModuleCatalog::downloadableAttachableOptions(), 'value'))],
                'attachable_id' => ['nullable', 'integer', 'min:1'],
                'position' => ['nullable', 'integer', 'min:0'],
                'is_active' => ['nullable', 'boolean'],
                'settings' => ['nullable', 'array'],
            ],
            'seo-metas' => array_filter([
                'entity_type' => $record === '' ? ['required', 'string', 'max:255'] : null,
                'entity_id' => $record === '' ? ['required', 'integer', 'min:1'] : null,
                'locale' => $record === '' ? ['required', 'string', 'max:5', Rule::in(BackofficeLocales::values())] : null,
                'meta_title' => ['nullable', 'string', 'max:255'],
                'meta_description' => ['nullable', 'string'],
                'canonical_url' => ['nullable', 'url', 'max:255'],
                'open_graph' => ['nullable', 'array'],
                'twitter_card' => ['nullable', 'array'],
                'json_ld' => ['nullable', 'array'],
            ]),
            default => [],
        };
    }

    protected function prepareForValidation(): void
    {
        $module = (string) $this->route('module');

        if (! Phase6ModuleCatalog::supports($module)) {
            return;
        }

        $merge = match ($module) {
            'events' => [
                'is_featured' => $this->boolean('is_featured'),
                'is_published' => $this->boolean('is_published'),
            ],
            'legal-documents' => [
                'position' => $this->filled('position') ? (int) $this->input('position') : 0,
                'is_published' => $this->boolean('is_published'),
                'settings' => $this->decodeJsonField('settings'),
            ],
            'music-tracks' => [
                'year' => $this->filled('year') ? (int) $this->input('year') : null,
                'position' => $this->filled('position') ? (int) $this->input('position') : 0,
                'is_featured' => $this->boolean('is_featured'),
                'is_published' => $this->boolean('is_published'),
                'settings' => $this->decodeJsonField('settings'),
            ],
            'media-assets' => [
                'size' => $this->filled('size') ? (int) $this->input('size') : null,
                'width' => $this->filled('width') ? (int) $this->input('width') : null,
                'height' => $this->filled('height') ? (int) $this->input('height') : null,
                'metadata' => $this->decodeJsonField('metadata'),
            ],
            'newsletter-campaigns' => [],
            'newsletter-subscribers' => [
                'is_active' => $this->boolean('is_active'),
            ],
            'pages' => [
                'is_published' => $this->boolean('is_published'),
            ],
            'page-blocks' => [
                'page_id' => $this->filled('page_id') ? (int) $this->input('page_id') : null,
                'position' => $this->filled('position') ? (int) $this->input('position') : 0,
                'is_active' => $this->boolean('is_active'),
                'settings' => $this->decodeJsonField('settings'),
            ],
            'settings' => [
                'position' => $this->filled('position') ? (int) $this->input('position') : 0,
                'is_translatable' => $this->boolean('is_translatable'),
                'is_public' => $this->boolean('is_public'),
                'value' => $this->decodeJsonField('value'),
                'settings' => $this->decodeJsonField('settings'),
            ],
            'partners' => [
                'position' => $this->filled('position') ? (int) $this->input('position') : 0,
                'is_active' => $this->boolean('is_active'),
                'settings' => $this->decodeJsonField('settings'),
            ],
            'redirect-rules' => [
                'http_status' => $this->filled('http_status') ? (int) $this->input('http_status') : 301,
                'hit_count' => $this->filled('hit_count') ? (int) $this->input('hit_count') : 0,
                'is_active' => $this->boolean('is_active'),
            ],
            'social-links' => [
                'position' => $this->filled('position') ? (int) $this->input('position') : 0,
                'is_active' => $this->boolean('is_active'),
                'settings' => $this->decodeJsonField('settings'),
            ],
            'downloadable-files' => [
                'size' => $this->filled('size') ? (int) $this->input('size') : null,
                'attachable_id' => $this->filled('attachable_id') ? (int) $this->input('attachable_id') : null,
                'position' => $this->filled('position') ? (int) $this->input('position') : 0,
                'is_active' => $this->boolean('is_active'),
                'settings' => $this->decodeJsonField('settings'),
            ],
            'seo-metas' => [
                'entity_id' => $this->filled('entity_id') ? (int) $this->input('entity_id') : null,
                'open_graph' => $this->decodeJsonField('open_graph'),
                'twitter_card' => $this->decodeJsonField('twitter_card'),
                'json_ld' => $this->decodeJsonField('json_ld'),
            ],
            default => [],
        };

        $this->merge($merge);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ((string) $this->route('module') !== 'downloadable-files') {
                return;
            }

            $type = $this->input('attachable_type');
            $id = $this->input('attachable_id');

            if (($type && ! $id) || (! $type && $id)) {
                $validator->errors()->add('attachable_id', 'Debe indicar tipo e ID relacionado a la vez.');

                return;
            }

            if (! $type || ! $id) {
                return;
            }

            $modelClass = match ($type) {
                PageBlock::class => PageBlock::class,
                MusicTrack::class => MusicTrack::class,
                LegalDocument::class => LegalDocument::class,
                Event::class => Event::class,
                default => null,
            };

            if (! $modelClass || ! $modelClass::query()->whereKey($id)->exists()) {
                $validator->errors()->add('attachable_id', 'El registro relacionado indicado no existe.');
            }
        });
    }

    private function decodeJsonField(string $field): ?array
    {
        $value = $this->input($field);

        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode((string) $value, true);

        return is_array($decoded) ? $decoded : null;
    }
}

<?php

namespace App\Actions\Backoffice;

use App\Models\DownloadableFile;
use App\Models\Event;
use App\Models\LegalDocument;
use App\Models\LegalDocumentTranslation;
use App\Models\MediaAsset;
use App\Models\MusicTrack;
use App\Models\MusicTrackTranslation;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterLog;
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
use App\Models\User;
use App\Support\Backoffice\BackofficePath;
use App\Support\Backoffice\Phase6ModuleCatalog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class BuildBackofficePhase6CrudPayloadAction
{
    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function index(User $user, string $slug, array $query = []): array
    {
        $module = $this->module($slug);
        $filters = $this->filtersFor($slug, $module['filters']);
        $normalized = $this->normalizeIndexQuery($module, $filters, $query);
        $paginator = $this->paginate($this->applyIndexQuery($slug, $normalized), $normalized);

        $payload = [
            'mode' => 'index',
            'module' => Arr::only($module, ['slug', 'title', 'singular', 'group', 'description']),
            'title' => $module['title'],
            'description' => $module['description'],
            'breadcrumbs' => [
                ['label' => 'Backoffice', 'href' => BackofficePath::active()],
                ['label' => $module['group'], 'href' => null],
                ['label' => $module['title'], 'href' => BackofficePath::active($module['slug'])],
            ],
            'actions' => [
                [
                    'label' => 'Nuevo '.$module['singular'],
                    'href' => BackofficePath::active($module['slug'].'/create'),
                    'variant' => 'primary',
                    'visible' => $this->canCreateFor($slug, $user),
                ],
            ],
            'summaryCards' => [
                ['label' => 'Registros', 'value' => (string) $paginator->total(), 'tone' => 'cyan'],
                ['label' => 'Filtros', 'value' => (string) count($filters), 'tone' => 'fuchsia'],
                ['label' => 'Lectura real', 'value' => 'DB', 'tone' => 'emerald'],
                ['label' => 'Fase activa', 'value' => $this->phaseLabel($slug), 'tone' => 'amber'],
            ],
            'filters' => $this->filterPayload($filters, $normalized),
            'table' => [
                'path' => BackofficePath::active($module['slug']),
                'query' => $normalized,
                'columns' => $module['columns'],
                'rows' => array_map(
                    fn (Model $record): array => $this->mapRow($slug, $record, $module['columns'], $user),
                    $paginator->items()
                ),
                'emptyState' => [
                    'title' => 'Sin registros todavia',
                    'description' => 'El modulo ya esta conectado a persistencia real. Puedes crear el primer registro desde esta misma superficie Inertia.',
                    'ctaLabel' => $this->canCreateFor($slug, $user) ? 'Crear '.$module['singular'] : null,
                    'ctaHref' => $this->canCreateFor($slug, $user) ? BackofficePath::active($module['slug'].'/create') : null,
                ],
                'bulkActions' => [],
                'sort' => [
                    'column' => $normalized['sort'],
                    'direction' => $normalized['direction'],
                ],
                'pagination' => [
                    'currentPage' => $paginator->currentPage(),
                    'perPage' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'totalPages' => $paginator->lastPage(),
                ],
            ],
            'capabilities' => [
                'canCreate' => $this->canCreateFor($slug, $user),
                'canEdit' => ! Phase6ModuleCatalog::isReadOnly($slug) && $user->canManageBackofficeContent(),
                'canDelete' => false,
                'canView' => $user->canViewBackofficeContent(),
            ],
            'testChecklist' => Phase6ModuleCatalog::testChecklist($slug),
        ];

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $oldInput
     * @return array<string, mixed>
     */
    public function form(User $user, string $slug, string $mode, ?string $record = null, array $oldInput = []): array
    {
        $module = $this->module($slug);
        $recordModel = $record ? $this->resolveRecord($slug, $record) : null;
        $readOnly = Phase6ModuleCatalog::isReadOnly($slug);
        $modeLabel = $readOnly && $mode === 'edit'
            ? 'Ver'
            : ($mode === 'edit' ? 'Editar' : 'Crear');
        $sections = $this->sectionsFor($slug, $mode);

        return [
            'mode' => $mode,
            'module' => Arr::only($module, ['slug', 'title', 'singular', 'group', 'description']),
            'recordLabel' => $recordModel?->getKey(),
            'title' => $modeLabel.' '.$module['singular'],
            'description' => $module['description'],
            'breadcrumbs' => [
                ['label' => 'Backoffice', 'href' => BackofficePath::active()],
                ['label' => $module['title'], 'href' => BackofficePath::active($module['slug'])],
                ['label' => $modeLabel.' '.$module['singular'], 'href' => null],
            ],
            'actions' => [
                [
                    'label' => 'Volver al indice',
                    'href' => BackofficePath::active($module['slug']),
                    'variant' => 'ghost',
                    'visible' => true,
                ],
            ],
            'summaryCards' => [
                ['label' => 'Modo', 'value' => strtoupper($mode), 'tone' => 'cyan'],
                ['label' => 'Persistencia', 'value' => 'REAL', 'tone' => 'emerald'],
                ['label' => 'Campos', 'value' => (string) collect($sections)->sum(fn (array $section): int => count($section['fields'])), 'tone' => 'fuchsia'],
                ['label' => 'Relaciones', 'value' => (string) count($this->relationManagers($slug, $recordModel)), 'tone' => 'amber'],
            ],
            'form' => [
                'action' => BackofficePath::active($module['slug'].'/draft'.($recordModel ? '/'.$recordModel->getKey() : '')),
                'method' => 'post',
                'submitLabel' => $readOnly ? 'Sin cambios' : ($mode === 'edit' ? 'Guardar cambios' : 'Crear registro'),
                'defaults' => $this->defaultsFor($slug, $sections, $recordModel, $oldInput),
                'sections' => $sections,
                'relationManagers' => $this->relationManagers($slug, $recordModel),
                'specialActions' => $this->specialActions($slug, $recordModel),
                'dangerousActions' => [],
                'validationSummary' => $this->validationSummary($slug),
                'testChecklist' => Phase6ModuleCatalog::testChecklist($slug),
            ],
            'capabilities' => [
                'canSubmit' => ! $readOnly && $user->canManageBackofficeContent(),
                'canView' => $user->canViewBackofficeContent(),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $oldInput
     * @return array<string, mixed>
     */
    public function pageTranslationForm(User $user, Page $page, ?PageTranslation $translation, array $oldInput = []): array
    {
        return $this->translationFormPayload(
            user: $user,
            title: $translation ? 'Editar traduccion de pagina' : 'Crear traduccion de pagina',
            description: 'Paridad React/Inertia del relation manager de traducciones de paginas.',
            action: BackofficePath::active('pages/'.$page->getKey().'/translations'.($translation ? '/'.$translation->getKey() : '')),
            defaults: $this->translationDefaults(
                Phase6ModuleCatalog::pageTranslationForm()['fields'],
                $translation,
                $oldInput,
            ),
            fields: $this->translationFields(
                Phase6ModuleCatalog::pageTranslationForm()['fields'],
                $translation !== null,
            ),
            breadcrumbs: [
                ['label' => 'Backoffice', 'href' => BackofficePath::active()],
                ['label' => 'Paginas', 'href' => BackofficePath::active('pages')],
                ['label' => 'Editar Pagina', 'href' => BackofficePath::active('pages/'.$page->getKey().'/edit')],
                ['label' => $translation ? 'Editar traduccion' : 'Nueva traduccion', 'href' => null],
            ],
            relationManagers: [
                [
                    'label' => 'Pagina propietaria',
                    'description' => 'La traduccion se guarda mediante `updateOrCreate` por `page_id + locale`, igual que el flujo legacy y Filament.',
                    'items' => [
                        [
                            'id' => 'page-'.$page->getKey(),
                            'label' => $page->slug,
                            'meta' => 'Template: '.$page->template,
                            'href' => BackofficePath::active('pages/'.$page->getKey().'/edit'),
                        ],
                    ],
                ],
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $oldInput
     * @return array<string, mixed>
     */
    public function pageBlockTranslationForm(User $user, PageBlock $block, ?PageBlockTranslation $translation, array $oldInput = []): array
    {
        return $this->translationFormPayload(
            user: $user,
            title: $translation ? 'Editar traduccion de bloque' : 'Crear traduccion de bloque',
            description: 'Paridad React/Inertia del relation manager de traducciones de bloques editoriales.',
            action: BackofficePath::active('page-blocks/'.$block->getKey().'/translations'.($translation ? '/'.$translation->getKey() : '')),
            defaults: $this->translationDefaults(
                Phase6ModuleCatalog::pageBlockTranslationForm()['fields'],
                $translation,
                $oldInput,
            ),
            fields: $this->translationFields(
                Phase6ModuleCatalog::pageBlockTranslationForm()['fields'],
                $translation !== null,
            ),
            breadcrumbs: [
                ['label' => 'Backoffice', 'href' => BackofficePath::active()],
                ['label' => 'Bloques de pagina', 'href' => BackofficePath::active('page-blocks')],
                ['label' => 'Editar bloque', 'href' => BackofficePath::active('page-blocks/'.$block->getKey().'/edit')],
                ['label' => $translation ? 'Editar traduccion' : 'Nueva traduccion', 'href' => null],
            ],
            relationManagers: [
                [
                    'label' => 'Bloque propietario',
                    'description' => 'La traduccion se guarda mediante `updateOrCreate` por `page_block_id + locale`.',
                    'items' => [
                        [
                            'id' => 'block-'.$block->getKey(),
                            'label' => $block->key,
                            'meta' => 'Pagina: '.$block->page?->slug,
                            'href' => BackofficePath::active('page-blocks/'.$block->getKey().'/edit'),
                        ],
                    ],
                ],
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $oldInput
     * @return array<string, mixed>
     */
    public function settingTranslationForm(User $user, Setting $setting, ?SettingTranslation $translation, array $oldInput = []): array
    {
        return $this->translationFormPayload(
            user: $user,
            title: $translation ? 'Editar traduccion de setting' : 'Crear traduccion de setting',
            description: 'Paridad React/Inertia del relation manager de traducciones de settings.',
            action: BackofficePath::active('settings/'.$setting->getKey().'/translations'.($translation ? '/'.$translation->getKey() : '')),
            defaults: $this->translationDefaults(
                Phase6ModuleCatalog::settingTranslationForm()['fields'],
                $translation,
                $oldInput,
            ),
            fields: $this->translationFields(
                Phase6ModuleCatalog::settingTranslationForm()['fields'],
                $translation !== null,
            ),
            breadcrumbs: [
                ['label' => 'Backoffice', 'href' => BackofficePath::active()],
                ['label' => 'Settings', 'href' => BackofficePath::active('settings')],
                ['label' => 'Editar setting', 'href' => BackofficePath::active('settings/'.$setting->getKey().'/edit')],
                ['label' => $translation ? 'Editar traduccion' : 'Nueva traduccion', 'href' => null],
            ],
            relationManagers: [
                [
                    'label' => 'Setting propietario',
                    'description' => 'La traduccion se guarda mediante `updateOrCreate` por `setting_id + locale`.',
                    'items' => [
                        [
                            'id' => 'setting-'.$setting->getKey(),
                            'label' => $setting->group.'.'.$setting->key,
                            'meta' => 'Tipo: '.$setting->type,
                            'href' => BackofficePath::active('settings/'.$setting->getKey().'/edit'),
                        ],
                    ],
                ],
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $oldInput
     * @return array<string, mixed>
     */
    public function musicTrackTranslationForm(User $user, MusicTrack $track, ?MusicTrackTranslation $translation, array $oldInput = []): array
    {
        return $this->translationFormPayload(
            user: $user,
            title: $translation ? 'Editar traduccion de track musical' : 'Crear traduccion de track musical',
            description: 'Paridad React/Inertia del relation manager de traducciones de tracks musicales.',
            action: BackofficePath::active('music-tracks/'.$track->getKey().'/translations'.($translation ? '/'.$translation->getKey() : '')),
            defaults: $this->translationDefaults(
                Phase6ModuleCatalog::musicTrackTranslationForm()['fields'],
                $translation,
                $oldInput,
            ),
            fields: $this->translationFields(
                Phase6ModuleCatalog::musicTrackTranslationForm()['fields'],
                $translation !== null,
            ),
            breadcrumbs: [
                ['label' => 'Backoffice', 'href' => BackofficePath::active()],
                ['label' => 'Tracks musicales', 'href' => BackofficePath::active('music-tracks')],
                ['label' => 'Editar track', 'href' => BackofficePath::active('music-tracks/'.$track->getKey().'/edit')],
                ['label' => $translation ? 'Editar traduccion' : 'Nueva traduccion', 'href' => null],
            ],
            relationManagers: [
                [
                    'label' => 'Track propietario',
                    'description' => 'La traduccion se guarda mediante `updateOrCreate` por `music_track_id + locale`, igual que el relation manager Filament.',
                    'items' => [
                        [
                            'id' => 'track-'.$track->getKey(),
                            'label' => $track->slug,
                            'meta' => 'Plataforma: '.$track->platform,
                            'href' => BackofficePath::active('music-tracks/'.$track->getKey().'/edit'),
                        ],
                    ],
                ],
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $oldInput
     * @return array<string, mixed>
     */
    public function legalDocumentTranslationForm(User $user, LegalDocument $document, ?LegalDocumentTranslation $translation, array $oldInput = []): array
    {
        return $this->translationFormPayload(
            user: $user,
            title: $translation ? 'Editar traduccion de documento legal' : 'Crear traduccion de documento legal',
            description: 'Paridad React/Inertia del relation manager de traducciones de documentos legales.',
            action: BackofficePath::active('legal-documents/'.$document->getKey().'/translations'.($translation ? '/'.$translation->getKey() : '')),
            defaults: $this->translationDefaults(
                Phase6ModuleCatalog::legalDocumentTranslationForm()['fields'],
                $translation,
                $oldInput,
            ),
            fields: $this->translationFields(
                Phase6ModuleCatalog::legalDocumentTranslationForm()['fields'],
                $translation !== null,
            ),
            breadcrumbs: [
                ['label' => 'Backoffice', 'href' => BackofficePath::active()],
                ['label' => 'Documentos legales', 'href' => BackofficePath::active('legal-documents')],
                ['label' => 'Editar documento legal', 'href' => BackofficePath::active('legal-documents/'.$document->getKey().'/edit')],
                ['label' => $translation ? 'Editar traduccion' : 'Nueva traduccion', 'href' => null],
            ],
            relationManagers: [
                [
                    'label' => 'Documento propietario',
                    'description' => 'La traduccion se guarda mediante `updateOrCreate` por `legal_document_id + locale`, igual que el relation manager Filament.',
                    'items' => [
                        [
                            'id' => 'legal-document-'.$document->getKey(),
                            'label' => $document->slug,
                            'meta' => 'Tipo: '.$document->document_type,
                            'href' => BackofficePath::active('legal-documents/'.$document->getKey().'/edit'),
                        ],
                    ],
                ],
            ],
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $filters
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function normalizeIndexQuery(array $module, array $filters, array $query): array
    {
        $allowedSorts = array_map(
            fn (array $column): string => $column['key'],
            array_values(array_filter($module['columns'], fn (array $column): bool => $column['sortable'] ?? false))
        );
        $sort = (string) Arr::get($query, 'sort', $module['defaultSort']);

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = $module['defaultSort'];
        }

        return [
            'search' => trim((string) Arr::get($query, 'search', '')),
            'sort' => $sort,
            'direction' => Arr::get($query, 'direction') === 'asc' ? 'asc' : $module['defaultDirection'],
            'page' => max(1, (int) Arr::get($query, 'page', 1)),
            'perPage' => max(1, min(25, (int) Arr::get($query, 'perPage', 2))),
            'filters' => collect($filters)
                ->mapWithKeys(fn (array $filter): array => [
                    $filter['key'] => (string) Arr::get($query, 'filters.'.$filter['key'], ''),
                ])
                ->all(),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $filters
     * @return array<int, array<string, mixed>>
     */
    private function filterPayload(array $filters, array $query): array
    {
        return array_map(function (array $filter) use ($query): array {
            $filter['value'] = Arr::get($query, 'filters.'.$filter['key'], '');

            return $filter;
        }, $filters);
    }

    /**
     * @param  array<int, array<string, mixed>>  $filters
     * @return array<int, array<string, mixed>>
     */
    private function filtersFor(string $slug, array $filters): array
    {
        return match ($slug) {
            'page-blocks' => array_map(function (array $filter): array {
                if ($filter['key'] !== 'page_id') {
                    return $filter;
                }

                $filter['options'] = Page::query()
                    ->orderBy('slug')
                    ->get()
                    ->map(fn (Page $page): array => [
                        'value' => (string) $page->getKey(),
                        'label' => $page->slug,
                    ])->all();

                return $filter;
            }, $filters),
            'media-assets' => array_map(function (array $filter): array {
                if ($filter['key'] !== 'disk') {
                    return $filter;
                }

                $filter['options'] = MediaAsset::query()
                    ->select('disk')
                    ->distinct()
                    ->orderBy('disk')
                    ->pluck('disk')
                    ->map(fn (string $disk): array => ['value' => $disk, 'label' => $disk])
                    ->values()
                    ->all();

                return $filter;
            }, $filters),
            'settings' => array_map(function (array $filter): array {
                if ($filter['key'] !== 'group') {
                    return $filter;
                }

                $filter['options'] = Setting::query()
                    ->select('group')
                    ->distinct()
                    ->orderBy('group')
                    ->pluck('group')
                    ->map(fn (string $group): array => ['value' => $group, 'label' => $group])
                    ->values()
                    ->all();

                return $filter;
            }, $filters),
            'newsletter-logs' => array_map(function (array $filter): array {
                if ($filter['key'] !== 'campaign_id') {
                    return $filter;
                }

                $filter['options'] = NewsletterCampaign::query()
                    ->orderBy('name')
                    ->get()
                    ->map(fn (NewsletterCampaign $campaign): array => [
                        'value' => (string) $campaign->getKey(),
                        'label' => $campaign->name,
                    ])->all();

                return $filter;
            }, $filters),
            default => $filters,
        };
    }

    private function applyIndexQuery(string $slug, array $query): Builder
    {
        $builder = match ($slug) {
            'events' => Event::query(),
            'legal-documents' => LegalDocument::query()->withCount('translations'),
            'music-tracks' => MusicTrack::query()->withCount('translations'),
            'media-assets' => MediaAsset::query(),
            'newsletter-campaigns' => NewsletterCampaign::query(),
            'newsletter-logs' => NewsletterLog::query()->with(['campaign', 'subscriber']),
            'newsletter-subscribers' => NewsletterSubscriber::query(),
            'pages' => Page::query()->withCount('translations'),
            'page-blocks' => PageBlock::query()->with(['page'])->withCount('translations'),
            'partners' => Partner::query(),
            'redirect-rules' => RedirectRule::query(),
            'social-links' => SocialLink::query(),
            'settings' => Setting::query()->withCount('translations'),
            'downloadable-files' => DownloadableFile::query(),
            'seo-metas' => SeoMeta::query(),
            default => abort(404),
        };

        $search = (string) $query['search'];

        if ($search !== '') {
            $this->applySearch($builder, $slug, $search);
        }

        foreach (($query['filters'] ?? []) as $key => $value) {
            if ($value === '') {
                continue;
            }

            $this->applyFilter($builder, $slug, (string) $key, (string) $value);
        }

        return $builder->orderBy((string) $query['sort'], (string) $query['direction']);
    }

    private function applySearch(Builder $builder, string $slug, string $search): void
    {
        $builder->where(function (Builder $nested) use ($slug, $search): void {
            match ($slug) {
                'events' => $nested
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%"),
                'legal-documents' => $nested
                    ->where('slug', 'like', "%{$search}%")
                    ->orWhere('document_type', 'like', "%{$search}%")
                    ->orWhere('version', 'like', "%{$search}%")
                    ->orWhereHas('translations', fn (Builder $translations): Builder => $translations
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('summary', 'like', "%{$search}%")),
                'music-tracks' => $nested
                    ->where('slug', 'like', "%{$search}%")
                    ->orWhere('genre', 'like', "%{$search}%")
                    ->orWhereHas('translations', fn (Builder $translations): Builder => $translations
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('artist_name', 'like', "%{$search}%")),
                'media-assets' => $nested
                    ->where('filename', 'like', "%{$search}%")
                    ->orWhere('path', 'like', "%{$search}%")
                    ->orWhere('mime_type', 'like', "%{$search}%")
                    ->orWhere('alt_text', 'like', "%{$search}%"),
                'newsletter-campaigns' => $nested
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('html_body', 'like', "%{$search}%"),
                'newsletter-logs' => $nested
                    ->where('status', 'like', "%{$search}%")
                    ->orWhere('error_message', 'like', "%{$search}%")
                    ->orWhereHas('campaign', fn (Builder $campaign): Builder => $campaign->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('subscriber', fn (Builder $subscriber): Builder => $subscriber
                        ->where('email', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")),
                'newsletter-subscribers' => $nested
                    ->where('email', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%"),
                'pages' => $nested
                    ->where('slug', 'like', "%{$search}%")
                    ->orWhere('template', 'like', "%{$search}%"),
                'page-blocks' => $nested
                    ->where('key', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%")
                    ->orWhereHas('page', fn (Builder $page): Builder => $page->where('slug', 'like', "%{$search}%")),
                'settings' => $nested
                    ->where('group', 'like', "%{$search}%")
                    ->orWhere('key', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%"),
                'partners' => $nested
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('website_url', 'like', "%{$search}%"),
                'redirect-rules' => $nested
                    ->where('source_path', 'like', "%{$search}%")
                    ->orWhere('destination_url', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhere('locale', 'like', "%{$search}%"),
                'social-links' => $nested
                    ->where('label', 'like', "%{$search}%")
                    ->orWhere('platform', 'like', "%{$search}%")
                    ->orWhere('url', 'like', "%{$search}%"),
                'downloadable-files' => $nested
                    ->where('display_name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('collection', 'like', "%{$search}%")
                    ->orWhere('file_name', 'like', "%{$search}%"),
                'seo-metas' => $nested
                    ->where('entity_type', 'like', "%{$search}%")
                    ->orWhere('meta_title', 'like', "%{$search}%")
                    ->orWhere('canonical_url', 'like', "%{$search}%")
                    ->orWhere('locale', 'like', "%{$search}%"),
                default => null,
            };
        });
    }

    private function applyFilter(Builder $builder, string $slug, string $key, string $value): void
    {
        match ($slug) {
            'events' => $this->applyBooleanFilter($builder, $key, $value),
            'legal-documents' => match ($key) {
                'document_type' => $builder->where('document_type', $value),
                'is_published' => $this->applyBooleanFilter($builder, $key, $value),
                default => null,
            },
            'music-tracks' => match ($key) {
                'platform' => $builder->where('platform', $value),
                'is_featured', 'is_published' => $this->applyBooleanFilter($builder, $key, $value),
                default => null,
            },
            'media-assets' => $key === 'disk' ? $builder->where('disk', $value) : null,
            'newsletter-campaigns' => $key === 'status' ? $builder->where('status', $value) : null,
            'newsletter-logs' => match ($key) {
                'campaign_id' => $builder->where('campaign_id', $value),
                'status' => $builder->where('status', $value),
                default => null,
            },
            'newsletter-subscribers' => $key === 'is_active' ? $this->applyBooleanFilter($builder, $key, $value) : null,
            'pages' => $this->applyBooleanFilter($builder, $key, $value),
            'page-blocks' => match ($key) {
                'page_id' => $builder->where('page_id', $value),
                'is_active' => $this->applyBooleanFilter($builder, $key, $value),
                default => null,
            },
            'partners' => match ($key) {
                'partner_type' => $builder->where('partner_type', $value),
                'is_active' => $this->applyBooleanFilter($builder, $key, $value),
                default => null,
            },
            'redirect-rules' => match ($key) {
                'http_status' => $builder->where('http_status', (int) $value),
                'is_active' => $this->applyBooleanFilter($builder, $key, $value),
                default => null,
            },
            'social-links' => match ($key) {
                'location' => $builder->where('location', $value),
                'is_active' => $this->applyBooleanFilter($builder, $key, $value),
                default => null,
            },
            'settings' => match ($key) {
                'group' => $builder->where('group', $value),
                'is_translatable', 'is_public' => $this->applyBooleanFilter($builder, $key, $value),
                default => null,
            },
            'downloadable-files' => $key === 'is_active' ? $this->applyBooleanFilter($builder, $key, $value) : null,
            'seo-metas' => $key === 'locale' ? $builder->where('locale', $value) : null,
            default => null,
        };
    }

    private function applyBooleanFilter(Builder $builder, string $key, string $value): void
    {
        if (! in_array($key, ['is_featured', 'is_published', 'is_active', 'is_translatable', 'is_public'], true)) {
            return;
        }

        $builder->where($key, $value === '1');
    }

    private function paginate(Builder $builder, array $query): LengthAwarePaginator
    {
        return $builder->paginate(
            perPage: (int) $query['perPage'],
            page: (int) $query['page'],
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $columns
     * @return array<string, mixed>
     */
    private function mapRow(string $slug, Model $record, array $columns, User $user): array
    {
        $canOpen = $this->canOpenRecord($slug, $user);

        return [
            'id' => (string) $record->getKey(),
            'cells' => array_map(function (array $column) use ($slug, $record): array {
                return [
                    'key' => $column['key'],
                    'value' => $this->formatCellValue($slug, $record, $column['key']),
                    'badge' => $column['badge'],
                    'align' => $column['align'],
                ];
            }, $columns),
            'actions' => [
                [
                    'label' => Phase6ModuleCatalog::isReadOnly($slug) ? 'Ver detalle' : 'Editar',
                    'variant' => 'ghost',
                    'enabled' => $canOpen,
                    'href' => $canOpen
                        ? BackofficePath::active($slug.'/'.$record->getKey().'/edit')
                        : null,
                ],
            ],
        ];
    }

    private function formatCellValue(string $slug, Model $record, string $key): string
    {
        $value = match ($slug) {
            'newsletter-logs' => match ($key) {
                'campaign_name' => $record->campaign?->name,
                'subscriber_email' => $record->subscriber?->email,
                default => $record->getAttribute($key),
            },
            'page-blocks' => $key === 'page_slug' ? $record->page?->slug : $record->getAttribute($key),
            'media-assets' => $key === 'size_human' ? $this->formatBytes($record->getAttribute('size')) : $record->getAttribute($key),
            'downloadable-files' => $key === 'attachable_label' ? $this->attachableLabel($record) : $record->getAttribute($key),
            default => $record->getAttribute($key),
        };

        if (is_bool($value)) {
            return $value ? 'Si' : 'No';
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('d/m/Y H:i');
        }

        if (is_numeric($value)) {
            return (string) $value;
        }

        return blank($value) ? '—' : (string) $value;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function sectionsFor(string $slug, string $mode): array
    {
        $sections = $this->module($slug)['formSections'];

        if ($slug === 'page-blocks') {
            $options = Page::query()
                ->orderBy('slug')
                ->get()
                ->map(fn (Page $page): array => ['value' => (string) $page->getKey(), 'label' => $page->slug])
                ->all();

            $sections[0]['fields'] = array_map(function (array $field) use ($options): array {
                if ($field['key'] !== 'page_id') {
                    return $field;
                }

                $field['options'] = $options;

                return $field;
            }, $sections[0]['fields']);
        }

        if ($slug === 'seo-metas' && $mode === 'edit') {
            $sections = array_map(function (array $section): array {
                $section['fields'] = array_map(function (array $field): array {
                    if (in_array($field['key'], ['entity_type', 'entity_id', 'locale'], true)) {
                        $field['disabled'] = true;
                    }

                    return $field;
                }, $section['fields']);

                return $section;
            }, $sections);
        }

        if ($slug === 'newsletter-subscribers') {
            $sections = $this->disableFields($sections, ['subscribed_at', 'unsubscribed_at']);
        }

        if ($slug === 'newsletter-campaigns') {
            $sections = array_map(function (array $section) use ($mode): array {
                $section['fields'] = array_values(array_filter(array_map(function (array $field) use ($mode): ?array {
                    if ($field['key'] === 'status' && $mode === 'create') {
                        return null;
                    }

                    if (in_array($field['key'], ['sent_at', 'sent_count'], true)) {
                        $field['disabled'] = true;
                    }

                    return $field;
                }, $section['fields'])));

                return $section;
            }, $sections);
        }

        if ($slug === 'newsletter-logs') {
            $sections = $this->disableFields($sections, ['campaign_name', 'subscriber_email', 'status', 'processed_at', 'error_message']);
        }

        return $sections;
    }

    /**
     * @param  array<int, array<string, mixed>>  $sections
     * @param  array<string, mixed>  $oldInput
     * @return array<string, mixed>
     */
    private function defaultsFor(string $slug, array $sections, ?Model $record, array $oldInput): array
    {
        $defaults = [];

        foreach ($sections as $section) {
            foreach ($section['fields'] as $field) {
                $defaults[$field['key']] = $this->defaultValueForField($field, $slug, $record);
            }
        }

        return array_merge($defaults, Arr::except($oldInput, ['_token']));
    }

    private function defaultValueForField(array $field, string $slug, ?Model $record): mixed
    {
        $value = $record?->getAttribute($field['key']);

        if ($slug === 'page-blocks' && $field['key'] === 'page_id' && $record instanceof PageBlock) {
            $value = $record->page_id;
        }

        if ($slug === 'newsletter-logs' && $record instanceof NewsletterLog) {
            $value = match ($field['key']) {
                'campaign_name' => $record->campaign?->name,
                'subscriber_email' => $record->subscriber?->email,
                default => $value,
            };
        }

        if ($record instanceof Model) {
            if (is_bool($value)) {
                return $value;
            }

            if ($value instanceof \DateTimeInterface) {
                return $value->format('Y-m-d\TH:i');
            }

            if (is_array($value)) {
                return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }

            return $value ?? '';
        }

        return match ($field['type']) {
            'toggle' => false,
            'number' => 0,
            'select' => '',
            'json', 'textarea', 'text', 'url', 'datetime' => '',
            default => '',
        };
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function relationManagers(string $slug, ?Model $record): array
    {
        return match ($slug) {
            'legal-documents' => $this->legalDocumentRelations($record instanceof LegalDocument ? $record : null),
            'music-tracks' => $this->musicTrackRelations($record instanceof MusicTrack ? $record : null),
            'newsletter-campaigns' => $this->newsletterCampaignRelations($record instanceof NewsletterCampaign ? $record : null),
            'pages' => $this->pageRelations($record instanceof Page ? $record : null),
            'page-blocks' => $this->pageBlockRelations($record instanceof PageBlock ? $record : null),
            'settings' => $this->settingRelations($record instanceof Setting ? $record : null),
            default => [],
        };
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function legalDocumentRelations(?LegalDocument $document): array
    {
        if (! $document instanceof LegalDocument) {
            return [[
                'label' => 'Traducciones',
                'description' => 'Guarda primero el documento legal para habilitar traducciones equivalentes al relation manager.',
                'items' => [],
            ]];
        }

        return [[
            'label' => 'Traducciones',
            'description' => 'Paridad del relation manager de traducciones legales con `updateOrCreate` por locale.',
            'createHref' => BackofficePath::active('legal-documents/'.$document->getKey().'/translations/create'),
            'items' => $document->translations()
                ->orderBy('locale')
                ->get()
                ->map(fn (LegalDocumentTranslation $translation): array => [
                    'id' => (string) $translation->getKey(),
                    'label' => $translation->locale,
                    'meta' => $translation->title,
                    'href' => BackofficePath::active('legal-documents/'.$document->getKey().'/translations/'.$translation->getKey().'/edit'),
                ])->all(),
        ]];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function pageRelations(?Page $page): array
    {
        if (! $page instanceof Page) {
            return [
                [
                    'label' => 'Traducciones',
                    'description' => 'Guarda primero la pagina para habilitar las traducciones equivalentes al relation manager de Filament.',
                    'items' => [],
                ],
                [
                    'label' => 'Bloques',
                    'description' => 'Guarda primero la pagina para habilitar la composicion con bloques hijos.',
                    'items' => [],
                ],
            ];
        }

        return [
            [
                'label' => 'Traducciones',
                'description' => 'Paridad del relation manager de traducciones con `updateOrCreate` por locale.',
                'createHref' => BackofficePath::active('pages/'.$page->getKey().'/translations/create'),
                'items' => $page->translations()
                    ->orderBy('locale')
                    ->get()
                    ->map(fn (PageTranslation $translation): array => [
                        'id' => (string) $translation->getKey(),
                        'label' => $translation->locale,
                        'meta' => $translation->title,
                        'href' => BackofficePath::active('pages/'.$page->getKey().'/translations/'.$translation->getKey().'/edit'),
                    ])->all(),
            ],
            [
                'label' => 'Bloques',
                'description' => 'Composicion padre-hijo equivalente al relation manager de bloques.',
                'createHref' => BackofficePath::active('page-blocks/create').'?page_id='.$page->getKey(),
                'items' => $page->blocks()
                    ->withCount('translations')
                    ->orderBy('position')
                    ->get()
                    ->map(fn (PageBlock $block): array => [
                        'id' => (string) $block->getKey(),
                        'label' => $block->key,
                        'meta' => $block->type.' · Posicion '.$block->position.' · '.$block->translations_count.' traducciones',
                        'href' => BackofficePath::active('page-blocks/'.$block->getKey().'/edit'),
                    ])->all(),
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function musicTrackRelations(?MusicTrack $track): array
    {
        if (! $track instanceof MusicTrack) {
            return [[
                'label' => 'Traducciones',
                'description' => 'Guarda primero el track para habilitar las traducciones equivalentes al relation manager actual.',
                'items' => [],
            ]];
        }

        return [[
            'label' => 'Traducciones',
            'description' => 'Paridad del relation manager de tracks musicales con `updateOrCreate` por locale.',
            'createHref' => BackofficePath::active('music-tracks/'.$track->getKey().'/translations/create'),
            'items' => $track->translations()
                ->orderBy('locale')
                ->get()
                ->map(fn (MusicTrackTranslation $translation): array => [
                    'id' => (string) $translation->getKey(),
                    'label' => $translation->locale,
                    'meta' => trim(($translation->artist_name ? $translation->artist_name.' · ' : '').$translation->title),
                    'href' => BackofficePath::active('music-tracks/'.$track->getKey().'/translations/'.$translation->getKey().'/edit'),
                ])->all(),
        ]];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function pageBlockRelations(?PageBlock $block): array
    {
        if (! $block instanceof PageBlock) {
            return [[
                'label' => 'Traducciones',
                'description' => 'Guarda primero el bloque para habilitar las traducciones por locale.',
                'items' => [],
            ]];
        }

        return [[
            'label' => 'Traducciones',
            'description' => 'Paridad del relation manager de traducciones de bloques con `updateOrCreate` por locale.',
            'createHref' => BackofficePath::active('page-blocks/'.$block->getKey().'/translations/create'),
            'items' => $block->translations()
                ->orderBy('locale')
                ->get()
                ->map(fn (PageBlockTranslation $translation): array => [
                    'id' => (string) $translation->getKey(),
                    'label' => $translation->locale,
                    'meta' => 'Editar JSON traducido del bloque',
                    'href' => BackofficePath::active('page-blocks/'.$block->getKey().'/translations/'.$translation->getKey().'/edit'),
                ])->all(),
        ]];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function settingRelations(?Setting $setting): array
    {
        if (! $setting instanceof Setting) {
            return [[
                'label' => 'Traducciones',
                'description' => 'Guarda primero el setting para habilitar traducciones equivalentes al relation manager.',
                'items' => [],
            ]];
        }

        return [[
            'label' => 'Traducciones',
            'description' => 'Paridad del relation manager de traducciones de settings con `updateOrCreate` por locale.',
            'createHref' => BackofficePath::active('settings/'.$setting->getKey().'/translations/create'),
            'items' => $setting->translations()
                ->orderBy('locale')
                ->get()
                ->map(fn (SettingTranslation $translation): array => [
                    'id' => (string) $translation->getKey(),
                    'label' => $translation->locale,
                    'meta' => 'Editar JSON traducido del setting',
                    'href' => BackofficePath::active('settings/'.$setting->getKey().'/translations/'.$translation->getKey().'/edit'),
                ])->all(),
        ]];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function newsletterCampaignRelations(?NewsletterCampaign $campaign): array
    {
        if (! $campaign instanceof NewsletterCampaign) {
            return [[
                'label' => 'Logs de entrega',
                'description' => 'Guarda primero la campana para consultar los logs reales asociados.',
                'items' => [],
            ]];
        }

        return [[
            'label' => 'Logs de entrega',
            'description' => 'Consulta los logs reales asociados a la campana desde la superficie oficial del backoffice.',
            'createHref' => BackofficePath::active('newsletter-logs').'?filters[campaign_id]='.$campaign->getKey(),
            'createLabel' => 'Ver logs',
            'items' => $campaign->logs()
                ->with('subscriber')
                ->latest('processed_at')
                ->limit(5)
                ->get()
                ->map(fn (NewsletterLog $log): array => [
                    'id' => (string) $log->getKey(),
                    'label' => $log->status,
                    'meta' => trim(($log->subscriber?->email ?? 'Sin suscriptor').' · '.($log->processed_at?->format('d/m/Y H:i') ?? 'Pendiente')),
                    'href' => BackofficePath::active('newsletter-logs/'.$log->getKey().'/edit'),
                ])->all(),
        ]];
    }

    /**
     * @return array<int, string>
     */
    private function validationSummary(string $slug): array
    {
        return match ($slug) {
            'events' => [
                'Slug: required, string, max:255, unique.',
                'Titulo: required, string, max:255.',
                'URL externa: nullable, url.',
                'Fechas: nullable, date.',
            ],
            'music-tracks' => [
                'Slug: required, string, max:255, unique.',
                'Plataforma: required, in:soundcloud,spotify,youtube,apple_music,custom.',
                'Year: nullable, integer, min:1900, max:2100.',
                'Settings JSON: nullable, array.',
            ],
            'media-assets' => [
                'Disk / Path / Filename: required.',
                'MIME type: nullable, string.',
                'Size / Width / Height: nullable, integer, min:0.',
                'Metadata JSON: nullable, array.',
            ],
            'pages' => [
                'Slug: required, string, max:255, unique.',
                'Template: required, string, max:120.',
                'Publicado: boolean.',
            ],
            'page-blocks' => [
                'Pagina: required, exists:pages,id.',
                'Clave: required, string, max:255, unique por pagina.',
                'Tipo: required, string, max:120.',
                'Settings JSON: nullable, array.',
            ],
            'settings' => [
                'Grupo + clave: required y unicos en conjunto.',
                'Tipo: required, in:string,json,boolean,number,url,html.',
                'Value JSON y Settings JSON: nullable, array.',
            ],
            'partners' => [
                'Slug: required, string, max:255, unique.',
                'Nombre: required, string, max:255.',
                'Tipo: required, in:sponsor,partner,media.',
                'Website URL: nullable, url.',
            ],
            'social-links' => [
                'Plataforma: required, string, max:120.',
                'URL: required, url.',
                'Ubicacion: required, in:global,contact,footer.',
                'Settings JSON: nullable, array.',
            ],
            'downloadable-files' => [
                'Slug: required, string, max:255, unique.',
                'Nombre visible y disk: required.',
                'Adjunto polimorfico: si hay tipo, debe existir el ID relacionado.',
                'Settings JSON: nullable, array.',
            ],
            'legal-documents' => [
                'Slug: required, string, max:255, unique.',
                'Tipo: required, in:terms,privacy,cookies,custom.',
                'Version: nullable, string, max:120.',
                'Settings JSON: nullable, array.',
            ],
            'newsletter-campaigns' => [
                'Nombre y asunto: required, string, max:255.',
                'HTML body: required, string.',
                'Status: draft|queued|sent|cancelled solo en edit.',
                'Fechas y contadores derivados permanecen bloqueados salvo la programacion.',
            ],
            'newsletter-logs' => [
                'Modulo de solo lectura; no admite mutaciones desde esta superficie.',
                'Filtro por campana y estado sobre persistencia real.',
            ],
            'newsletter-subscribers' => [
                'Email: required, email:rfc,dns, max:255, unique.',
                'Nombre: nullable, string, max:255.',
                'Alta/baja reutiliza `PrepareNewsletterSubscriberData` para fechas derivadas.',
            ],
            'seo-metas' => [
                'Entity type / Entity ID / locale: required; la combinacion se upserta en create.',
                'Canonical URL: nullable, url.',
                'Open Graph / Twitter / JSON-LD: nullable, array.',
            ],
            'redirect-rules' => [
                'Origen: required, string, max:255, unique.',
                'Destino: required, url o path absoluto, max:65535.',
                'HTTP status: required, in:301,302,307,308.',
                'Hit count: nullable, integer, min:0.',
            ],
            default => [],
        };
    }

    private function resolveRecord(string $slug, string $record): Model
    {
        return match ($slug) {
            'events' => Event::query()->findOrFail($record),
            'legal-documents' => LegalDocument::query()->with('translations')->findOrFail($record),
            'music-tracks' => MusicTrack::query()->with('translations')->findOrFail($record),
            'media-assets' => MediaAsset::query()->findOrFail($record),
            'newsletter-campaigns' => NewsletterCampaign::query()->with(['logs.subscriber'])->findOrFail($record),
            'newsletter-logs' => NewsletterLog::query()->with(['campaign', 'subscriber'])->findOrFail($record),
            'newsletter-subscribers' => NewsletterSubscriber::query()->findOrFail($record),
            'pages' => Page::query()->findOrFail($record),
            'page-blocks' => PageBlock::query()->with(['page', 'translations'])->findOrFail($record),
            'partners' => Partner::query()->findOrFail($record),
            'redirect-rules' => RedirectRule::query()->findOrFail($record),
            'social-links' => SocialLink::query()->findOrFail($record),
            'settings' => Setting::query()->with('translations')->findOrFail($record),
            'downloadable-files' => DownloadableFile::query()->findOrFail($record),
            'seo-metas' => SeoMeta::query()->findOrFail($record),
            default => abort(404),
        };
    }

    private function phaseLabel(string $slug): string
    {
        return match (true) {
            in_array($slug, ['music-tracks', 'media-assets', 'partners', 'social-links', 'downloadable-files'], true) => 'Fase 7',
            in_array($slug, ['legal-documents', 'redirect-rules', 'newsletter-subscribers', 'newsletter-campaigns', 'newsletter-logs'], true) => 'Fase 8',
            default => 'Fase 6',
        };
    }

    private function formatBytes(mixed $bytes): string
    {
        if (! is_numeric($bytes)) {
            return '—';
        }

        $bytes = (int) $bytes;

        return match (true) {
            $bytes >= 1024 * 1024 => number_format($bytes / (1024 * 1024), 2).' MB',
            $bytes >= 1024 => number_format($bytes / 1024, 2).' KB',
            default => $bytes.' B',
        };
    }

    private function attachableLabel(Model $record): string
    {
        $type = $record->getAttribute('attachable_type');
        $id = $record->getAttribute('attachable_id');

        if (! is_string($type) || blank($type) || ! $id) {
            return '—';
        }

        return class_basename($type).' #'.$id;
    }

    /**
     * @param  array<int, array<string, mixed>>  $sections
     * @param  array<int, string>  $fields
     * @return array<int, array<string, mixed>>
     */
    private function disableFields(array $sections, array $fields): array
    {
        return array_map(function (array $section) use ($fields): array {
            $section['fields'] = array_map(function (array $field) use ($fields): array {
                if (in_array($field['key'], $fields, true)) {
                    $field['disabled'] = true;
                }

                return $field;
            }, $section['fields']);

            return $section;
        }, $sections);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function specialActions(string $slug, ?Model $record): array
    {
        if ($slug !== 'newsletter-campaigns' || ! $record instanceof NewsletterCampaign) {
            return [];
        }

        if (! in_array($record->status, ['draft', 'cancelled'], true)) {
            return [];
        }

        $action = Phase6ModuleCatalog::specialAction($slug, 'queue-campaign');

        if (! is_array($action)) {
            return [];
        }

        return [[
            ...$action,
            'endpoint' => BackofficePath::active('newsletter-campaigns/actions/queue-campaign'),
            'record' => (string) $record->getKey(),
        ]];
    }

    private function canCreateFor(string $slug, User $user): bool
    {
        return ! Phase6ModuleCatalog::isReadOnly($slug) && $user->canManageBackofficeContent();
    }

    private function canOpenRecord(string $slug, User $user): bool
    {
        if (Phase6ModuleCatalog::isReadOnly($slug)) {
            return $user->canViewBackofficeContent();
        }

        return $user->canManageBackofficeContent();
    }

    /**
     * @return array<string, mixed>
     */
    private function module(string $slug): array
    {
        return Phase6ModuleCatalog::module($slug);
    }

    /**
     * @param  array<int, array<string, mixed>>  $fields
     * @param  array<string, mixed>  $oldInput
     * @return array<string, mixed>
     */
    private function translationDefaults(array $fields, ?Model $translation, array $oldInput): array
    {
        $defaults = [];

        foreach ($fields as $field) {
            $value = $translation?->getAttribute($field['key']);

            if (is_array($value)) {
                $value = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }

            $defaults[$field['key']] = $value ?? ($field['type'] === 'json' ? '' : '');
        }

        return array_merge($defaults, Arr::except($oldInput, ['_token']));
    }

    /**
     * @param  array<int, array<string, mixed>>  $fields
     * @return array<int, array<string, mixed>>
     */
    private function translationFields(array $fields, bool $editing): array
    {
        return array_map(function (array $field) use ($editing): array {
            if ($editing && $field['key'] === 'locale') {
                $field['disabled'] = true;
            }

            return $field;
        }, $fields);
    }

    /**
     * @param  array<string, mixed>  $defaults
     * @param  array<int, array<string, mixed>>  $fields
     * @param  array<int, array<string, mixed>>  $breadcrumbs
     * @param  array<int, array<string, mixed>>  $relationManagers
     * @return array<string, mixed>
     */
    private function translationFormPayload(
        User $user,
        string $title,
        string $description,
        string $action,
        array $defaults,
        array $fields,
        array $breadcrumbs,
        array $relationManagers,
    ): array {
        return [
            'mode' => 'edit',
            'module' => [
                'slug' => 'translations',
                'title' => 'Traducciones',
                'singular' => 'Traduccion',
                'group' => 'Editorial',
                'description' => $description,
            ],
            'recordLabel' => null,
            'title' => $title,
            'description' => $description,
            'breadcrumbs' => $breadcrumbs,
            'actions' => [
                [
                    'label' => 'Volver',
                    'href' => $breadcrumbs[count($breadcrumbs) - 2]['href'] ?? BackofficePath::active(),
                    'variant' => 'ghost',
                    'visible' => true,
                ],
            ],
            'summaryCards' => [
                ['label' => 'Persistencia', 'value' => 'REAL', 'tone' => 'emerald'],
                ['label' => 'Relation manager', 'value' => 'EQUIVALENTE', 'tone' => 'cyan'],
            ],
            'form' => [
                'action' => $action,
                'method' => 'post',
                'submitLabel' => 'Guardar traduccion',
                'defaults' => $defaults,
                'sections' => [[
                    'title' => $title,
                    'fields' => $fields,
                ]],
                'relationManagers' => $relationManagers,
                'specialActions' => [],
                'dangerousActions' => [],
                'validationSummary' => [
                    'Locale: required, fijo en edit.',
                    'Payload JSON: se decodifica y valida en servidor antes de persistir.',
                ],
                'testChecklist' => [
                    'Feature test de create/edit del equivalente al relation manager.',
                    'Validacion de locale unico por propietario.',
                ],
            ],
            'capabilities' => [
                'canSubmit' => $user->canManageBackofficeContent(),
                'canView' => $user->canViewBackofficeContent(),
            ],
        ];
    }
}

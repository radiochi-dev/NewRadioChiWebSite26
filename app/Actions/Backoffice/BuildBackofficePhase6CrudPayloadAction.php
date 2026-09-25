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
use App\Support\BackofficeLocales;
use App\Support\Backoffice\Phase6ModuleCatalog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BuildBackofficePhase6CrudPayloadAction
{
    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function index(User $user, string $slug, array $query = []): array
    {
        if ($slug === 'pages') {
            return $this->editorialPagesIndex($user, $query);
        }

        if ($slug === 'media-assets') {
            return $this->mediaAssetsIndex($user, $query);
        }

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
        ];

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $oldInput
     * @return array<string, mixed>
     */
    public function form(User $user, string $slug, string $mode, ?string $record = null, array $oldInput = []): array
    {
        if ($slug === 'pages') {
            abort_if($mode !== 'edit' || ! is_string($record) || trim($record) === '', 404);

            return $this->editorialPageForm($user, $record, request()->query('locale'));
        }

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
                ...$this->localeHeaderActionsFor($slug, $recordModel),
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
                'mediaLibrary' => $this->hasImageFields($sections) ? $this->imageLibrary() : [],
                'mediaUploadUrl' => $this->hasImageFields($sections) ? BackofficePath::active('media-assets/uploads/images') : null,
                'relationManagers' => $this->relationManagers($slug, $recordModel),
                'specialActions' => $this->specialActions($slug, $recordModel),
                'dangerousActions' => [],
                'validationSummary' => $this->validationSummary($slug),
            ],
            'capabilities' => [
                'canSubmit' => ! $readOnly && $user->canManageBackofficeContent(),
                'canView' => $user->canViewBackofficeContent(),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    private function mediaAssetsIndex(User $user, array $query = []): array
    {
        $module = $this->module('media-assets');
        $normalized = $this->normalizeMediaAssetsQuery($query);
        $usageIndex = $this->mediaAssetUsageIndex();

        $cards = MediaAsset::query()
            ->orderByDesc('updated_at')
            ->get()
            ->map(function (MediaAsset $asset) use ($usageIndex): array {
                return $this->mapMediaAssetCard(
                    $asset,
                    $usageIndex[$this->normalizeAssetReference((string) $asset->path)] ?? [],
                );
            });

        $filteredCards = $cards
            ->filter(function (array $card) use ($normalized): bool {
                $search = Str::lower(trim((string) $normalized['search']));

                if ($search !== '' && ! Str::contains(Str::lower((string) $card['filename']), $search)) {
                    return false;
                }

                $assignedPage = (string) Arr::get($normalized, 'filters.assigned_page', '');

                if ($assignedPage !== '' && ! in_array($assignedPage, $card['assignedPageKeys'], true)) {
                    return false;
                }

                $format = (string) Arr::get($normalized, 'filters.format', '');

                if ($format !== '' && $card['formatKey'] !== $format) {
                    return false;
                }

                return true;
            })
            ->values();

        $paginator = $this->paginateCollection($filteredCards, $normalized, BackofficePath::active($module['slug']));

        return [
            'mode' => 'index',
            'module' => Arr::only($module, ['slug', 'title', 'singular', 'group', 'description']),
            'title' => $module['title'],
            'description' => 'Galeria visual del catalogo multimedia del backoffice con tarjetas, metadata util y filtros editoriales por nombre, pagina asignada y formato.',
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
                    'visible' => $this->canCreateFor('media-assets', $user),
                ],
            ],
            'summaryCards' => [
                ['label' => 'Assets', 'value' => (string) $cards->count(), 'tone' => 'cyan'],
                ['label' => 'En uso', 'value' => (string) $cards->filter(fn (array $card): bool => $card['isAssigned'])->count(), 'tone' => 'emerald'],
                ['label' => 'Imagenes', 'value' => (string) $cards->where('formatKey', 'image')->count(), 'tone' => 'fuchsia'],
                ['label' => 'Videos / PDF', 'value' => (string) ($cards->where('formatKey', 'video')->count() + $cards->where('formatKey', 'pdf')->count()), 'tone' => 'amber'],
            ],
            'filters' => [
                [
                    'key' => 'assigned_page',
                    'label' => 'Pagina asignada',
                    'placeholder' => 'Todas las paginas',
                    'options' => $this->mediaAssetPageOptions($cards),
                    'value' => Arr::get($normalized, 'filters.assigned_page', ''),
                ],
                [
                    'key' => 'format',
                    'label' => 'Formato',
                    'placeholder' => 'Todos los formatos',
                    'options' => [
                        ['value' => 'image', 'label' => 'Imagenes'],
                        ['value' => 'video', 'label' => 'Videos'],
                        ['value' => 'pdf', 'label' => 'PDF'],
                    ],
                    'value' => Arr::get($normalized, 'filters.format', ''),
                ],
            ],
            'gallery' => [
                'path' => BackofficePath::active($module['slug']),
                'query' => $normalized,
                'items' => array_values($paginator->items()),
                'emptyState' => [
                    'title' => 'Sin assets para esta busqueda',
                    'description' => 'No hay resultados con el nombre, la pagina asignada o el formato seleccionados.',
                    'ctaLabel' => $this->canCreateFor('media-assets', $user) ? 'Subir nuevo asset' : null,
                    'ctaHref' => $this->canCreateFor('media-assets', $user) ? BackofficePath::active('media-assets/create') : null,
                ],
                'pagination' => [
                    'currentPage' => $paginator->currentPage(),
                    'perPage' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'totalPages' => $paginator->lastPage(),
                ],
            ],
            'table' => [
                'path' => BackofficePath::active($module['slug']),
                'query' => $normalized,
                'columns' => $module['columns'],
                'rows' => array_map(
                    fn (MediaAsset $record): array => $this->mapRow('media-assets', $record, $module['columns'], $user),
                    MediaAsset::query()
                        ->whereIn('id', collect($paginator->items())->pluck('id')->all())
                        ->orderByDesc('updated_at')
                        ->get()
                        ->all()
                ),
                'emptyState' => [
                    'title' => 'Sin registros todavia',
                    'description' => 'El catalogo multimedia todavia no tiene registros que coincidan con este filtro.',
                    'ctaLabel' => null,
                    'ctaHref' => null,
                ],
                'bulkActions' => [],
                'sort' => [
                    'column' => 'updated_at',
                    'direction' => 'desc',
                ],
                'pagination' => [
                    'currentPage' => $paginator->currentPage(),
                    'perPage' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'totalPages' => $paginator->lastPage(),
                ],
            ],
            'capabilities' => [
                'canCreate' => $this->canCreateFor('media-assets', $user),
                'canEdit' => $user->canManageBackofficeContent(),
                'canDelete' => false,
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
        $schema = $this->pageTranslationSchema($page);
        $pageEditHref = $this->pageEditHref($page, $translation?->locale ?? request()->query('locale', 'es'));

        return $this->translationFormPayload(
            user: $user,
            title: $translation ? 'Editar traduccion de pagina' : 'Crear traduccion de pagina',
            description: $schema['description'],
            action: $this->withEditorQuery(
                BackofficePath::active('pages/'.$page->slug.'/translations'.($translation ? '/'.$translation->getKey() : '')),
                $translation?->locale ?? request()->query('locale'),
                request()->query('from'),
            ),
            defaults: $this->translationDefaults($schema['fields'], $translation, $oldInput, request()->query('locale')),
            fields: $this->translationFields($schema['fields'], $translation !== null),
            breadcrumbs: [
                ['label' => 'Backoffice', 'href' => BackofficePath::active()],
                ['label' => 'Paginas', 'href' => BackofficePath::active('pages')],
                ['label' => 'Editar Pagina', 'href' => $pageEditHref],
                ['label' => $translation ? 'Editar traduccion' : 'Nueva traduccion', 'href' => null],
            ],
            relationManagers: [],
            actions: $this->pageTranslationHeaderActions($page, $translation),
            validationSummary: [],
        );
    }

    /**
     * @param  array<string, mixed>  $oldInput
     * @return array<string, mixed>
     */
    public function pageBlockTranslationForm(User $user, PageBlock $block, ?PageBlockTranslation $translation, array $oldInput = []): array
    {
        if ($translation instanceof PageBlockTranslation) {
            $translation->setRelation('pageBlock', $block);
        }

        $schema = $this->pageBlockTranslationSchema($block);
        $pageOwnerHref = $block->page instanceof Page
            ? $this->pageEditHref($block->page, $translation?->locale ?? request()->query('locale', 'es'))
            : BackofficePath::active('pages');

        return $this->translationFormPayload(
            user: $user,
            title: $translation ? 'Editar traduccion de bloque' : 'Crear traduccion de bloque',
            description: $schema['description'],
            action: $this->withEditorQuery(
                $block->page instanceof Page
                    ? BackofficePath::active('pages/'.$block->page->slug.'/components/'.$block->key)
                    : BackofficePath::active('page-blocks/'.$block->getKey().'/translations'.($translation ? '/'.$translation->getKey() : '')),
                $translation?->locale ?? request()->query('locale'),
                request()->query('from'),
            ),
            defaults: $this->translationDefaults($schema['fields'], $translation, $oldInput, request()->query('locale')),
            fields: $this->translationFields($schema['fields'], $translation !== null),
            breadcrumbs: [
                ['label' => 'Backoffice', 'href' => BackofficePath::active()],
                ['label' => 'Paginas', 'href' => BackofficePath::active('pages')],
                ['label' => 'Editar Pagina', 'href' => $pageOwnerHref],
                ['label' => $translation ? 'Editar traduccion' : 'Nueva traduccion', 'href' => null],
            ],
            relationManagers: [],
            validationSummary: [],
        );
    }

    /**
     * @param  array<string, mixed>  $oldInput
     * @return array<string, mixed>
     */
    public function settingTranslationForm(User $user, Setting $setting, ?SettingTranslation $translation, array $oldInput = []): array
    {
        $schema = $this->settingTranslationSchema($setting);

        return $this->translationFormPayload(
            user: $user,
            title: $translation ? 'Editar traduccion de setting' : 'Crear traduccion de setting',
            description: $schema['description'],
            action: $this->withEditorQuery(
                BackofficePath::active('settings/'.$setting->getKey().'/translations'.($translation ? '/'.$translation->getKey() : '')),
                $translation?->locale ?? request()->query('locale'),
                request()->query('from'),
            ),
            defaults: $this->translationDefaults($schema['fields'], $translation, $oldInput, request()->query('locale')),
            fields: $this->translationFields($schema['fields'], $translation !== null),
            breadcrumbs: [
                ['label' => 'Backoffice', 'href' => BackofficePath::active()],
                ['label' => 'Settings', 'href' => BackofficePath::active('settings')],
                ['label' => 'Editar setting', 'href' => BackofficePath::active('settings/'.$setting->getKey().'/edit')],
                ['label' => $translation ? 'Editar traduccion' : 'Nueva traduccion', 'href' => null],
            ],
            relationManagers: [],
            validationSummary: [],
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
            relationManagers: [],
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
            action: $this->withEditorQuery(
                BackofficePath::active('legal-documents/'.$document->getKey().'/translations'.($translation ? '/'.$translation->getKey() : '')),
                $translation?->locale ?? request()->query('locale'),
                request()->query('from'),
            ),
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
            relationManagers: [],
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
            'perPage' => max(1, min(50, (int) Arr::get($query, 'perPage', 10))),
            'filters' => collect($filters)
                ->mapWithKeys(fn (array $filter): array => [
                    $filter['key'] => (string) Arr::get($query, 'filters.'.$filter['key'], ''),
                ])
                ->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    private function normalizeMediaAssetsQuery(array $query): array
    {
        return [
            'search' => trim((string) Arr::get($query, 'search', '')),
            'page' => max(1, (int) Arr::get($query, 'page', 1)),
            'perPage' => max(1, min(50, (int) Arr::get($query, 'perPage', 10))),
            'filters' => [
                'assigned_page' => trim((string) Arr::get($query, 'filters.assigned_page', '')),
                'format' => trim((string) Arr::get($query, 'filters.format', '')),
            ],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function paginateCollection(Collection $items, array $query, string $path): LengthAwarePaginator
    {
        $currentPage = max(1, (int) ($query['page'] ?? 1));
        $perPage = max(1, (int) ($query['perPage'] ?? 10));
        $total = $items->count();
        $results = $items
            ->slice(($currentPage - 1) * $perPage, $perPage)
            ->values()
            ->all();

        return new LengthAwarePaginator(
            $results,
            $total,
            $perPage,
            $currentPage,
            ['path' => $path]
        );
    }

    /**
     * @return array<string, array{pages: array<int, string>}>
     */
    private function mediaAssetUsageIndex(): array
    {
        $usage = [];
        $register = function (?string $path, string $pageSlug) use (&$usage): void {
            $normalized = $this->normalizeAssetReference((string) $path);

            if ($normalized === '') {
                return;
            }

            $usage[$normalized]['pages'] ??= [];

            if (! in_array($pageSlug, $usage[$normalized]['pages'], true)) {
                $usage[$normalized]['pages'][] = $pageSlug;
            }
        };

        PageBlock::query()
            ->with('page:id,slug')
            ->get(['id', 'page_id', 'settings'])
            ->each(function (PageBlock $block) use ($register): void {
                $pageSlug = (string) ($block->page?->slug ?? '');

                if ($pageSlug === '') {
                    return;
                }

                foreach (['logo', 'personImage', 'elipseImage', 'image', 'image2'] as $key) {
                    $register((string) data_get($block->settings, $key, ''), $pageSlug);
                }
            });

        MusicTrack::query()
            ->get(['id', 'label_image_path', 'cover_image_path'])
            ->each(function (MusicTrack $track) use ($register): void {
                $register((string) $track->label_image_path, 'music');
                $register((string) $track->cover_image_path, 'music');
            });

        Partner::query()
            ->get(['id', 'logo_path'])
            ->each(fn (Partner $partner) => $register((string) $partner->logo_path, 'contact'));

        Event::query()
            ->get(['id', 'poster_path'])
            ->each(fn (Event $event) => $register((string) $event->poster_path, 'calendar'));

        MediaAsset::query()
            ->get(['id', 'path', 'metadata'])
            ->each(function (MediaAsset $asset) use ($register): void {
                if (in_array((string) data_get($asset->metadata, 'kind', ''), ['photo', 'video'], true)) {
                    $register((string) $asset->path, 'media');
                }
            });

        return $usage;
    }

    /**
     * @param  array{pages?: array<int, string>}  $usage
     * @return array<string, mixed>
     */
    private function mapMediaAssetCard(MediaAsset $asset, array $usage): array
    {
        $pageKeys = collect($usage['pages'] ?? [])
            ->filter(fn (mixed $page): bool => is_string($page) && $page !== '')
            ->unique()
            ->sortBy(fn (string $slug): array => [$this->editorialPageSortWeight($slug), $slug])
            ->values()
            ->all();

        $formatKey = $this->mediaAssetFormat($asset);
        $preview = $this->mediaAssetPreview($asset, $formatKey);

        return [
            'id' => (string) $asset->getKey(),
            'filename' => (string) $asset->filename,
            'preview' => $preview,
            'formatKey' => $formatKey,
            'formatLabel' => match ($formatKey) {
                'image' => 'Imagen',
                'video' => 'Video',
                'pdf' => 'PDF',
                default => 'Archivo',
            },
            'mimeType' => (string) ($asset->mime_type ?? '—'),
            'sizeHuman' => $this->formatBytes($asset->size),
            'dimensions' => $asset->width && $asset->height ? $asset->width.' x '.$asset->height.' px' : '—',
            'assignedPageKeys' => $pageKeys,
            'assignedPages' => array_map(fn (string $slug): string => $this->editorialPageLabel($slug), $pageKeys),
            'isAssigned' => $pageKeys !== [],
            'updatedAt' => $asset->updated_at?->format('d/m/Y H:i') ?? '—',
            'editHref' => BackofficePath::active('media-assets/'.$asset->getKey().'/edit'),
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $cards
     * @return array<int, array{value: string, label: string}>
     */
    private function mediaAssetPageOptions(Collection $cards): array
    {
        return $cards
            ->flatMap(fn (array $card): array => $card['assignedPageKeys'])
            ->filter(fn (mixed $slug): bool => is_string($slug) && $slug !== '')
            ->unique()
            ->sortBy(fn (string $slug): array => [$this->editorialPageSortWeight($slug), $slug])
            ->values()
            ->map(fn (string $slug): array => ['value' => $slug, 'label' => $this->editorialPageLabel($slug)])
            ->all();
    }

    private function mediaAssetFormat(MediaAsset $asset): string
    {
        $mimeType = Str::lower((string) ($asset->mime_type ?? ''));
        $path = Str::lower((string) $asset->path);
        $kind = Str::lower((string) data_get($asset->metadata, 'kind', ''));

        if (Str::startsWith($mimeType, 'image/') || preg_match('/\.(png|jpe?g|webp|gif|svg|avif)$/', $path) === 1) {
            return 'image';
        }

        if ($kind === 'video' || Str::startsWith($mimeType, 'video/') || $mimeType === 'video/youtube' || Str::startsWith($path, 'youtube:')) {
            return 'video';
        }

        if ($mimeType === 'application/pdf' || str_ends_with($path, '.pdf')) {
            return 'pdf';
        }

        return 'other';
    }

    /**
     * @param  string  $formatKey
     * @return array<string, string|null>
     */
    private function mediaAssetPreview(MediaAsset $asset, string $formatKey): array
    {
        if ($formatKey === 'image') {
            return [
                'kind' => 'image',
                'url' => $this->normalizeMediaPath((string) $asset->path),
            ];
        }

        if ($formatKey === 'video') {
            $thumbnail = (string) data_get($asset->metadata, 'thumbnail', '');
            $youtubeId = (string) data_get($asset->metadata, 'youtube_id', '');

            if ($thumbnail !== '') {
                return [
                    'kind' => 'video',
                    'url' => $thumbnail,
                ];
            }

            if ($youtubeId !== '') {
                return [
                    'kind' => 'video',
                    'url' => 'https://img.youtube.com/vi/'.$youtubeId.'/hqdefault.jpg',
                ];
            }
        }

        return [
            'kind' => $formatKey,
            'url' => null,
        ];
    }

    private function normalizeAssetReference(string $path): string
    {
        $path = trim($path);

        if ($path === '') {
            return '';
        }

        if (Str::startsWith($path, ['http://', 'https://', 'data:', 'youtube:'])) {
            return $path;
        }

        return '/'.ltrim($path, '/');
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
            'social-links' => SocialLink::query()->where('location', 'global'),
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
            'social-links' => $key === 'is_active' ? $this->applyBooleanFilter($builder, $key, $value) : null,
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
            'json', 'textarea', 'text', 'url', 'datetime', 'image' => '',
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
                'createHref' => BackofficePath::active('pages/'.$page->slug.'/translations/create'),
                'items' => $page->translations()
                    ->orderBy('locale')
                    ->get()
                    ->map(fn (PageTranslation $translation): array => [
                        'id' => (string) $translation->getKey(),
                        'label' => $translation->locale,
                        'meta' => $translation->title,
                        'href' => BackofficePath::active('pages/'.$page->slug.'/translations/'.$translation->getKey().'/edit'),
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
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    private function editorialPagesIndex(User $user, array $query): array
    {
        $pages = Page::query()
            ->withCount([
                'translations',
                'blocks as active_blocks_count' => fn (Builder $builder) => $builder->where('is_active', true),
            ])
            ->get()
            ->sortBy(fn (Page $page): array => [$this->editorialPageSortWeight($page->slug), $page->slug])
            ->values();

        $rows = $pages
            ->map(function (Page $page) use ($user): array {
                return [
                    'id' => (string) $page->getKey(),
                    'cells' => [
                        ['key' => 'page', 'value' => $this->editorialPageLabel($page->slug), 'badge' => false, 'align' => 'left'],
                        ['key' => 'template', 'value' => $page->template ?: 'default', 'badge' => true, 'align' => 'left'],
                        ['key' => 'components', 'value' => (string) (max(0, (int) $page->active_blocks_count) + 1), 'badge' => false, 'align' => 'right'],
                        ['key' => 'locales', 'value' => (string) $page->translations_count, 'badge' => false, 'align' => 'right'],
                        ['key' => 'updated_at', 'value' => $page->updated_at?->format('d/m/Y H:i') ?? '—', 'badge' => false, 'align' => 'left'],
                    ],
                    'actions' => [[
                        'label' => 'Editar',
                        'variant' => 'ghost',
                        'enabled' => $user->canManageBackofficeContent(),
                        'href' => $this->pageEditHref($page, 'es'),
                    ]],
                ];
            })
            ->all();

        $rows[] = [
            'id' => 'login',
            'cells' => [
                ['key' => 'page', 'value' => 'Login', 'badge' => false, 'align' => 'left'],
                ['key' => 'template', 'value' => 'auth', 'badge' => true, 'align' => 'left'],
                ['key' => 'components', 'value' => '3', 'badge' => false, 'align' => 'right'],
                ['key' => 'locales', 'value' => (string) count(BackofficeLocales::values()), 'badge' => false, 'align' => 'right'],
                ['key' => 'updated_at', 'value' => 'Vivo', 'badge' => false, 'align' => 'left'],
            ],
            'actions' => [[
                'label' => 'Editar',
                'variant' => 'ghost',
                'enabled' => $user->canManageBackofficeContent(),
                'href' => BackofficePath::active('pages/login/edit').'?locale=es',
            ]],
        ];

        return [
            'mode' => 'index',
            'module' => [
                'slug' => 'pages',
                'title' => 'Paginas',
                'singular' => 'Pagina',
                'group' => 'Editorial',
                'description' => 'Flujo editorial page-centric: pagina -> componentes -> editor tipado del componente.',
            ],
            'title' => 'Paginas',
            'description' => 'Lista real de paginas administrables. Al entrar en una pagina solo se muestran sus componentes y cada componente abre su editor propio.',
            'breadcrumbs' => [
                ['label' => 'Backoffice', 'href' => BackofficePath::active()],
                ['label' => 'Editorial', 'href' => null],
                ['label' => 'Paginas', 'href' => BackofficePath::active('pages')],
            ],
            'actions' => [],
            'summaryCards' => [
                ['label' => 'Paginas reales', 'value' => (string) count($rows), 'tone' => 'cyan'],
                ['label' => 'Modo editorial', 'value' => 'PAGE', 'tone' => 'fuchsia'],
                ['label' => 'Lectura real', 'value' => 'DB', 'tone' => 'emerald'],
                ['label' => 'Locale base', 'value' => 'ES', 'tone' => 'amber'],
            ],
            'filters' => [],
            'table' => [
                'path' => BackofficePath::active('pages'),
                'query' => $this->editorialIndexQueryState($query, max(1, count($rows))),
                'columns' => [
                    ['key' => 'page', 'label' => 'Pagina', 'sortable' => false, 'badge' => false, 'align' => 'left'],
                    ['key' => 'template', 'label' => 'Template', 'sortable' => false, 'badge' => false, 'align' => 'left'],
                    ['key' => 'components', 'label' => 'Componentes', 'sortable' => false, 'badge' => false, 'align' => 'right'],
                    ['key' => 'locales', 'label' => 'Idiomas', 'sortable' => false, 'badge' => false, 'align' => 'right'],
                    ['key' => 'updated_at', 'label' => 'Actualizada', 'sortable' => false, 'badge' => false, 'align' => 'left'],
                ],
                'rows' => $rows,
                'emptyState' => [
                    'title' => 'Sin paginas editoriales',
                    'description' => 'El bootstrap del CMS debe exponer al menos `Home` y la superficie de `Login`.',
                    'ctaLabel' => null,
                    'ctaHref' => null,
                ],
                'bulkActions' => [],
                'sort' => ['column' => '', 'direction' => 'asc'],
                'pagination' => [
                    'currentPage' => 1,
                    'perPage' => max(1, count($rows)),
                    'total' => count($rows),
                    'totalPages' => 1,
                ],
            ],
            'capabilities' => [
                'canCreate' => false,
                'canEdit' => $user->canManageBackofficeContent(),
                'canDelete' => false,
                'canView' => $user->canViewBackofficeContent(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function editorialPageForm(User $user, string $record, ?string $locale): array
    {
        $activeLocale = $this->normalizeLocale($locale);

        if ($record === 'login') {
            return $this->loginEditorialPageForm($user, $activeLocale);
        }

        $page = Page::query()
            ->with([
                'translations' => fn ($query) => $query->where('locale', $activeLocale),
                'blocks.translations' => fn ($query) => $query->where('locale', $activeLocale),
            ])
            ->when(
                ctype_digit($record),
                fn (Builder $builder) => $builder->whereKey($record),
                fn (Builder $builder) => $builder->where('slug', $record),
            )
            ->firstOrFail();

        $sections = $this->pageEditorialSections($page, $activeLocale);
        $pageLabel = $this->editorialPageLabel($page->slug);

        return [
            'mode' => 'edit',
            'module' => [
                'slug' => 'pages',
                'title' => 'Paginas',
                'singular' => 'Pagina',
                'group' => 'Editorial',
                'description' => 'Editor onepage centrado en pagina y componentes.',
            ],
            'recordLabel' => $pageLabel,
            'title' => 'Editar '.$pageLabel,
            'description' => 'Desde aqui se gobiernan solo los componentes reales de esta pagina. Cada componente abre su editor tipado sin exponer CRUD tecnico ni mezclar otras paginas.',
            'breadcrumbs' => [
                ['label' => 'Backoffice', 'href' => BackofficePath::active()],
                ['label' => 'Paginas', 'href' => BackofficePath::active('pages')],
                ['label' => $pageLabel, 'href' => null],
            ],
            'actions' => [
                [
                    'label' => 'Volver a paginas',
                    'href' => BackofficePath::active('pages'),
                    'variant' => 'ghost',
                    'visible' => true,
                ],
                ...$this->editorialPageLocaleActions($page->slug, $activeLocale),
            ],
            'summaryCards' => [
                ['label' => 'Pagina', 'value' => mb_strtoupper($pageLabel), 'tone' => 'cyan'],
                ['label' => 'Locale activo', 'value' => mb_strtoupper($activeLocale), 'tone' => 'fuchsia'],
                ['label' => 'Componentes', 'value' => (string) count($sections), 'tone' => 'emerald'],
                ['label' => 'Persistencia', 'value' => 'REAL', 'tone' => 'amber'],
            ],
            'form' => [
                'action' => $this->pageEditHref($page, $activeLocale),
                'method' => 'post',
                'submitLabel' => null,
                'defaults' => [],
                'sections' => [],
                'relationManagersTitle' => 'Componentes administrables de la pagina',
                'relationManagersDescription' => 'Cada tarjeta corresponde solo a esta pagina. Al abrir un item entras en el editor tipado del componente concreto para el idioma seleccionado.',
                'relationManagers' => $sections,
                'specialActions' => [],
                'dangerousActions' => [],
                'validationSummary' => [],
                'hideSubmit' => true,
            ],
            'capabilities' => [
                'canSubmit' => false,
                'canView' => $user->canViewBackofficeContent(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function loginEditorialPageForm(User $user, string $locale): array
    {
        return [
            'mode' => 'edit',
            'module' => [
                'slug' => 'pages',
                'title' => 'Paginas',
                'singular' => 'Pagina',
                'group' => 'Editorial',
                'description' => 'Editor onepage centrado en pagina y componentes.',
            ],
            'recordLabel' => 'Login',
            'title' => 'Editar Login',
            'description' => 'Superficie editorial del acceso privado: footer, enlaces sociales y modales legales compartidos con el login.',
            'breadcrumbs' => [
                ['label' => 'Backoffice', 'href' => BackofficePath::active()],
                ['label' => 'Paginas', 'href' => BackofficePath::active('pages')],
                ['label' => 'Login', 'href' => null],
            ],
            'actions' => [
                [
                    'label' => 'Volver a paginas',
                    'href' => BackofficePath::active('pages'),
                    'variant' => 'ghost',
                    'visible' => true,
                ],
                ...$this->editorialPageLocaleActions('login', $locale),
            ],
            'summaryCards' => [
                ['label' => 'Pagina', 'value' => 'LOGIN', 'tone' => 'cyan'],
                ['label' => 'Locale activo', 'value' => mb_strtoupper($locale), 'tone' => 'fuchsia'],
                ['label' => 'Secciones', 'value' => '3', 'tone' => 'emerald'],
                ['label' => 'Persistencia', 'value' => 'REAL', 'tone' => 'amber'],
            ],
            'form' => [
                'action' => BackofficePath::active('pages/login/edit').'?locale='.$locale,
                'method' => 'post',
                'submitLabel' => null,
                'defaults' => [],
                'sections' => [],
                'relationManagersTitle' => 'Componentes administrables de la pagina',
                'relationManagersDescription' => 'El login expone solo sus componentes operativos: footer, redes y modales legales.',
                'relationManagers' => $this->loginEditorialSections($locale),
                'specialActions' => [],
                'dangerousActions' => [],
                'validationSummary' => [],
                'hideSubmit' => true,
            ],
            'capabilities' => [
                'canSubmit' => false,
                'canView' => $user->canViewBackofficeContent(),
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function pageEditorialSections(Page $page, string $locale): array
    {
        $settings = Setting::query()
            ->whereIn('group', ['header', 'intro', 'footer', 'legal', 'media'])
            ->orWhere(fn (Builder $builder) => $builder->where('group', 'contact')->where('key', 'marquee_rows'))
            ->with(['translations' => fn ($query) => $query->where('locale', $locale)])
            ->get()
            ->keyBy(fn (Setting $setting) => $setting->group.'.'.$setting->key);

        return match ($page->slug) {
            'home' => [[
                'label' => 'Hero / Home',
                'description' => 'Componente hero del home. Aqui se editan el contenido base y cada slide del carrusel para el idioma seleccionado.',
                'items' => array_values(array_filter([
                    $this->pageTranslationItem($page, $locale, 'Contenido base', $page->slug),
                    ...$this->pageBlockItems($page->blocks->where('type', 'hero_slide')->values()->all(), $locale, 'Slide', $page->slug),
                ])),
            ]],
            'about' => [[
                'label' => 'About',
                'description' => 'Componente about. Aqui se editan el contenido base y los pasos del scrollytelling asociados a esta pagina.',
                'items' => array_values(array_filter([
                    $this->pageTranslationItem($page, $locale, 'Contenido base', $page->slug),
                    ...$this->pageBlockItems($page->blocks->where('type', 'about_step')->values()->all(), $locale, 'Paso', $page->slug),
                ])),
            ]],
            'music' => [[
                'label' => 'Music',
                'description' => 'Componente music. Aqui se editan el contenido base y el acceso al catalogo de tracks que alimenta esta pagina.',
                'items' => array_values(array_filter([
                    $this->pageTranslationItem($page, $locale, 'Contenido base', $page->slug),
                    $this->linkItem(
                        'music-index',
                        'Tracks musicales',
                        'Portadas, SoundCloud, orden editorial y traducciones musicales.',
                        BackofficePath::active('music-tracks')
                    ),
                ])),
            ]],
            'calendar' => [[
                'label' => 'Calendar',
                'description' => 'Componente calendar. Aqui se editan los textos de la pagina y el acceso al listado real de eventos.',
                'items' => array_values(array_filter([
                    $this->pageTranslationItem($page, $locale, 'Contenido base', $page->slug),
                    $this->linkItem(
                        'events-index',
                        'Eventos',
                        'Fechas, carteles, ubicacion y enlaces de compra.',
                        BackofficePath::active('events')
                    ),
                ])),
            ]],
            'media' => [[
                'label' => 'Media',
                'description' => 'Componente media. Aqui se editan los labels visibles y los recursos compartidos asociados a esta pagina.',
                'items' => array_values(array_filter([
                    $this->pageTranslationItem($page, $locale, 'Contenido base', $page->slug),
                    $this->settingTranslationItem($settings->get('media.youtube_channel_url'), $locale, 'Canal de YouTube', $page->slug),
                    $this->linkItem(
                        'media-assets-index',
                        'Assets multimedia',
                        'Fotos, videos y metadata tecnica del catalogo multimedia.',
                        BackofficePath::active('media-assets')
                    ),
                ])),
            ]],
            'contact' => [[
                'label' => 'Contact',
                'description' => 'Componente contact. Aqui se editan el copy base, las filas marquee y los modulos operativos que pertenecen a esta pagina.',
                'items' => array_values(array_filter([
                    $this->pageTranslationItem($page, $locale, 'Contenido base', $page->slug),
                    ...$this->pageBlockItems($page->blocks->where('type', 'contact_marquee')->values()->all(), $locale, 'Fila marquee', $page->slug),
                    $this->linkItem(
                        'social-links-index',
                        'Redes sociales',
                        'Fuente comun reutilizada por contacto, footer y superficies compartidas.',
                        BackofficePath::active('social-links')
                    ),
                    $this->linkItem(
                        'partners-index',
                        'Sponsors',
                        'Logos y enlaces de sponsors del carrusel.',
                        BackofficePath::active('partners')
                    ),
                ])),
            ]],
            default => [[
                'label' => 'Componentes',
                'description' => 'Lista de componentes administrables de esta pagina.',
                'items' => array_values(array_filter([
                    $this->pageTranslationItem($page, $locale, 'Contenido base', $page->slug),
                    ...$this->pageBlockItems($page->blocks->values()->all(), $locale, 'Componente', $page->slug),
                ])),
            ]],
        };
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function loginEditorialSections(string $locale): array
    {
        $settings = Setting::query()
            ->whereIn('group', ['footer', 'legal'])
            ->with(['translations' => fn ($query) => $query->where('locale', $locale)])
            ->get()
            ->keyBy(fn (Setting $setting) => $setting->group.'.'.$setting->key);

        $legalDocuments = LegalDocument::query()
            ->whereIn('slug', ['terms', 'privacy', 'cookies'])
            ->with(['translations' => fn ($query) => $query->where('locale', $locale)])
            ->orderBy('position')
            ->get()
            ->keyBy('slug');

        return [
            [
                'label' => 'Footer',
                'description' => 'Creditos visibles en desktop y mobile dentro del login.',
                'items' => array_values(array_filter([
                    $this->settingTranslationItem($settings->get('footer.credits'), $locale, 'Creditos del footer', 'login'),
                ])),
            ],
            [
                'label' => 'Redes sociales',
                'description' => 'Fuente comun visible tambien en el footer del login.',
                'items' => [
                    $this->linkItem(
                        'social-links-index',
                        'Redes sociales',
                        'Gestion centralizada de Facebook, Instagram, SoundCloud y Spotify.',
                        BackofficePath::active('social-links')
                    ),
                ],
            ],
            [
                'label' => 'Modales legales',
                'description' => 'Botones y contenido legal compartidos con el footer publico.',
                'items' => array_values(array_filter([
                    $this->settingTranslationItem($settings->get('legal.buttons'), $locale, 'Botones legales', 'login'),
                    $this->legalTranslationItem($legalDocuments->get('terms'), $locale, 'Terminos y condiciones', 'login'),
                    $this->legalTranslationItem($legalDocuments->get('privacy'), $locale, 'Politica de privacidad', 'login'),
                    $this->legalTranslationItem($legalDocuments->get('cookies'), $locale, 'Politica de cookies', 'login'),
                ])),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function pageTranslationSchema(Page $page): array
    {
        return match ($page->slug) {
            'home' => [
                'description' => 'Editor tipado del contenido base de `home`. Los slides del hero se editan desde sus propios bloques.',
                'fields' => [
                    $this->editorField('locale', 'Idioma', 'select', true, Phase6ModuleCatalog::localeOptions()),
                    $this->editorField('title', 'Titulo interno', 'text', true),
                    $this->editorField('meta_title', 'Meta title'),
                    $this->editorField('meta_description', 'Meta description', 'textarea'),
                    $this->editorField('home_name', 'Nombre de marca', 'text', false, [], 'Se guarda en `content.name`.', ['contentKey' => 'name']),
                    $this->editorField('home_title', 'Titulo principal', 'text', false, [], 'Se guarda en `content.title`.', ['contentKey' => 'title']),
                    $this->editorField('home_satisfied_customers', 'Texto clientes satisfechos', 'textarea', false, [], 'Se guarda en `content.satisfied_customers`.', ['contentKey' => 'satisfied_customers']),
                    $this->editorField('home_watch_resume', 'CTA ver CV', 'text', false, [], 'Se guarda en `content.watch_resume`.', ['contentKey' => 'watch_resume']),
                ],
                'validationSummary' => [
                    'Los campos tipados se serializan dentro de `page_translations.content`.',
                    'Los slides del hero mantienen su propia edicion por bloque.',
                ],
            ],
            'music' => [
                'description' => 'Editor tipado del bloque `music` para el locale seleccionado.',
                'fields' => [
                    $this->editorField('locale', 'Idioma', 'select', true, Phase6ModuleCatalog::localeOptions()),
                    $this->editorField('title', 'Titulo interno', 'text', true),
                    $this->editorField('meta_title', 'Meta title'),
                    $this->editorField('meta_description', 'Meta description', 'textarea'),
                    $this->editorField('music_title', 'Titulo visible', 'text', false, [], 'Se guarda en `content.title`.', ['contentKey' => 'title']),
                    $this->editorField('music_subtitle', 'Subtitulo', 'text', false, [], 'Se guarda en `content.subtitle`.', ['contentKey' => 'subtitle']),
                    $this->editorField('music_description', 'Descripcion', 'textarea', false, [], 'Se guarda en `content.description`.', ['contentKey' => 'description']),
                ],
                'validationSummary' => [
                    'El copy de la seccion musical se guarda en `page_translations.content`.',
                    'Los tracks mantienen su catalogo independiente en `music_tracks`.',
                ],
            ],
            'calendar' => [
                'description' => 'Editor tipado del bloque `calendar` para labels y textos del calendario.',
                'fields' => [
                    $this->editorField('locale', 'Idioma', 'select', true, Phase6ModuleCatalog::localeOptions()),
                    $this->editorField('title', 'Titulo interno', 'text', true),
                    $this->editorField('meta_title', 'Meta title'),
                    $this->editorField('meta_description', 'Meta description', 'textarea'),
                    $this->editorField('calendar_title', 'Titulo visible', 'text', false, [], 'Se guarda en `content.title`.', ['contentKey' => 'title']),
                    $this->editorField('calendar_buy_tickets', 'CTA comprar entradas', 'text', false, [], 'Se guarda en `content.buyTickets`.', ['contentKey' => 'buyTickets']),
                    $this->editorField('calendar_view_all', 'CTA ver todos los eventos', 'text', false, [], 'Se guarda en `content.viewAllEvents`.', ['contentKey' => 'viewAllEvents']),
                    $this->editorField('calendar_no_events', 'Texto sin eventos', 'textarea', false, [], 'Se guarda en `content.noEvents`.', ['contentKey' => 'noEvents']),
                ],
                'validationSummary' => [
                    'Los labels del calendario se guardan en `page_translations.content`.',
                    'Los eventos concretos se editan desde el modulo `events`.',
                ],
            ],
            'media' => [
                'description' => 'Editor tipado del bloque `media` y de sus labels visibles.',
                'fields' => [
                    $this->editorField('locale', 'Idioma', 'select', true, Phase6ModuleCatalog::localeOptions()),
                    $this->editorField('title', 'Titulo interno', 'text', true),
                    $this->editorField('meta_title', 'Meta title'),
                    $this->editorField('meta_description', 'Meta description', 'textarea'),
                    $this->editorField('media_title', 'Titulo visible', 'text', false, [], 'Se guarda en `content.title`.', ['contentKey' => 'title']),
                    $this->editorField('media_photos', 'Label fotos', 'text', false, [], 'Se guarda en `content.photos`.', ['contentKey' => 'photos']),
                    $this->editorField('media_videos', 'Label videos', 'text', false, [], 'Se guarda en `content.videos`.', ['contentKey' => 'videos']),
                    $this->editorField('media_photo_label', 'Label tarjeta foto', 'text', false, [], 'Se guarda en `content.photoLabel`.', ['contentKey' => 'photoLabel']),
                    $this->editorField('media_video_label', 'Label tarjeta video', 'text', false, [], 'Se guarda en `content.videoLabel`.', ['contentKey' => 'videoLabel']),
                    $this->editorField('media_youtube_cta', 'CTA YouTube', 'text', false, [], 'Se guarda en `content.viewMoreOnYoutube`.', ['contentKey' => 'viewMoreOnYoutube']),
                ],
                'validationSummary' => [
                    'Los labels visibles del media se guardan en `page_translations.content`.',
                    'El canal YouTube compartido se edita como setting independiente.',
                ],
            ],
            'contact' => [
                'description' => 'Editor tipado del bloque `contact` para el locale seleccionado.',
                'fields' => [
                    $this->editorField('locale', 'Idioma', 'select', true, Phase6ModuleCatalog::localeOptions()),
                    $this->editorField('title', 'Titulo interno', 'text', true),
                    $this->editorField('meta_title', 'Meta title'),
                    $this->editorField('meta_description', 'Meta description', 'textarea'),
                    $this->editorField('contact_title', 'Titulo visible', 'text', false, [], 'Se guarda en `content.title`.', ['contentKey' => 'title']),
                    $this->editorField('contact_subtitle', 'Subtitulo', 'textarea', false, [], 'Se guarda en `content.subtitle`.', ['contentKey' => 'subtitle']),
                    $this->editorField('contact_email_label', 'Label email', 'text', false, [], 'Se guarda en `content.email_label`.', ['contentKey' => 'email_label']),
                    $this->editorField('contact_phone_label', 'Label telefono', 'text', false, [], 'Se guarda en `content.phone_label`.', ['contentKey' => 'phone_label']),
                    $this->editorField('contact_get_direction', 'CTA obtener direccion', 'text', false, [], 'Se guarda en `content.get_direction`.', ['contentKey' => 'get_direction']),
                    $this->editorField('contact_form_title', 'Titulo del formulario', 'textarea', false, [], 'Se guarda en `content.form.title`.', ['contentKey' => 'form.title']),
                    $this->editorField('contact_submit_button', 'Texto boton enviar', 'text', false, [], 'Se guarda en `content.form.submit_button`.', ['contentKey' => 'form.submit_button']),
                    $this->editorField('contact_success_message', 'Mensaje de exito', 'textarea', false, [], 'Se guarda en `content.form.success_message`.', ['contentKey' => 'form.success_message']),
                    $this->editorField('contact_error_message', 'Mensaje de error', 'textarea', false, [], 'Se guarda en `content.form.error_message`.', ['contentKey' => 'form.error_message']),
                ],
                'validationSummary' => [
                    'El copy del bloque de contacto se guarda en `page_translations.content`.',
                    'Las redes, sponsors y filas marquee mantienen sus propios editores o modulos asociados.',
                ],
            ],
            default => [
                'description' => 'Editor tipado de la traduccion de pagina sobre el contrato existente del CMS.',
                'fields' => Phase6ModuleCatalog::pageTranslationForm()['fields'],
                'validationSummary' => [
                    'Locale: required, fijo en edit.',
                    'La traduccion se persiste sobre `page_translations`.',
                ],
            ],
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function pageBlockTranslationSchema(PageBlock $block): array
    {
        return match ($block->type) {
            'hero_slide' => [
                'description' => 'Editor tipado de un slide del hero. El texto cambia por idioma y las imagenes se comparten entre todos los idiomas.',
                'fields' => [
                    $this->editorField('locale', 'Idioma', 'select', true, Phase6ModuleCatalog::localeOptions()),
                    $this->editorField('title', 'Titulo', 'text', false, [], 'Se guarda en `content.title`.', ['contentKey' => 'title']),
                    $this->editorField('subtitle', 'Subtitulo', 'text', false, [], 'Se guarda en `content.subtitle`.', ['contentKey' => 'subtitle']),
                    $this->editorField('description', 'Descripcion', 'textarea', false, [], 'Se guarda en `content.description`.', ['contentKey' => 'description']),
                    $this->editorField('button_text', 'Texto del boton', 'text', false, [], 'Se guarda en `content.buttonText`.', ['contentKey' => 'buttonText']),
                    $this->editorField('link', 'URL del boton', 'url', false, [], 'Se guarda en `content.link`.', ['contentKey' => 'link']),
                    $this->editorField('event', 'Fecha o evento', 'text', false, [], 'Se guarda en `content.event`.', ['contentKey' => 'event']),
                    $this->editorField('logo', 'Logo', 'image', false, [], 'Se guarda en `page_blocks.settings.logo`.', ['settingsKey' => 'logo']),
                    $this->editorField('logo_position', 'Posicion del logo', 'select', false, [
                        ['value' => 'left', 'label' => 'left'],
                        ['value' => 'top', 'label' => 'top'],
                    ], 'Se guarda en `page_blocks.settings.logoPosition`.', ['settingsKey' => 'logoPosition']),
                    $this->editorField('person_image', 'Imagen principal', 'image', false, [], 'Se guarda en `page_blocks.settings.personImage`.', ['settingsKey' => 'personImage']),
                    $this->editorField('elipse_image', 'Imagen elipse', 'image', false, [], 'Se guarda en `page_blocks.settings.elipseImage`.', ['settingsKey' => 'elipseImage']),
                ],
                'validationSummary' => [
                    'Los textos del slide se guardan en `page_block_translations.content`.',
                    'Las imagenes y assets del slide se guardan en `page_blocks.settings`.',
                ],
            ],
            'about_step' => [
                'description' => 'Editor tipado de un paso del scrollytelling `about`.',
                'fields' => [
                    $this->editorField('locale', 'Idioma', 'select', true, Phase6ModuleCatalog::localeOptions()),
                    $this->editorField('title', 'Titulo', 'text', false, [], 'Se guarda en `content.title`.', ['contentKey' => 'title']),
                    $this->editorField('subtitle', 'Subtitulo', 'text', false, [], 'Se guarda en `content.subtitle`.', ['contentKey' => 'subtitle']),
                    $this->editorField('content_text', 'Contenido', 'textarea', false, [], 'Se guarda en `content.content`.', ['contentKey' => 'content']),
                    $this->editorField('chart_title', 'Chart title', 'text', false, [], 'Se guarda en `content.chartTitle`.', ['contentKey' => 'chartTitle']),
                    $this->editorField('chart_subtitle', 'Chart subtitle', 'text', false, [], 'Se guarda en `content.chartSubtitle`.', ['contentKey' => 'chartSubtitle']),
                    $this->editorField('image', 'Imagen principal', 'image', false, [], 'Se guarda en `page_blocks.settings.image`.', ['settingsKey' => 'image']),
                    $this->editorField('image_secondary', 'Imagen secundaria', 'image', false, [], 'Se guarda en `page_blocks.settings.image2`.', ['settingsKey' => 'image2']),
                ],
                'validationSummary' => [
                    'El copy traducido se guarda en `page_block_translations.content`.',
                    'Las imagenes compartidas se guardan en `page_blocks.settings`.',
                ],
            ],
            'contact_marquee' => [
                'description' => 'Editor tipado de una fila marquee del bloque contacto.',
                'fields' => [
                    $this->editorField('locale', 'Idioma', 'select', true, Phase6ModuleCatalog::localeOptions()),
                    $this->editorField('words_text', 'Palabras de la fila', 'textarea', false, [], 'Una palabra o frase por linea. Se guarda en `content.words`.', ['contentKey' => 'words']),
                ],
                'validationSummary' => [
                    'Cada linea se transforma en un array `content.words`.',
                    'La direccion del marquee sigue siendo un asset compartido del bloque.',
                ],
            ],
            default => [
                'description' => 'Editor de traduccion del bloque sobre el contrato actual del CMS.',
                'fields' => Phase6ModuleCatalog::pageBlockTranslationForm()['fields'],
                'validationSummary' => [
                    'Locale: required, fijo en edit.',
                    'La traduccion se persiste sobre `page_block_translations`.',
                ],
            ],
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function settingTranslationSchema(Setting $setting): array
    {
        return match ($setting->group.'.'.$setting->key) {
            'header.open_menu' => [
                'description' => 'Editor tipado del label de apertura del menu.',
                'fields' => [
                    $this->editorField('locale', 'Idioma', 'select', true, Phase6ModuleCatalog::localeOptions()),
                    $this->editorField('label', 'Label', 'text', false, [], 'Se guarda en `value.label`.', ['valueKey' => 'label']),
                ],
                'validationSummary' => ['El valor traducido se serializa en `settings_translations.value.label`.'],
            ],
            'header.menu' => [
                'description' => 'Editor tipado del menu principal de navegacion.',
                'fields' => [
                    $this->editorField('locale', 'Idioma', 'select', true, Phase6ModuleCatalog::localeOptions()),
                    $this->editorField('menu_home', 'Home', 'text', false, [], 'Se guarda en `value.items.home`.', ['valueKey' => 'items.home']),
                    $this->editorField('menu_about', 'About', 'text', false, [], 'Se guarda en `value.items.about`.', ['valueKey' => 'items.about']),
                    $this->editorField('menu_music', 'Music', 'text', false, [], 'Se guarda en `value.items.music`.', ['valueKey' => 'items.music']),
                    $this->editorField('menu_media', 'Media', 'text', false, [], 'Se guarda en `value.items.media`.', ['valueKey' => 'items.media']),
                    $this->editorField('menu_calendar', 'Calendar', 'text', false, [], 'Se guarda en `value.items.calendarEvents`.', ['valueKey' => 'items.calendarEvents']),
                    $this->editorField('menu_contact', 'Contact', 'text', false, [], 'Se guarda en `value.items.contact`.', ['valueKey' => 'items.contact']),
                ],
                'validationSummary' => ['Los labels se guardan dentro de `settings_translations.value.items`.'],
            ],
            'intro.welcome' => [
                'description' => 'Editor tipado del texto de bienvenida de la intro.',
                'fields' => [
                    $this->editorField('locale', 'Idioma', 'select', true, Phase6ModuleCatalog::localeOptions()),
                    $this->editorField('label', 'Texto de bienvenida', 'text', false, [], 'Se guarda en `value.label`.', ['valueKey' => 'label']),
                ],
                'validationSummary' => ['El valor traducido se serializa en `settings_translations.value.label`.'],
            ],
            'footer.credits' => [
                'description' => 'Editor tipado del footer compartido por home y login.',
                'fields' => [
                    $this->editorField('locale', 'Idioma', 'select', true, Phase6ModuleCatalog::localeOptions()),
                    $this->editorField('copyright', 'Copyright', 'text', false, [], 'Se guarda en `value.copyright`.', ['valueKey' => 'copyright']),
                    $this->editorField('rights', 'Texto derecho', 'text', false, [], 'Se guarda en `value.rights`.', ['valueKey' => 'rights']),
                ],
                'validationSummary' => ['Los creditos se guardan en `settings_translations.value`.'],
            ],
            'legal.buttons' => [
                'description' => 'Editor tipado de los botones de modales legales.',
                'fields' => [
                    $this->editorField('locale', 'Idioma', 'select', true, Phase6ModuleCatalog::localeOptions()),
                    $this->editorField('terms_button', 'Boton terminos', 'text', false, [], 'Se guarda en `value.terms_button`.', ['valueKey' => 'terms_button']),
                    $this->editorField('privacy_button', 'Boton privacidad', 'text', false, [], 'Se guarda en `value.privacy_button`.', ['valueKey' => 'privacy_button']),
                    $this->editorField('cookies_button', 'Boton cookies', 'text', false, [], 'Se guarda en `value.cookies_button`.', ['valueKey' => 'cookies_button']),
                ],
                'validationSummary' => ['Los labels legales se guardan en `settings_translations.value`.'],
            ],
            'media.youtube_channel_url' => [
                'description' => 'Editor tipado de la URL compartida del canal de YouTube.',
                'fields' => [
                    $this->editorField('locale', 'Idioma', 'select', true, Phase6ModuleCatalog::localeOptions()),
                    $this->editorField('value', 'URL YouTube', 'url', false, [], 'Se guarda en `value.value`.', ['valueKey' => 'value']),
                ],
                'validationSummary' => ['La URL del canal se serializa en `settings_translations.value.value`.'],
            ],
            default => [
                'description' => 'Editor de traduccion de setting sobre el contrato actual del CMS.',
                'fields' => Phase6ModuleCatalog::settingTranslationForm()['fields'],
                'validationSummary' => [
                    'Locale: required, fijo en edit.',
                    'La traduccion se persiste sobre `settings_translations`.',
                ],
            ],
        };
    }

    /**
     * @param  array<int, PageBlock>  $blocks
     * @return array<int, array<string, mixed>>
     */
    private function pageBlockItems(array $blocks, string $locale, string $prefix, ?string $from = null): array
    {
        return collect($blocks)
            ->values()
            ->map(function (PageBlock $block, int $index) use ($locale, $prefix, $from): array {
                /** @var PageBlockTranslation|null $translation */
                $translation = $block->translations->firstWhere('locale', $locale);
                $title = data_get($translation?->content, 'title');

                return [
                    'id' => 'block-'.$block->getKey(),
                    'label' => sprintf('%s %02d', $prefix, $index + 1),
                    'meta' => trim(($title ?: $block->key).' · '.($translation instanceof PageBlockTranslation ? 'traduccion '.$locale : 'sin traduccion '.$locale)),
                    'href' => $this->pageBlockTranslationHref($block, $locale, $from),
                ];
            })
            ->all();
    }

    private function pageTranslationItem(?Page $page, string $locale, string $label, ?string $from = null): ?array
    {
        if (! $page instanceof Page) {
            return null;
        }

        /** @var PageTranslation|null $translation */
        $translation = $page->translations->firstWhere('locale', $locale);

        return [
            'id' => 'page-'.$page->getKey(),
            'label' => $label,
            'meta' => $translation instanceof PageTranslation
                ? ($translation->title ?: 'Traduccion '.$locale)
                : 'Sin traduccion '.mb_strtoupper($locale),
            'href' => $this->pageTranslationHref($page, $locale, $from),
        ];
    }

    private function settingTranslationItem(?Setting $setting, string $locale, string $label, ?string $from = null): ?array
    {
        if (! $setting instanceof Setting) {
            return null;
        }

        /** @var SettingTranslation|null $translation */
        $translation = $setting->translations->firstWhere('locale', $locale);

        return [
            'id' => 'setting-'.$setting->getKey(),
            'label' => $label,
            'meta' => $translation instanceof SettingTranslation
                ? 'Traduccion '.mb_strtoupper($locale).' disponible'
                : 'Sin traduccion '.mb_strtoupper($locale),
            'href' => $this->settingTranslationHref($setting, $locale, $from),
        ];
    }

    private function legalTranslationItem(?LegalDocument $document, string $locale, string $label, ?string $from = null): ?array
    {
        if (! $document instanceof LegalDocument) {
            return null;
        }

        /** @var LegalDocumentTranslation|null $translation */
        $translation = $document->translations->firstWhere('locale', $locale);

        return [
            'id' => 'legal-'.$document->getKey(),
            'label' => $label,
            'meta' => $translation instanceof LegalDocumentTranslation
                ? ($translation->title ?: 'Traduccion '.mb_strtoupper($locale))
                : 'Sin traduccion '.mb_strtoupper($locale),
            'href' => $this->legalTranslationHref($document, $locale, $from),
        ];
    }

    private function linkItem(string $id, string $label, string $meta, string $href): array
    {
        return [
            'id' => $id,
            'label' => $label,
            'meta' => $meta,
            'href' => $href,
        ];
    }

    private function pageTranslationHref(Page $page, string $locale, ?string $from = null): string
    {
        /** @var PageTranslation|null $translation */
        $translation = $page->translations->firstWhere('locale', $locale);

        return $this->withEditorQuery(
            $translation instanceof PageTranslation
                ? BackofficePath::active('pages/'.$page->slug.'/translations/'.$translation->getKey().'/edit')
                : BackofficePath::active('pages/'.$page->slug.'/translations/create'),
            $locale,
            $from,
        );
    }

    private function pageBlockTranslationHref(PageBlock $block, string $locale, ?string $from = null): string
    {
        if ($block->page instanceof Page) {
            return $this->pageComponentEditHref($block, $locale, $from);
        }

        return $this->withEditorQuery(
            BackofficePath::active('page-blocks/'.$block->getKey().'/translations/create'),
            $locale,
            $from,
        );
    }

    private function pageEditHref(Page $page, string $locale): string
    {
        return $this->withEditorQuery(
            BackofficePath::active('pages/'.$page->slug.'/edit'),
            $this->normalizeLocale($locale),
        );
    }

    private function pageComponentEditHref(PageBlock $block, string $locale, ?string $from = null): string
    {
        return $this->withEditorQuery(
            BackofficePath::active('pages/'.$block->page->slug.'/components/'.$block->key.'/edit'),
            $this->normalizeLocale($locale),
            $from,
        );
    }

    private function settingTranslationHref(Setting $setting, string $locale, ?string $from = null): string
    {
        /** @var SettingTranslation|null $translation */
        $translation = $setting->translations->firstWhere('locale', $locale);

        return $this->withEditorQuery(
            $translation instanceof SettingTranslation
                ? BackofficePath::active('settings/'.$setting->getKey().'/translations/'.$translation->getKey().'/edit')
                : BackofficePath::active('settings/'.$setting->getKey().'/translations/create'),
            $locale,
            $from,
        );
    }

    private function legalTranslationHref(LegalDocument $document, string $locale, ?string $from = null): string
    {
        /** @var LegalDocumentTranslation|null $translation */
        $translation = $document->translations->firstWhere('locale', $locale);

        return $this->withEditorQuery(
            $translation instanceof LegalDocumentTranslation
                ? BackofficePath::active('legal-documents/'.$document->getKey().'/translations/'.$translation->getKey().'/edit')
                : BackofficePath::active('legal-documents/'.$document->getKey().'/translations/create'),
            $locale,
            $from,
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function editorialPageLocaleActions(string $record, string $activeLocale): array
    {
        return collect(BackofficeLocales::values())
            ->map(fn (string $locale): array => [
                'label' => mb_strtoupper($locale),
                'href' => $record === 'login'
                    ? BackofficePath::active('pages/login/edit').'?locale='.$locale
                    : BackofficePath::active('pages/'.$record.'/edit').'?locale='.$locale,
                'variant' => $locale === $activeLocale ? 'primary' : 'secondary',
                'visible' => true,
            ])
            ->all();
    }

    private function normalizeLocale(?string $locale): string
    {
        return is_string($locale) && in_array($locale, BackofficeLocales::values(), true)
            ? $locale
            : 'es';
    }

    private function editorialPageLabel(string $slug): string
    {
        return match ($slug) {
            'home' => 'Home',
            'about' => 'About',
            'music' => 'Music',
            'calendar' => 'Calendar',
            'media' => 'Media',
            'contact' => 'Contact',
            default => Str::headline($slug),
        };
    }

    private function editorialPageSortWeight(string $slug): int
    {
        return match ($slug) {
            'home' => 10,
            'about' => 20,
            'music' => 30,
            'calendar' => 40,
            'media' => 50,
            'contact' => 60,
            default => 100,
        };
    }

    private function mappedArrayValue(mixed $payload, string $key): mixed
    {
        if (! is_array($payload)) {
            return null;
        }

        if (array_key_exists($key, $payload)) {
            return $payload[$key];
        }

        return data_get($payload, $key);
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    private function editorialIndexQueryState(array $query, int $perPage): array
    {
        return [
            'search' => is_string($query['search'] ?? null) ? $query['search'] : '',
            'sort' => is_string($query['sort'] ?? null) ? $query['sort'] : '',
            'direction' => in_array($query['direction'] ?? null, ['asc', 'desc'], true) ? $query['direction'] : 'desc',
            'perPage' => (int) ($query['perPage'] ?? $perPage),
            'page' => (int) ($query['page'] ?? 1),
            'filters' => is_array($query['filters'] ?? null) ? $query['filters'] : [],
        ];
    }

    private function withEditorQuery(string $href, ?string $locale = null, mixed $from = null): string
    {
        $query = array_filter([
            'locale' => is_string($locale) && $locale !== '' ? $locale : null,
            'from' => is_string($from) && $from !== '' ? $from : null,
        ], fn (mixed $value): bool => $value !== null);

        if ($query === []) {
            return $href;
        }

        return $href.'?'.http_build_query($query);
    }

    /**
     * @param  array<int, array<string, string>>  $options
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private function editorField(string $key, string $label, string $type = 'text', bool $required = false, array $options = [], string $help = '', array $extra = []): array
    {
        return array_merge([
            'key' => $key,
            'label' => $label,
            'type' => $type,
            'surface' => 'input',
            'required' => $required,
            'options' => $options,
            'help' => $help !== '' ? $help : 'Campo tipado alineado con la persistencia real del CMS.',
        ], $extra);
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
                'Fuente comun compartida por contacto y footer.',
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
        return $slug !== 'pages'
            && ! Phase6ModuleCatalog::isReadOnly($slug)
            && $user->canManageBackofficeContent();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function localeHeaderActionsFor(string $slug, ?Model $record): array
    {
        if ($slug !== 'pages' || ! $record instanceof Page) {
            return [];
        }

        return $this->pageTranslationHeaderActions($record, null);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function pageTranslationHeaderActions(Page $page, ?PageTranslation $currentTranslation): array
    {
        $translations = $page->translations()
            ->get()
            ->keyBy('locale');

        return collect(BackofficeLocales::values())
            ->map(function (string $locale) use ($page, $translations, $currentTranslation): array {
                /** @var PageTranslation|null $translation */
                $translation = $translations->get($locale);
                $isActive = $currentTranslation?->locale === $locale;

                return [
                    'label' => mb_strtoupper($locale).($translation instanceof PageTranslation ? '' : ' +'),
                    'href' => $this->pageTranslationHref($page, $locale, request()->query('from')),
                    'variant' => $isActive ? 'primary' : ($translation instanceof PageTranslation ? 'secondary' : 'ghost'),
                    'visible' => true,
                ];
            })
            ->values()
            ->all();
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
    private function translationDefaults(array $fields, ?Model $translation, array $oldInput, ?string $preferredLocale = null): array
    {
        $defaults = [];

        foreach ($fields as $field) {
            $value = match (true) {
                isset($field['contentKey']) => $this->mappedArrayValue($translation?->getAttribute('content') ?? [], $field['contentKey']),
                isset($field['valueKey']) => $this->mappedArrayValue($translation?->getAttribute('value') ?? [], $field['valueKey']),
                isset($field['settingsKey']) => $this->mappedArrayValue($translation instanceof PageBlockTranslation ? ($translation->pageBlock?->settings ?? []) : [], $field['settingsKey']),
                default => $translation?->getAttribute($field['key']),
            };

            if (($field['contentKey'] ?? null) === 'words' && is_array($value)) {
                $value = implode(PHP_EOL, $value);
            }

            if (is_array($value)) {
                $value = $field['type'] === 'json'
                    ? json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                    : implode(PHP_EOL, $value);
            }

            $defaults[$field['key']] = $value ?? ($field['type'] === 'json' ? '' : '');
        }

        $defaults = array_merge($defaults, Arr::except($oldInput, ['_token']));

        if (($defaults['locale'] ?? '') === '' && $translation === null && is_string($preferredLocale) && in_array($preferredLocale, BackofficeLocales::values(), true)) {
            $defaults['locale'] = $preferredLocale;
        }

        return $defaults;
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
        array $actions = [],
        array $validationSummary = [],
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
                ...$actions,
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
                'mediaLibrary' => $this->hasImageFields([['fields' => $fields]]) ? $this->imageLibrary() : [],
                'mediaUploadUrl' => $this->hasImageFields([['fields' => $fields]]) ? BackofficePath::active('media-assets/uploads/images') : null,
                'relationManagers' => $relationManagers,
                'specialActions' => [],
                'dangerousActions' => [],
                'validationSummary' => $validationSummary,
            ],
            'capabilities' => [
                'canSubmit' => $user->canManageBackofficeContent(),
                'canView' => $user->canViewBackofficeContent(),
            ],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $sections
     */
    private function hasImageFields(array $sections): bool
    {
        foreach ($sections as $section) {
            foreach (($section['fields'] ?? []) as $field) {
                if (($field['type'] ?? null) === 'image') {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function imageLibrary(): array
    {
        return MediaAsset::query()
            ->orderByDesc('updated_at')
            ->get()
            ->filter(function (MediaAsset $asset): bool {
                $mimeType = (string) ($asset->mime_type ?? '');
                $path = (string) $asset->path;

                return Str::startsWith($mimeType, 'image/')
                    || preg_match('/\.(png|jpe?g|webp|gif|svg|avif)$/i', $path) === 1;
            })
            ->map(function (MediaAsset $asset): array {
                return [
                    'id' => (string) $asset->getKey(),
                    'path' => (string) $asset->path,
                    'previewUrl' => $this->normalizeMediaPath((string) $asset->path),
                    'filename' => (string) $asset->filename,
                    'altText' => (string) ($asset->alt_text ?? ''),
                    'mimeType' => (string) ($asset->mime_type ?? ''),
                    'width' => $asset->width,
                    'height' => $asset->height,
                ];
            })
            ->values()
            ->all();
    }

    private function normalizeMediaPath(string $path): string
    {
        if ($path === '') {
            return '';
        }

        if (Str::startsWith($path, ['http://', 'https://', 'data:', '/'])) {
            return $path;
        }

        return '/'.ltrim($path, '/');
    }
}

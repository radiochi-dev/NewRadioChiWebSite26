<?php

namespace App\Actions\Backoffice;

use App\Models\User;
use App\Support\Backoffice\BackofficePath;
use App\Support\Backoffice\CrudModuleBlueprintFactory;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BuildBackofficeCrudModulePayloadAction
{
    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function index(User $user, string $slug, array $query = []): array
    {
        $blueprint = CrudModuleBlueprintFactory::make($slug);
        $module = $blueprint['module'];
        $normalizedQuery = $this->normalizeIndexQuery($blueprint, $query);
        $rows = $this->applyQuery($blueprint['sampleRows'], $blueprint['columns'], $blueprint['filters'], $normalizedQuery);
        $pagination = $this->paginate($rows, (int) $normalizedQuery['page'], (int) $normalizedQuery['perPage']);
        $rowActions = $this->rowActions($module['slug'], $user);

        return [
            'mode' => 'index',
            'module' => $module,
            'title' => $module['title'],
            'description' => $module['description'],
            'breadcrumbs' => [
                ['label' => 'Backoffice', 'href' => BackofficePath::active()],
                ['label' => $module['group'], 'href' => null],
                ['label' => $module['title'], 'href' => BackofficePath::active($module['slug'])],
            ],
            'actions' => [
                [
                    'label' => 'Nueva '.$module['singular'],
                    'href' => BackofficePath::active($module['slug'].'/create'),
                    'variant' => 'primary',
                    'visible' => $user->canManageBackofficeContent(),
                ],
            ],
            'summaryCards' => [
                ['label' => 'Columnas', 'value' => (string) count($blueprint['columns']), 'tone' => 'cyan'],
                ['label' => 'Filtros', 'value' => (string) count($blueprint['filters']), 'tone' => 'fuchsia'],
                ['label' => 'Acciones fila', 'value' => (string) count($this->rowActions($module['slug'], $user)), 'tone' => 'emerald'],
                ['label' => 'Bulk actions', 'value' => (string) count($this->bulkActions($user)), 'tone' => 'amber'],
            ],
            'filters' => $this->filterPayload($blueprint['filters'], $normalizedQuery),
            'table' => [
                'path' => BackofficePath::active($module['slug']),
                'query' => $normalizedQuery,
                'columns' => $blueprint['columns'],
                'rows' => array_map(function (array $row) use ($rowActions): array {
                    $row['actions'] = $rowActions;

                    return $row;
                }, $pagination['rows']),
                'emptyState' => [
                    'title' => 'Infraestructura CRUD conectada',
                    'description' => 'La superficie oficial ya expone patron de filtros, ordenacion, acciones fila/masivas, paginacion y payload estable para cada modulo.',
                    'ctaLabel' => $user->canManageBackofficeContent() ? 'Abrir formulario base' : null,
                    'ctaHref' => $user->canManageBackofficeContent() ? BackofficePath::active($module['slug'].'/create') : null,
                ],
                'bulkActions' => $this->bulkActions($user),
                'sort' => [
                    'column' => $normalizedQuery['sort'],
                    'direction' => $normalizedQuery['direction'],
                ],
                'pagination' => $pagination['meta'],
            ],
            'capabilities' => [
                'canCreate' => $user->canManageBackofficeContent(),
                'canEdit' => $user->canManageBackofficeContent(),
                'canDelete' => $user->canManageBackofficeContent(),
                'canView' => $user->canViewBackofficeContent(),
            ],
            'testChecklist' => $blueprint['testChecklist'],
        ];
    }

    /**
     * @param  array<string, mixed>  $oldInput
     * @return array<string, mixed>
     */
    public function form(User $user, string $slug, string $mode, ?string $record = null, array $oldInput = []): array
    {
        $blueprint = CrudModuleBlueprintFactory::make($slug);
        $module = $blueprint['module'];
        $modeLabel = $mode === 'edit' ? 'Editar' : 'Crear';

        return [
            'mode' => $mode,
            'module' => $module,
            'recordLabel' => $record,
            'title' => $modeLabel.' '.$module['singular'],
            'description' => 'Formulario reusable del backoffice con patron `Index / Create / Edit / Form`, validacion servidor via FormRequest, errores Inertia y paneles de relaciones, uploads y acciones especiales.',
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
                ['label' => 'Campos input', 'value' => (string) collect($blueprint['inputSections'])->sum(fn (array $section): int => count($section['fields'])), 'tone' => 'fuchsia'],
                ['label' => 'Relations', 'value' => (string) count($blueprint['relationManagers']), 'tone' => 'emerald'],
                ['label' => 'Validacion', 'value' => 'FormRequest', 'tone' => 'amber'],
            ],
            'form' => [
                'action' => BackofficePath::active($module['slug'].'/draft'.($mode === 'edit' && $record ? '/'.$record : '')),
                'method' => 'post',
                'defaults' => $this->defaults($blueprint, $oldInput, $mode),
                'sections' => $blueprint['inputSections'],
                'relationManagers' => $blueprint['relationManagers'],
                'specialActions' => $this->actionPayload($module['slug'], $blueprint['specialActions'], $record),
                'dangerousActions' => $this->actionPayload($module['slug'], $blueprint['dangerousActions'], $record),
                'validationSummary' => [],
                'testChecklist' => $blueprint['testChecklist'],
            ],
            'capabilities' => [
                'canSubmit' => $user->canManageBackofficeContent(),
                'canView' => $user->canViewBackofficeContent(),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    private function normalizeIndexQuery(array $blueprint, array $query): array
    {
        $allowedSorts = array_map(fn (array $column): string => $column['key'], $blueprint['columns']);
        $sort = in_array((string) Arr::get($query, 'sort', $blueprint['columns'][0]['key']), $allowedSorts, true)
            ? (string) Arr::get($query, 'sort', $blueprint['columns'][0]['key'])
            : $blueprint['columns'][0]['key'];

        return [
            'search' => trim((string) Arr::get($query, 'search', '')),
            'sort' => $sort,
            'direction' => Arr::get($query, 'direction') === 'asc' ? 'asc' : 'desc',
            'page' => max(1, (int) Arr::get($query, 'page', 1)),
            'perPage' => max(1, min(50, (int) Arr::get($query, 'perPage', 10))),
            'filters' => collect($blueprint['filters'])
                ->mapWithKeys(fn (array $filter): array => [
                    $filter['key'] => (string) Arr::get($query, 'filters.'.$filter['key'], ''),
                ])
                ->all(),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $filters
     * @param  array<string, mixed>  $query
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
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<int, array<string, mixed>>  $columns
     * @param  array<int, array<string, mixed>>  $filters
     * @param  array<string, mixed>  $query
     * @return array<int, array<string, mixed>>
     */
    private function applyQuery(array $rows, array $columns, array $filters, array $query): array
    {
        $collection = collect($rows);
        $search = Str::lower((string) $query['search']);

        if ($search !== '') {
            $collection = $collection->filter(function (array $row) use ($search): bool {
                return Str::contains(Str::lower(implode(' ', array_map('strval', $row))), $search);
            });
        }

        foreach ($filters as $filter) {
            $value = (string) Arr::get($query, 'filters.'.$filter['key'], '');

            if ($value === '' || $value === 'Todos') {
                continue;
            }

            $collection = $collection->filter(function (array $row) use ($filter, $value): bool {
                return Str::contains(Str::lower((string) Arr::get($row, $filter['key'], '')), Str::lower($value));
            });
        }

        $sorted = $collection->sortBy(
            fn (array $row): string => Str::lower((string) Arr::get($row, $query['sort'], '')),
            SORT_NATURAL,
            $query['direction'] === 'desc'
        )->values();

        $columnsByKey = collect($columns)->keyBy('key');

        return $sorted->map(function (array $row) use ($columnsByKey): array {
            $row['cells'] = $columnsByKey->map(function (array $column, string $key) use ($row): array {
                return [
                    'key' => $key,
                    'value' => Arr::get($row, $key, '—'),
                    'badge' => $column['badge'],
                    'align' => $column['align'],
                ];
            })->values()->all();

            return $row;
        })->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array{rows: array<int, array<string, mixed>>, meta: array<string, int>}
     */
    private function paginate(array $rows, int $page, int $perPage): array
    {
        $collection = collect($rows);
        $total = $collection->count();
        $totalPages = max(1, (int) ceil($total / max($perPage, 1)));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        return [
            'rows' => $collection->slice($offset, $perPage)->values()->all(),
            'meta' => [
                'currentPage' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'totalPages' => $totalPages,
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function rowActions(string $slug, User $user): array
    {
        return array_values(array_filter([
            [
                'label' => 'Editar',
                'variant' => 'ghost',
                'enabled' => $user->canManageBackofficeContent(),
                'href' => BackofficePath::active($slug.'/sample-record/edit'),
            ],
            [
                'label' => 'Duplicar',
                'variant' => 'ghost',
                'enabled' => $user->canManageBackofficeContent(),
                'href' => null,
            ],
            [
                'label' => 'Ver',
                'variant' => 'secondary',
                'enabled' => true,
                'href' => BackofficePath::active($slug.'/sample-record/edit'),
            ],
        ], fn (array $action): bool => $action['enabled'] || $action['label'] === 'Ver'));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function bulkActions(User $user): array
    {
        if (! $user->canManageBackofficeContent()) {
            return [];
        }

        return [
            ['label' => 'Publicar seleccion', 'variant' => 'secondary', 'enabled' => true],
            ['label' => 'Desactivar seleccion', 'variant' => 'ghost', 'enabled' => true],
            ['label' => 'Borrado controlado', 'variant' => 'ghost', 'enabled' => true],
        ];
    }

    /**
     * @param  array<string, mixed>  $blueprint
     * @param  array<string, mixed>  $oldInput
     * @return array<string, mixed>
     */
    private function defaults(array $blueprint, array $oldInput, string $mode): array
    {
        $defaults = $blueprint['defaultValues'];

        if ($mode === 'edit') {
            foreach ($defaults as $key => $value) {
                $defaults[$key] = match (true) {
                    is_bool($value) => true,
                    $value === null => null,
                    Str::contains($key, ['slug']) => 'sample-edit-record',
                    Str::contains($key, ['title', 'name', 'subject', 'label']) => 'Valor de ejemplo',
                    Str::contains($key, ['status']) => 'draft',
                    Str::contains($key, ['locale']) => 'es',
                    Str::contains($key, ['url']) => 'https://example.com',
                    Str::contains($key, ['email']) => 'editor@radiochi.test',
                    Str::contains($key, ['position', 'count', 'size', 'width', 'height', 'year']) => 1,
                    default => '',
                };
            }
        }

        return array_merge($defaults, Arr::except($oldInput, ['_token']));
    }

    /**
     * @param  array<int, array<string, mixed>>  $actions
     * @return array<int, array<string, mixed>>
     */
    private function actionPayload(string $moduleSlug, array $actions, ?string $record): array
    {
        return array_map(function (array $action) use ($moduleSlug, $record): array {
            $action['endpoint'] = BackofficePath::active("{$moduleSlug}/actions/{$action['slug']}");
            $action['record'] = $record;

            return $action;
        }, $actions);
    }

    /**
     * @param  array<string, array<int, string>>  $rules
     * @return array<int, string>
     */
    private function validationSummary(array $rules): array
    {
        return collect($rules)
            ->map(fn (array $fieldRules, string $field): string => Str::headline($field).': '.implode(', ', $fieldRules))
            ->values()
            ->all();
    }
}

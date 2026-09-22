<?php

namespace App\Support\Backoffice;

use Illuminate\Support\Str;

final class CrudModuleBlueprintFactory
{
    /**
     * @return array<string, mixed>
     */
    public static function make(string $slug): array
    {
        $module = PreviewModuleRegistry::find($slug);

        abort_if(! is_array($module), 404);

        $columns = array_map(
            fn (string $column): array => self::normalizeColumn($column),
            $module['columns']
        );

        $filters = array_map(
            fn (string $filter): array => self::normalizeFilter($filter),
            $module['filters']
        );

        $formSections = array_map(
            fn (array $section): array => [
                'title' => $section['title'],
                'fields' => array_map(
                    fn (string $field): array => self::normalizeField($field, $module['slug']),
                    $section['fields']
                ),
            ],
            $module['formSections']
        );

        $inputSections = array_map(function (array $section): array {
            return [
                'title' => $section['title'],
                'fields' => array_values(array_filter(
                    $section['fields'],
                    fn (array $field): bool => $field['surface'] === 'input'
                )),
            ];
        }, $formSections);

        return [
            'module' => $module,
            'columns' => $columns,
            'filters' => $filters,
            'formSections' => $formSections,
            'inputSections' => array_values(array_filter(
                $inputSections,
                fn (array $section): bool => count($section['fields']) > 0
            )),
            'relationManagers' => self::relationManagers($module['slug']),
            'dangerousActions' => self::dangerousActions($module['slug']),
            'specialActions' => self::specialActions($module['slug']),
            'testChecklist' => self::testChecklist($module['slug']),
            'validationRules' => self::validationRulesFromSections($formSections),
            'defaultValues' => self::defaultValues($formSections),
            'sampleRows' => self::sampleRows($module['slug'], $module['title'], $columns),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function findAction(string $moduleSlug, string $actionSlug): ?array
    {
        $blueprint = self::make($moduleSlug);

        foreach (array_merge($blueprint['specialActions'], $blueprint['dangerousActions']) as $action) {
            if ($action['slug'] === $actionSlug) {
                return $action;
            }
        }

        return null;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function validationRules(string $slug): array
    {
        return self::make($slug)['validationRules'];
    }

    /**
     * @param  array<int, array<string, mixed>>  $formSections
     * @return array<string, array<int, string>>
     */
    private static function validationRulesFromSections(array $formSections): array
    {
        $rules = [];

        foreach ($formSections as $section) {
            foreach ($section['fields'] as $field) {
                if ($field['surface'] !== 'input') {
                    continue;
                }

                $rules[$field['key']] = $field['validation'];
            }
        }

        return $rules;
    }

    /**
     * @param  array<int, array<string, mixed>>  $formSections
     * @return array<string, mixed>
     */
    private static function defaultValues(array $formSections): array
    {
        $defaults = [];

        foreach ($formSections as $section) {
            foreach ($section['fields'] as $field) {
                if ($field['surface'] !== 'input') {
                    continue;
                }

                $defaults[$field['key']] = $field['default'];
            }
        }

        return $defaults;
    }

    /**
     * @return array<string, mixed>
     */
    private static function normalizeColumn(string $column): array
    {
        $key = Str::slug($column, '_');

        return [
            'key' => $key,
            'label' => $column,
            'sortable' => true,
            'badge' => self::isBadgeColumn($key),
            'align' => self::isNumericColumn($key) ? 'right' : 'left',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function normalizeFilter(string $filter): array
    {
        $key = Str::slug($filter, '_');

        return [
            'key' => $key,
            'label' => $filter,
            'kind' => 'select',
            'placeholder' => 'Todos',
            'options' => self::filterOptions($key),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function normalizeField(string $field, string $moduleSlug): array
    {
        $key = Str::slug($field, '_');
        $normalized = Str::lower($field);
        $type = 'text';
        $surface = 'input';
        $help = 'Campo base del patron CRUD reusable.';
        $validation = ['nullable', 'string', 'max:255'];
        $options = [];

        if (Str::contains($normalized, ['traducciones', 'bloques'])) {
            $surface = 'relation';
            $type = 'manager';
            $validation = ['nullable', 'array'];
            $help = 'Este dominio se gestionara con relation managers/componentes compuestos en las fases modulares.';
        } elseif (Str::contains($normalized, ['pagina', 'entity type', 'entity id', 'attachable type', 'attachable id'])) {
            $surface = 'relation';
            $type = 'relation';
            $validation = ['nullable', 'array'];
            $help = 'Patron de relacion controlado desde payloads Inertia y selects asincronos.';
        } elseif (Str::contains($normalized, ['cover', 'logo', 'label image', 'file', 'path', 'mime', 'alt text'])) {
            $type = 'file';
            $help = 'Patron de upload con vista previa local, validacion backend y metadatos.';
            $validation = ['nullable', 'file', 'max:5120'];
        } elseif (Str::contains($normalized, ['metadata', 'settings', 'json', 'open graph', 'twitter', 'json-ld', 'content', 'html'])) {
            $type = 'json';
            $help = 'Patron para payloads JSON estructurados con validacion servidor y feedback en formulario.';
            $validation = ['nullable', 'string'];
        } elseif (Str::contains($normalized, ['descripcion', 'description', 'summary', 'notes', 'value'])) {
            $type = 'textarea';
            $help = 'Campo de texto largo para contenido editorial o notas operativas.';
            $validation = ['nullable', 'string'];
        } elseif (Str::contains($normalized, ['publicado', 'activo', 'destacado', 'traducible', 'publico'])) {
            $type = 'toggle';
            $help = 'Patron booleano con feedback inmediato en index, formulario y acciones masivas.';
            $validation = ['nullable', 'boolean'];
        } elseif (Str::contains($normalized, ['status', 'tipo', 'type', 'locale', 'plataforma', 'group', 'template', 'disk', 'collection', 'http status', 'ubicacion'])) {
            $type = 'select';
            $help = 'Select normalizado con opciones fijas, validacion `in:` y estado via querystring.';
            $options = self::fieldOptions($key, $moduleSlug);
            $validation = ['required', 'string', 'in:'.implode(',', array_column($options, 'value'))];
        } elseif (Str::contains($normalized, ['inicio', 'fin', 'fecha', 'scheduled', 'sent at', 'subscribed', 'unsubscribed'])) {
            $type = 'datetime';
            $help = 'Patron datetime compatible con validacion Laravel e input Inertia.';
            $validation = ['nullable', 'date'];
        } elseif (Str::contains($normalized, ['email'])) {
            $type = 'email';
            $help = 'Campo email con validacion dedicada en FormRequest.';
            $validation = ['required', 'email'];
        } elseif (Str::contains($normalized, ['url', 'website'])) {
            $type = 'url';
            $help = 'Campo URL con validacion y sanitizacion en backend.';
            $validation = ['nullable', 'url'];
        } elseif (Str::contains($normalized, ['year', 'posicion', 'position', 'count', 'size', 'width', 'height', 'id'])) {
            $type = 'number';
            $help = 'Campo numerico normalizado para orden, metrica o relacion.';
            $validation = ['nullable', 'integer'];
        }

        if (Str::contains($key, ['slug', 'title', 'nombre', 'name', 'subject', 'label'])) {
            $validation = ['required', 'string', 'max:255'];
        }

        return [
            'key' => $key,
            'label' => $field,
            'type' => $type,
            'surface' => $surface,
            'help' => $help,
            'required' => in_array('required', $validation, true),
            'validation' => $validation,
            'default' => self::defaultValueForType($type),
            'options' => $options,
            'accept' => $type === 'file' ? '.jpg,.jpeg,.png,.webp,.pdf,.svg,.json' : null,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function sampleRows(string $moduleSlug, string $moduleTitle, array $columns): array
    {
        $rows = [];

        for ($index = 1; $index <= 4; $index++) {
            $row = ['id' => "{$moduleSlug}-{$index}"];

            foreach ($columns as $column) {
                $row[$column['key']] = self::sampleValueForColumn($column['key'], $moduleTitle, $index);
            }

            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function relationManagers(string $moduleSlug): array
    {
        return match ($moduleSlug) {
            'pages' => [
                [
                    'label' => 'Traducciones',
                    'description' => 'Patron para relation manager de traducciones con locale, titulo, metadatos y payload JSON.',
                    'href' => BackofficePath::active('pages/sample-page/edit'),
                ],
                [
                    'label' => 'Bloques hijos',
                    'description' => 'Patron de composicion padre-hijo para bloques de pagina con orden, tipo y settings JSON.',
                    'href' => BackofficePath::active('page-blocks'),
                ],
            ],
            'page-blocks', 'music-tracks', 'legal-documents', 'settings' => [[
                'label' => 'Traducciones',
                'description' => 'Patron reusable de traducciones por locale con create/edit aislado y validacion propia.',
                'href' => BackofficePath::active("{$moduleSlug}/sample-record/edit"),
            ]],
            default => [],
        };
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function dangerousActions(string $moduleSlug): array
    {
        return [
            [
                'slug' => 'archive-preview',
                'label' => 'Simular archivado',
                'description' => 'Patron de accion peligrosa con confirmacion manual antes de ejecutar cambios irreversibles o de alto impacto.',
                'tone' => 'amber',
                'requires_confirmation' => true,
                'confirmation_phrase' => strtoupper(Str::slug($moduleSlug, ' ')),
            ],
            [
                'slug' => 'delete-preview',
                'label' => 'Simular borrado',
                'description' => 'Patron de borrado con confirmacion explicita y feedback por flash message.',
                'tone' => 'rose',
                'requires_confirmation' => true,
                'confirmation_phrase' => 'BORRAR',
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function specialActions(string $moduleSlug): array
    {
        return match ($moduleSlug) {
            'newsletter-campaigns' => [[
                'slug' => 'queue-campaign',
                'label' => 'Simular queue campaign',
                'description' => 'Patron de accion de negocio que dispara una cola y cambia el estado editorial de la campaña.',
                'tone' => 'fuchsia',
                'requires_confirmation' => false,
            ]],
            'pages' => [[
                'slug' => 'open-translations',
                'label' => 'Patron relation manager de traducciones',
                'description' => 'Confirma el flujo reusable para traducciones de paginas con payload JSON.',
                'tone' => 'cyan',
                'requires_confirmation' => false,
            ], [
                'slug' => 'open-blocks',
                'label' => 'Patron bloques hijos de pagina',
                'description' => 'Valida la composicion padre-hijo para bloques y orden editorial.',
                'tone' => 'emerald',
                'requires_confirmation' => false,
            ]],
            'page-blocks', 'settings', 'seo-metas', 'social-links', 'partners', 'music-tracks', 'legal-documents' => [[
                'slug' => 'review-json-payload',
                'label' => 'Validar payload estructurado',
                'description' => 'Patron para formularios con JSON, metadata o contenido estructurado que requieren validacion adicional.',
                'tone' => 'cyan',
                'requires_confirmation' => false,
            ]],
            default => [],
        };
    }

    /**
     * @return array<int, string>
     */
    private static function testChecklist(string $moduleSlug): array
    {
        $items = [
            'Feature test del index con filtros, busqueda y ordenacion.',
            'Feature test del create/edit con FormRequest y mensajes de validacion.',
            'Feature test de permisos por rol (`editor`, `marketing`, `readonly`, `super_admin`).',
            'Smoke visual de index y formulario dentro del shell backoffice.',
            'Regresion del payload Inertia para evitar cambios rotos en componentes reutilizables.',
        ];

        if ($moduleSlug === 'newsletter-campaigns') {
            $items[] = 'Test del flujo especial de queue newsletter campaign.';
        }

        if (in_array($moduleSlug, ['pages', 'page-blocks', 'music-tracks', 'legal-documents', 'settings'], true)) {
            $items[] = 'Test de relation managers/traducciones del modulo.';
        }

        return $items;
    }

    private static function isBadgeColumn(string $key): bool
    {
        return Str::contains($key, ['status', 'locale', 'activo', 'published', 'publicado', 'featured', 'destacado', 'type', 'tipo']);
    }

    private static function isNumericColumn(string $key): bool
    {
        return Str::contains($key, ['count', 'size', 'width', 'height', 'position', 'id', 'year']);
    }

    /**
     * @return array<int, array<string, string>>
     */
    private static function fieldOptions(string $key, string $moduleSlug): array
    {
        return match ($key) {
            'platform' => self::options(['soundcloud', 'spotify', 'youtube']),
            'status' => self::options(['draft', 'queued', 'sent', 'cancelled']),
            'locale' => self::options(['es', 'en', 'ca', 'fr', 'it', 'de']),
            'template' => self::options(['home', 'about', 'music', 'calendar', 'media', 'contact', 'default']),
            'disk' => self::options(['public', 's3']),
            'partner_type', 'tipo_de_partner' => self::options(['sponsor', 'collaborator', 'media']),
            'document_type', 'tipo' => self::options(['privacy', 'terms', 'cookies', 'json']),
            'location', 'ubicacion' => self::options(['header', 'footer', 'contact']),
            'http_status' => self::options(['301', '302', '307']),
            'group', 'grupo' => $moduleSlug === 'settings'
                ? self::options(['header', 'footer', 'media', 'legal', 'seo', 'legacy'])
                : self::options(['general', 'seo', 'media']),
            default => self::options(['default', 'secondary']),
        };
    }

    /**
     * @return array<int, array<string, string>>
     */
    private static function filterOptions(string $key): array
    {
        return match ($key) {
            'activo', 'publicado', 'destacado', 'traducible', 'publico' => self::options(['Si', 'No']),
            'status' => self::options(['draft', 'queued', 'sent']),
            'locale' => self::options(['es', 'en', 'fr']),
            default => self::options(['Todos', 'Solo activos', 'Solo borradores']),
        };
    }

    /**
     * @param  array<int, string>  $values
     * @return array<int, array<string, string>>
     */
    private static function options(array $values): array
    {
        return array_map(fn (string $value): array => [
            'value' => $value,
            'label' => Str::headline($value),
        ], $values);
    }

    private static function defaultValueForType(string $type): mixed
    {
        return match ($type) {
            'toggle' => false,
            'relation' => [],
            'manager' => [],
            'file' => null,
            default => '',
        };
    }

    private static function sampleValueForColumn(string $key, string $moduleTitle, int $index): string
    {
        return match (true) {
            Str::contains($key, ['title', 'subject', 'name', 'label']) => "{$moduleTitle} {$index}",
            Str::contains($key, ['slug']) => Str::slug($moduleTitle)."-{$index}",
            Str::contains($key, ['location', 'ubicacion']) => ['Barcelona', 'Madrid', 'Sitges', 'Ibiza'][$index - 1] ?? 'Barcelona',
            Str::contains($key, ['status']) => ['draft', 'queued', 'sent', 'draft'][$index - 1] ?? 'draft',
            Str::contains($key, ['locale']) => ['es', 'en', 'fr', 'it'][$index - 1] ?? 'es',
            Str::contains($key, ['published', 'publicado', 'activo', 'featured', 'destacado']) => $index % 2 === 0 ? 'No' : 'Si',
            Str::contains($key, ['type', 'tipo']) => ['primary', 'secondary', 'editorial', 'legacy'][$index - 1] ?? 'primary',
            Str::contains($key, ['path']) => "/storage/{$index}/asset-preview",
            Str::contains($key, ['url']) => "https://example.com/{$index}",
            Str::contains($key, ['mime']) => 'image/jpeg',
            Str::contains($key, ['disk']) => 'public',
            Str::contains($key, ['position', 'count', 'id', 'year', 'tamano', 'size', 'width', 'height']) => (string) ($index * 10),
            Str::contains($key, ['actualizado', 'updated', 'scheduled', 'sent_at', 'subscribed', 'unsubscribed', 'inicio', 'fin', 'fecha']) => now()->subDays($index)->format('d/m/Y H:i'),
            default => "Registro {$index}",
        };
    }
}

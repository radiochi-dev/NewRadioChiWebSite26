<?php

namespace App\Actions\Backoffice;

use App\Models\Event;
use App\Models\MediaAsset;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use App\Models\Page;
use App\Models\SeoMeta;
use App\Models\User;

class BuildBackofficeDashboardPayloadAction
{
    /**
     * @return array<string, mixed>
     */
    public function execute(User $user): array
    {
        $payload = [
            'title' => 'Dashboard unificado del backoffice',
            'description' => 'Superficie principal del nuevo backoffice React + Inertia. Consolida las metricas reales del CMS, la sesion actual y los listados recientes sin arrastrar el CRUD monolitico del dashboard legacy.',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => '/backoffice'],
            ],
            'actions' => [
                [
                    'label' => 'Gestionar Eventos',
                    'href' => '/backoffice/events',
                    'variant' => 'primary',
                    'visible' => true,
                ],
            ],
            'summaryCards' => [
                ['label' => 'Eventos', 'value' => (string) Event::count(), 'tone' => 'amber'],
                ['label' => 'Paginas', 'value' => (string) Page::count(), 'tone' => 'cyan'],
                ['label' => 'Media', 'value' => (string) MediaAsset::count(), 'tone' => 'fuchsia'],
                ['label' => 'SEO', 'value' => (string) SeoMeta::count(), 'tone' => 'emerald'],
                ['label' => 'Suscriptores', 'value' => (string) NewsletterSubscriber::count(), 'tone' => 'amber'],
                ['label' => 'Campanas', 'value' => (string) NewsletterCampaign::count(), 'tone' => 'fuchsia'],
            ],
            'sessionPanel' => [
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $this->resolveRoleLabels($user),
                ],
                'access' => [
                    'backoffice' => $user->hasBackofficeAccess() ? 'Habilitado' : 'Sin acceso',
                    'content' => $user->canManageBackofficeContent() ? 'Gestion total' : 'Solo lectura',
                    'surface' => $user->isSuperAdmin() ? 'Super Admin' : 'Editor/operador',
                ],
                'legacyLinks' => [],
            ],
            'quickActions' => [
                [
                    'title' => 'Editorial',
                    'description' => 'Entrar al flujo editorial principal sin pasar por el dashboard legacy.',
                    'actions' => [
                        ['label' => 'Paginas', 'href' => '/backoffice/pages', 'variant' => 'primary'],
                    ],
                ],
                [
                    'title' => 'Agenda y media',
                    'description' => 'Acceso directo a los recursos que mas volumen operativo mueven.',
                    'actions' => [
                        ['label' => 'Eventos', 'href' => '/backoffice/events', 'variant' => 'primary'],
                        ['label' => 'Assets', 'href' => '/backoffice/media-assets', 'variant' => 'ghost'],
                    ],
                ],
                [
                    'title' => 'Marketing y SEO',
                    'description' => 'Superficie rapida para campañas, suscriptores y metadatos.',
                    'actions' => [
                        ['label' => 'Campanas', 'href' => '/backoffice/newsletter-campaigns', 'variant' => 'primary'],
                        ['label' => 'SEO', 'href' => '/backoffice/seo-metas', 'variant' => 'ghost'],
                    ],
                ],
            ],
            'recentTables' => [
                [
                    'title' => 'Eventos recientes',
                    'description' => 'Mismo dominio estadistico que hoy expone el dashboard legacy, ya aterrizado sobre el shell nuevo.',
                    'table' => $this->dashboardTable(
                        '/backoffice/events',
                        [
                            ['key' => 'title', 'label' => 'Titulo'],
                            ['key' => 'schedule', 'label' => 'Fecha'],
                            ['key' => 'location', 'label' => 'Ubicacion'],
                            ['key' => 'published', 'label' => 'Publicado'],
                        ],
                        $this->buildEventRows(),
                        [
                            'title' => 'Sin eventos recientes',
                            'description' => 'Cuando existan nuevos eventos apareceran aqui con el mismo criterio que usa el dashboard legacy.',
                        ],
                    ),
                ],
                [
                    'title' => 'Paginas recientes',
                    'description' => 'Visibilidad rapida del inventario editorial actual de paginas y templates.',
                    'table' => $this->dashboardTable(
                        '/backoffice/pages',
                        [
                            ['key' => 'slug', 'label' => 'Slug'],
                            ['key' => 'template', 'label' => 'Template'],
                            ['key' => 'published', 'label' => 'Publicado'],
                            ['key' => 'updated_at', 'label' => 'Actualizado'],
                        ],
                        $this->buildPageRows(),
                        [
                            'title' => 'Sin paginas recientes',
                            'description' => 'La tabla se activara en cuanto haya actividad editorial sobre paginas.',
                        ],
                    ),
                ],
                [
                    'title' => 'Media reciente',
                    'description' => 'Resumen rapido de los ultimos assets dados de alta en el sistema.',
                    'table' => $this->dashboardTable(
                        '/backoffice/media-assets',
                        [
                            ['key' => 'filename', 'label' => 'Archivo'],
                            ['key' => 'mime_type', 'label' => 'Mime'],
                            ['key' => 'disk', 'label' => 'Disk'],
                            ['key' => 'updated_at', 'label' => 'Actualizado'],
                        ],
                        $this->buildMediaRows(),
                        [
                            'title' => 'Sin media reciente',
                            'description' => 'Los nuevos assets apareceran aqui conforme se carguen al CMS.',
                        ],
                    ),
                ],
                [
                    'title' => 'Campanas recientes',
                    'description' => 'Seguimiento rapido del estado de newsletter sin abrir el dashboard legacy.',
                    'table' => $this->dashboardTable(
                        '/backoffice/newsletter-campaigns',
                        [
                            ['key' => 'subject', 'label' => 'Subject'],
                            ['key' => 'status', 'label' => 'Estado'],
                            ['key' => 'scheduled_at', 'label' => 'Programada'],
                            ['key' => 'sent_at', 'label' => 'Enviada'],
                        ],
                        $this->buildCampaignRows(),
                        [
                            'title' => 'Sin campanas recientes',
                            'description' => 'La actividad de newsletters se reflejara aqui con los ultimos envios o borradores.',
                        ],
                    ),
                ],
            ],
        ];

        return $payload;
    }

    /**
     * @return array<int, string>
     */
    private function resolveRoleLabels(User $user): array
    {
        $roles = method_exists($user, 'getRoleNames')
            ? $user->getRoleNames()->values()->all()
            : [];

        if (empty($roles) && $user->isLegacySuperAdmin()) {
            $roles[] = 'super_admin';
        }

        return array_values(array_unique($roles));
    }

    /**
     * @param  array<int, array<string, string>>  $columns
     * @param  array<int, array<string, string>>  $rows
     * @param  array<string, string>  $emptyState
     * @return array<string, mixed>
     */
    private function dashboardTable(string $path, array $columns, array $rows, array $emptyState): array
    {
        return [
            'path' => $path,
            'query' => [],
            'columns' => $columns,
            'rows' => array_map(
                fn (array $row): array => $this->dashboardRow($path, $columns, $row),
                $rows,
            ),
            'emptyState' => $emptyState,
            'bulkActions' => [],
            'sort' => [
                'column' => null,
                'direction' => 'desc',
            ],
        ];
    }

    /**
     * @param  array<int, array<string, string>>  $columns
     * @param  array<string, string>  $row
     * @return array<string, mixed>
     */
    private function dashboardRow(string $path, array $columns, array $row): array
    {
        $recordId = $row['id'] ?? null;

        return [
            'id' => $recordId ?? md5(json_encode($row)),
            'cells' => array_map(
                fn (array $column): array => [
                    'key' => $column['key'],
                    'value' => $row[$column['key']] ?? '—',
                    'badge' => false,
                    'align' => 'left',
                ],
                $columns,
            ),
            'actions' => [
                [
                    'label' => 'Abrir',
                    'variant' => 'ghost',
                    'enabled' => is_string($recordId) && $recordId !== '',
                    'href' => is_string($recordId) && $recordId !== ''
                        ? $path.'/'.$recordId.'/edit'
                        : $path,
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function buildEventRows(): array
    {
        return Event::query()
            ->latest()
            ->take(8)
            ->get(['id', 'title', 'event_starts_at', 'location', 'is_published'])
            ->map(fn (Event $event): array => [
                'id' => (string) $event->id,
                'title' => $event->title,
                'schedule' => $this->formatDateTime($event->event_starts_at),
                'location' => $event->location ?: '—',
                'published' => $event->is_published ? 'Si' : 'No',
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function buildPageRows(): array
    {
        return Page::query()
            ->latest()
            ->take(8)
            ->get(['id', 'slug', 'template', 'is_published', 'updated_at'])
            ->map(fn (Page $page): array => [
                'id' => (string) $page->id,
                'slug' => $page->slug,
                'template' => $page->template,
                'published' => $page->is_published ? 'Si' : 'No',
                'updated_at' => $this->formatDateTime($page->updated_at),
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function buildMediaRows(): array
    {
        return MediaAsset::query()
            ->latest()
            ->take(8)
            ->get(['id', 'filename', 'mime_type', 'disk', 'updated_at'])
            ->map(fn (MediaAsset $asset): array => [
                'id' => (string) $asset->id,
                'filename' => $asset->filename ?: $asset->path,
                'mime_type' => $asset->mime_type ?: '—',
                'disk' => $asset->disk,
                'updated_at' => $this->formatDateTime($asset->updated_at),
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function buildCampaignRows(): array
    {
        return NewsletterCampaign::query()
            ->latest()
            ->take(8)
            ->get(['id', 'subject', 'status', 'scheduled_at', 'sent_at'])
            ->map(fn (NewsletterCampaign $campaign): array => [
                'id' => (string) $campaign->id,
                'subject' => $campaign->subject,
                'status' => $campaign->status,
                'scheduled_at' => $this->formatDateTime($campaign->scheduled_at),
                'sent_at' => $this->formatDateTime($campaign->sent_at),
            ])
            ->all();
    }

    private function formatDateTime(mixed $value): string
    {
        if (! $value) {
            return '—';
        }

        return $value->format('d/m/Y H:i');
    }

}

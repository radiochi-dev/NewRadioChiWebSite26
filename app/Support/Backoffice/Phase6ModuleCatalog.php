<?php

namespace App\Support\Backoffice;

use App\Models\Event;
use App\Models\LegalDocument;
use App\Models\MusicTrack;
use App\Models\PageBlock;
use App\Support\BackofficeLocales;

final class Phase6ModuleCatalog
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public static function modules(): array
    {
        return [
            'events' => [
                'slug' => 'events',
                'title' => 'Eventos',
                'singular' => 'Evento',
                'group' => 'Agenda',
                'description' => 'Gestion real del calendario editorial con persistencia Laravel sobre la base reusable de Fase 5.',
                'defaultSort' => 'event_starts_at',
                'defaultDirection' => 'desc',
                'columns' => [
                    self::column('title', 'Titulo'),
                    self::column('slug', 'Slug'),
                    self::column('location', 'Ubicacion'),
                    self::column('event_starts_at', 'Inicio'),
                    self::column('is_featured', 'Destacado', badge: true),
                    self::column('is_published', 'Publicado', badge: true),
                    self::column('published_at', 'Publicado en'),
                ],
                'filters' => [
                    self::ternaryFilter('is_featured', 'Destacado'),
                    self::ternaryFilter('is_published', 'Publicado'),
                ],
                'formSections' => [
                    [
                        'title' => 'Datos del evento',
                        'fields' => [
                            self::text('slug', 'Slug', true),
                            self::text('title', 'Titulo', true),
                            self::text('location', 'Ubicacion'),
                            self::url('external_url', 'URL externa'),
                            self::datetime('event_starts_at', 'Inicio'),
                            self::datetime('event_ends_at', 'Fin'),
                            self::datetime('published_at', 'Publicado en'),
                            self::toggle('is_featured', 'Destacado'),
                            self::toggle('is_published', 'Publicado'),
                            self::textarea('excerpt', 'Extracto'),
                            self::textarea('body', 'Contenido'),
                        ],
                    ],
                ],
            ],
            'pages' => [
                'slug' => 'pages',
                'title' => 'Paginas',
                'singular' => 'Pagina',
                'group' => 'Editorial',
                'description' => 'Gestion real de paginas, traducciones y bloques hijos con coexistencia segura frente a Filament.',
                'defaultSort' => 'updated_at',
                'defaultDirection' => 'desc',
                'columns' => [
                    self::column('slug', 'Slug'),
                    self::column('template', 'Template'),
                    self::column('translations_count', 'Traducciones', align: 'right'),
                    self::column('is_published', 'Publicada', badge: true),
                    self::column('published_at', 'Publicado en'),
                    self::column('updated_at', 'Actualizada'),
                ],
                'filters' => [
                    self::ternaryFilter('is_published', 'Publicada'),
                ],
                'formSections' => [
                    [
                        'title' => 'Datos de pagina',
                        'fields' => [
                            self::text('slug', 'Slug', true),
                            self::text('template', 'Template', true),
                            self::datetime('published_at', 'Publicado en'),
                            self::toggle('is_published', 'Publicada'),
                        ],
                    ],
                ],
            ],
            'page-blocks' => [
                'slug' => 'page-blocks',
                'title' => 'Bloques de pagina',
                'singular' => 'Bloque de pagina',
                'group' => 'Editorial',
                'description' => 'Gestion real de bloques editoriales compuestos por pagina y traducciones por locale.',
                'defaultSort' => 'page_id',
                'defaultDirection' => 'asc',
                'columns' => [
                    self::column('page_slug', 'Pagina', sortable: false),
                    self::column('key', 'Clave'),
                    self::column('type', 'Tipo', badge: true),
                    self::column('position', 'Posicion', align: 'right'),
                    self::column('translations_count', 'Traducciones', align: 'right'),
                    self::column('is_active', 'Activo', badge: true),
                    self::column('updated_at', 'Actualizado'),
                ],
                'filters' => [
                    self::selectFilter('page_id', 'Pagina', []),
                    self::ternaryFilter('is_active', 'Activo'),
                ],
                'formSections' => [
                    [
                        'title' => 'Bloque editorial',
                        'fields' => [
                            self::select('page_id', 'Pagina', true, []),
                            self::text('key', 'Clave', true),
                            self::text('type', 'Tipo', true),
                            self::number('position', 'Posicion'),
                            self::toggle('is_active', 'Activo'),
                            self::json('settings', 'Settings JSON'),
                        ],
                    ],
                ],
            ],
            'music-tracks' => [
                'slug' => 'music-tracks',
                'title' => 'Tracks musicales',
                'singular' => 'Track musical',
                'group' => 'Editorial',
                'description' => 'Gestion real de tracks musicales, plataformas, metadata editorial y traducciones equivalentes al relation manager actual.',
                'defaultSort' => 'position',
                'defaultDirection' => 'asc',
                'columns' => [
                    self::column('slug', 'Slug'),
                    self::column('platform', 'Plataforma', badge: true),
                    self::column('genre', 'Genero'),
                    self::column('position', 'Posicion', align: 'right'),
                    self::column('translations_count', 'Traducciones', align: 'right'),
                    self::column('is_featured', 'Destacado', badge: true),
                    self::column('is_published', 'Publicado', badge: true),
                ],
                'filters' => [
                    self::selectFilter('platform', 'Plataforma', self::musicPlatformOptions()),
                    self::ternaryFilter('is_featured', 'Destacado'),
                    self::ternaryFilter('is_published', 'Publicado'),
                ],
                'formSections' => [
                    [
                        'title' => 'Track musical',
                        'fields' => [
                            self::text('slug', 'Slug', true),
                            self::select('platform', 'Plataforma', true, self::musicPlatformOptions()),
                            self::image('label_image_path', 'Imagen label'),
                            self::image('cover_image_path', 'Imagen cover'),
                            self::url('stream_url', 'URL stream'),
                            self::url('external_url', 'URL externa'),
                            self::text('genre', 'Genero'),
                            self::number('year', 'Ano'),
                            self::number('position', 'Posicion'),
                            self::datetime('published_at', 'Publicado en'),
                            self::toggle('is_featured', 'Destacado'),
                            self::toggle('is_published', 'Publicado'),
                            self::json('settings', 'Settings JSON'),
                        ],
                    ],
                ],
            ],
            'media-assets' => [
                'slug' => 'media-assets',
                'title' => 'Assets',
                'singular' => 'Asset',
                'group' => 'Media',
                'description' => 'Gestion real del catalogo tecnico de media con metadata, dimensiones y contrato legacy adicional via API admin.',
                'defaultSort' => 'updated_at',
                'defaultDirection' => 'desc',
                'columns' => [
                    self::column('filename', 'Filename'),
                    self::column('disk', 'Disk'),
                    self::column('path', 'Path'),
                    self::column('mime_type', 'MIME type'),
                    self::column('size_human', 'Tamano', sortable: false),
                    self::column('updated_at', 'Actualizado'),
                ],
                'filters' => [
                    self::selectFilter('disk', 'Disk', []),
                ],
                'formSections' => [
                    [
                        'title' => 'Datos de asset',
                        'fields' => [
                            self::text('disk', 'Disk', true),
                            self::text('path', 'Path', true),
                            self::text('filename', 'Filename', true),
                            self::text('mime_type', 'MIME type'),
                            self::number('size', 'Tamano bytes'),
                            self::number('width', 'Width'),
                            self::number('height', 'Height'),
                            self::textarea('alt_text', 'Alt text'),
                            self::json('metadata', 'Metadata JSON'),
                        ],
                    ],
                ],
            ],
            'partners' => [
                'slug' => 'partners',
                'title' => 'Sponsors',
                'singular' => 'Sponsor',
                'group' => 'Contacto',
                'description' => 'Gestion real de sponsors y partners con orden, logo, website y estado de visibilidad.',
                'defaultSort' => 'position',
                'defaultDirection' => 'asc',
                'columns' => [
                    self::column('name', 'Nombre'),
                    self::column('slug', 'Slug'),
                    self::column('partner_type', 'Tipo', badge: true),
                    self::column('website_url', 'Web'),
                    self::column('position', 'Posicion', align: 'right'),
                    self::column('is_active', 'Activo', badge: true),
                ],
                'filters' => [
                    self::selectFilter('partner_type', 'Tipo', self::partnerTypeOptions()),
                    self::ternaryFilter('is_active', 'Activo'),
                ],
                'formSections' => [
                    [
                        'title' => 'Sponsor',
                        'fields' => [
                            self::text('slug', 'Slug', true),
                            self::text('name', 'Nombre', true),
                            self::select('partner_type', 'Tipo', true, self::partnerTypeOptions()),
                            self::url('website_url', 'URL web'),
                            self::image('logo_path', 'Logo'),
                            self::number('position', 'Posicion'),
                            self::toggle('is_active', 'Activo'),
                            self::json('settings', 'Settings JSON'),
                        ],
                    ],
                ],
            ],
            'social-links' => [
                'slug' => 'social-links',
                'title' => 'Redes sociales',
                'singular' => 'Red social',
                'group' => 'Contacto',
                'description' => 'Fuente comun de enlaces sociales reutilizada en contacto, footer y superficies compartidas del proyecto.',
                'defaultSort' => 'position',
                'defaultDirection' => 'asc',
                'columns' => [
                    self::column('label', 'Etiqueta'),
                    self::column('platform', 'Plataforma', badge: true),
                    self::column('url', 'URL'),
                    self::column('position', 'Posicion', align: 'right'),
                    self::column('is_active', 'Activo', badge: true),
                ],
                'filters' => [
                    self::ternaryFilter('is_active', 'Activo'),
                ],
                'formSections' => [
                    [
                        'title' => 'Red social',
                        'fields' => [
                            self::text('platform', 'Plataforma', true),
                            self::text('label', 'Etiqueta'),
                            self::url('url', 'URL', true),
                            self::text('icon_key', 'Icon key'),
                            self::number('position', 'Posicion'),
                            self::toggle('is_active', 'Activo'),
                            self::json('settings', 'Settings JSON'),
                        ],
                    ],
                ],
            ],
            'settings' => [
                'slug' => 'settings',
                'title' => 'Settings',
                'singular' => 'Setting',
                'group' => 'Configuracion',
                'description' => 'Gestion real de configuracion global publica o privada con traducciones y payloads JSON.',
                'defaultSort' => 'group',
                'defaultDirection' => 'asc',
                'columns' => [
                    self::column('group', 'Grupo', badge: true),
                    self::column('key', 'Clave'),
                    self::column('type', 'Tipo', badge: true),
                    self::column('translations_count', 'Traducciones', align: 'right'),
                    self::column('is_translatable', 'Translatable', badge: true),
                    self::column('is_public', 'Publico', badge: true),
                    self::column('position', 'Posicion', align: 'right'),
                ],
                'filters' => [
                    self::selectFilter('group', 'Grupo', []),
                    self::ternaryFilter('is_translatable', 'Translatable'),
                    self::ternaryFilter('is_public', 'Publico'),
                ],
                'formSections' => [
                    [
                        'title' => 'Setting global',
                        'fields' => [
                            self::text('group', 'Grupo', true),
                            self::text('key', 'Clave', true),
                            self::select('type', 'Tipo', true, [
                                ['value' => 'string', 'label' => 'string'],
                                ['value' => 'json', 'label' => 'json'],
                                ['value' => 'boolean', 'label' => 'boolean'],
                                ['value' => 'number', 'label' => 'number'],
                                ['value' => 'url', 'label' => 'url'],
                                ['value' => 'html', 'label' => 'html'],
                            ]),
                            self::number('position', 'Posicion'),
                            self::toggle('is_translatable', 'Translatable'),
                            self::toggle('is_public', 'Publico'),
                            self::json('value', 'Value JSON'),
                            self::json('settings', 'Settings JSON'),
                        ],
                    ],
                ],
            ],
            'downloadable-files' => [
                'slug' => 'downloadable-files',
                'title' => 'Archivos descargables',
                'singular' => 'Archivo descargable',
                'group' => 'Media',
                'description' => 'Gestion real de documentos descargables, rutas locales/externas y adjuntos polimorficos.',
                'defaultSort' => 'position',
                'defaultDirection' => 'asc',
                'columns' => [
                    self::column('display_name', 'Nombre'),
                    self::column('slug', 'Slug'),
                    self::column('collection', 'Coleccion', badge: true),
                    self::column('disk', 'Disk'),
                    self::column('attachable_label', 'Relacionado', sortable: false),
                    self::column('position', 'Posicion', align: 'right'),
                    self::column('is_active', 'Activo', badge: true),
                ],
                'filters' => [
                    self::ternaryFilter('is_active', 'Activo'),
                ],
                'formSections' => [
                    [
                        'title' => 'Archivo descargable',
                        'fields' => [
                            self::text('slug', 'Slug', true),
                            self::text('display_name', 'Nombre visible', true),
                            self::text('disk', 'Disk', true),
                            self::text('collection', 'Coleccion'),
                            self::text('file_path', 'Ruta de archivo'),
                            self::text('file_name', 'Nombre de archivo'),
                            self::text('mime_type', 'MIME type'),
                            self::number('size', 'Tamano bytes'),
                            self::url('external_url', 'URL externa'),
                            self::select('attachable_type', 'Tipo relacionado', false, self::downloadableAttachableOptions()),
                            self::number('attachable_id', 'ID relacionado'),
                            self::number('position', 'Posicion'),
                            self::toggle('is_active', 'Activo'),
                            self::textarea('description', 'Descripcion'),
                            self::json('settings', 'Settings JSON'),
                        ],
                    ],
                ],
            ],
            'seo-metas' => [
                'slug' => 'seo-metas',
                'title' => 'SEO meta',
                'singular' => 'SEO meta',
                'group' => 'SEO',
                'description' => 'Gestion real de metadata SEO polimorfica por entidad y locale con payloads JSON legacy.',
                'defaultSort' => 'updated_at',
                'defaultDirection' => 'desc',
                'columns' => [
                    self::column('entity_type', 'Entity type'),
                    self::column('entity_id', 'Entity ID', align: 'right'),
                    self::column('locale', 'Idioma', badge: true),
                    self::column('meta_title', 'Meta title'),
                    self::column('canonical_url', 'Canonical URL'),
                    self::column('updated_at', 'Actualizado'),
                ],
                'filters' => [
                    self::selectFilter('locale', 'Idioma', self::localeOptions()),
                ],
                'formSections' => [
                    [
                        'title' => 'Identidad SEO',
                        'fields' => [
                            self::text('entity_type', 'Entity type', true),
                            self::number('entity_id', 'Entity ID', true),
                            self::select('locale', 'Idioma', true, self::localeOptions()),
                        ],
                    ],
                    [
                        'title' => 'Metadata',
                        'fields' => [
                            self::text('meta_title', 'Meta title'),
                            self::textarea('meta_description', 'Meta description'),
                            self::url('canonical_url', 'Canonical URL'),
                        ],
                    ],
                    [
                        'title' => 'Payloads JSON',
                        'fields' => [
                            self::json('open_graph', 'Open Graph JSON'),
                            self::json('twitter_card', 'Twitter Card JSON'),
                            self::json('json_ld', 'JSON-LD'),
                        ],
                    ],
                ],
            ],
            'legal-documents' => [
                'slug' => 'legal-documents',
                'title' => 'Documentos legales',
                'singular' => 'Documento legal',
                'group' => 'Legal',
                'description' => 'Gestion real de documentos legales, versiones publicadas y traducciones equivalentes al relation manager de Filament.',
                'defaultSort' => 'position',
                'defaultDirection' => 'asc',
                'columns' => [
                    self::column('slug', 'Slug'),
                    self::column('document_type', 'Tipo', badge: true),
                    self::column('version', 'Version'),
                    self::column('position', 'Posicion', align: 'right'),
                    self::column('translations_count', 'Traducciones', align: 'right'),
                    self::column('is_published', 'Publicado', badge: true),
                    self::column('updated_at', 'Actualizado'),
                ],
                'filters' => [
                    self::selectFilter('document_type', 'Tipo', self::legalDocumentTypeOptions()),
                    self::ternaryFilter('is_published', 'Publicado'),
                ],
                'formSections' => [
                    [
                        'title' => 'Documento legal',
                        'fields' => [
                            self::text('slug', 'Slug', true),
                            self::select('document_type', 'Tipo', true, self::legalDocumentTypeOptions()),
                            self::text('version', 'Version'),
                            self::number('position', 'Posicion'),
                            self::datetime('published_at', 'Publicado en'),
                            self::toggle('is_published', 'Publicado'),
                            self::json('settings', 'Settings JSON'),
                        ],
                    ],
                ],
            ],
            'redirect-rules' => [
                'slug' => 'redirect-rules',
                'title' => 'Redirecciones',
                'singular' => 'Regla de redireccion',
                'group' => 'SEO',
                'description' => 'Gestion real de reglas de redireccion con estado HTTP, locale opcional y metrica de hits acumulados.',
                'defaultSort' => 'updated_at',
                'defaultDirection' => 'desc',
                'columns' => [
                    self::column('source_path', 'Origen'),
                    self::column('destination_url', 'Destino', sortable: false),
                    self::column('http_status', 'HTTP status', badge: true),
                    self::column('locale', 'Locale', badge: true),
                    self::column('is_active', 'Activa', badge: true),
                    self::column('hit_count', 'Hits', align: 'right'),
                ],
                'filters' => [
                    self::selectFilter('http_status', 'HTTP status', self::redirectStatusOptions()),
                    self::ternaryFilter('is_active', 'Activa'),
                ],
                'formSections' => [
                    [
                        'title' => 'Regla de redireccion',
                        'fields' => [
                            self::text('source_path', 'Origen', true),
                            self::url('destination_url', 'Destino', true),
                            self::select('http_status', 'HTTP status', true, self::redirectStatusOptions()),
                            self::select('locale', 'Locale', false, self::localeOptions()),
                            self::number('hit_count', 'Hits'),
                            self::toggle('is_active', 'Activa'),
                            self::textarea('notes', 'Notas'),
                        ],
                    ],
                ],
            ],
            'newsletter-subscribers' => [
                'slug' => 'newsletter-subscribers',
                'title' => 'Suscriptores newsletter',
                'singular' => 'Suscriptor',
                'group' => 'Newsletters',
                'description' => 'Gestion real de suscriptores con alta, baja y fechas derivadas segun la logica de negocio vigente.',
                'defaultSort' => 'created_at',
                'defaultDirection' => 'desc',
                'columns' => [
                    self::column('email', 'Email'),
                    self::column('name', 'Nombre'),
                    self::column('is_active', 'Activo', badge: true),
                    self::column('subscribed_at', 'Suscrito en'),
                    self::column('unsubscribed_at', 'Baja en'),
                ],
                'filters' => [
                    self::ternaryFilter('is_active', 'Activo'),
                ],
                'formSections' => [
                    [
                        'title' => 'Datos del suscriptor',
                        'fields' => [
                            self::text('email', 'Email', true),
                            self::text('name', 'Nombre'),
                            self::toggle('is_active', 'Activo'),
                            self::datetime('subscribed_at', 'Suscrito en'),
                            self::datetime('unsubscribed_at', 'Baja en'),
                        ],
                    ],
                ],
            ],
            'newsletter-campaigns' => [
                'slug' => 'newsletter-campaigns',
                'title' => 'Campanas newsletter',
                'singular' => 'Campana newsletter',
                'group' => 'Newsletters',
                'description' => 'Gestion real de campanas con cola de envio, metrica agregada y consulta de logs por campana.',
                'defaultSort' => 'created_at',
                'defaultDirection' => 'desc',
                'columns' => [
                    self::column('name', 'Nombre'),
                    self::column('subject', 'Asunto', sortable: false),
                    self::column('status', 'Estado', badge: true),
                    self::column('scheduled_at', 'Programada'),
                    self::column('sent_at', 'Enviada'),
                    self::column('sent_count', 'Enviados', align: 'right'),
                ],
                'filters' => [
                    self::selectFilter('status', 'Estado', self::newsletterCampaignStatusOptions()),
                ],
                'formSections' => [
                    [
                        'title' => 'Datos de campana',
                        'fields' => [
                            self::text('name', 'Nombre', true),
                            self::text('subject', 'Asunto', true),
                            self::select('status', 'Estado', true, self::newsletterCampaignStatusOptions()),
                            self::datetime('scheduled_at', 'Programada para'),
                            self::datetime('sent_at', 'Enviada en'),
                            self::number('sent_count', 'Enviados'),
                        ],
                    ],
                    [
                        'title' => 'Contenido HTML',
                        'fields' => [
                            self::textarea('html_body', 'HTML body', true),
                        ],
                    ],
                ],
            ],
            'newsletter-logs' => [
                'slug' => 'newsletter-logs',
                'title' => 'Logs newsletter',
                'singular' => 'Log newsletter',
                'group' => 'Newsletters',
                'description' => 'Consulta real y de solo lectura de entregas newsletter, con filtro por campana y detalle por suscriptor.',
                'defaultSort' => 'processed_at',
                'defaultDirection' => 'desc',
                'readOnly' => true,
                'columns' => [
                    self::column('processed_at', 'Procesado en'),
                    self::column('campaign_name', 'Campana', sortable: false),
                    self::column('subscriber_email', 'Suscriptor', sortable: false),
                    self::column('status', 'Estado', badge: true),
                    self::column('error_message', 'Error', sortable: false),
                ],
                'filters' => [
                    self::selectFilter('campaign_id', 'Campana', []),
                    self::selectFilter('status', 'Estado', self::newsletterLogStatusOptions()),
                ],
                'formSections' => [
                    [
                        'title' => 'Detalle del log',
                        'fields' => [
                            self::text('campaign_name', 'Campana'),
                            self::text('subscriber_email', 'Suscriptor'),
                            self::text('status', 'Estado'),
                            self::datetime('processed_at', 'Procesado en'),
                            self::textarea('error_message', 'Error'),
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function module(string $slug): array
    {
        $module = self::modules()[$slug] ?? null;

        abort_if(! is_array($module), 404);

        return $module;
    }

    public static function supports(string $slug): bool
    {
        return array_key_exists($slug, self::modules());
    }

    public static function isReadOnly(string $slug): bool
    {
        return (bool) (self::modules()[$slug]['readOnly'] ?? false);
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function localeOptions(): array
    {
        return array_map(
            static fn (string $value): array => ['value' => $value, 'label' => $value],
            BackofficeLocales::values(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function pageTranslationForm(): array
    {
        return [
            'title' => 'Traduccion de pagina',
            'description' => 'Equivalente React/Inertia del relation manager de traducciones de paginas.',
            'fields' => [
                self::select('locale', 'Idioma', true, self::localeOptions()),
                self::text('title', 'Titulo', true),
                self::text('meta_title', 'Meta title'),
                self::textarea('meta_description', 'Meta description'),
                self::json('content', 'Content JSON'),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function pageBlockTranslationForm(): array
    {
        return [
            'title' => 'Traduccion de bloque',
            'description' => 'Equivalente React/Inertia del relation manager de traducciones de bloques.',
            'fields' => [
                self::select('locale', 'Idioma', true, self::localeOptions()),
                self::json('content', 'Content JSON'),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function settingTranslationForm(): array
    {
        return [
            'title' => 'Traduccion de setting',
            'description' => 'Equivalente React/Inertia del relation manager de traducciones de settings.',
            'fields' => [
                self::select('locale', 'Idioma', true, self::localeOptions()),
                self::json('value', 'Value JSON'),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function musicTrackTranslationForm(): array
    {
        return [
            'title' => 'Traduccion de track musical',
            'description' => 'Equivalente React/Inertia del relation manager de traducciones de tracks musicales.',
            'fields' => [
                self::select('locale', 'Idioma', true, self::localeOptions()),
                self::text('artist_name', 'Artista'),
                self::text('title', 'Titulo', true),
                self::text('hero_title', 'Hero title'),
                self::text('subtitle', 'Subtitulo'),
                self::text('cta_primary_label', 'CTA principal'),
                self::text('cta_secondary_label', 'CTA secundario'),
                self::textarea('description', 'Descripcion'),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function legalDocumentTranslationForm(): array
    {
        return [
            'title' => 'Traduccion de documento legal',
            'description' => 'Equivalente React/Inertia del relation manager de traducciones de documentos legales.',
            'fields' => [
                self::select('locale', 'Idioma', true, self::localeOptions()),
                self::text('title', 'Titulo', true),
                self::text('cta_label', 'CTA label'),
                self::textarea('summary', 'Resumen'),
                self::textarea('content', 'Contenido', true),
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function specialAction(string $module, string $action): ?array
    {
        return match ($module.':'.$action) {
            'newsletter-campaigns:queue-campaign' => [
                'slug' => 'queue-campaign',
                'label' => 'Encolar campana',
                'description' => 'Reutiliza la accion `QueueNewsletterCampaign` y despacha el job real a la cola `newsletter` igual que la superficie actual.',
                'tone' => 'amber',
                'requires_confirmation' => true,
                'confirmation_phrase' => 'ENCOLAR',
            ],
            default => null,
        };
    }

    /**
     * @return array<int, string>
     */
    public static function testChecklist(string $slug): array
    {
        $items = [
            'Feature test del index con querystring, filtros y ordenacion real.',
            'Feature test del create/edit con validacion Laravel e Inertia.',
            'Feature test de permisos por rol (`editor`, `marketing`, `readonly`, `super_admin`).',
            'Smoke de lectura/escritura sobre persistencia real.',
            'Regresion del payload Inertia para no romper la base reusable.',
        ];

        if ($slug === 'pages') {
            $items[] = 'Cobertura de traducciones de pagina y composicion con bloques hijos.';
        }

        if (in_array($slug, ['page-blocks', 'settings'], true)) {
            $items[] = 'Cobertura de relation manager equivalente para traducciones por locale.';
        }

        if ($slug === 'music-tracks') {
            $items[] = 'Cobertura de relation manager equivalente para traducciones del track musical.';
        }

        if ($slug === 'legal-documents') {
            $items[] = 'Cobertura de relation manager equivalente para traducciones del documento legal.';
        }

        if (in_array($slug, ['media-assets', 'downloadable-files'], true)) {
            $items[] = 'Cobertura de metadata técnica y contrato de assets/adjuntos sin inventar un flujo distinto al actual.';
        }

        if ($slug === 'redirect-rules') {
            $items[] = 'Cobertura de estados HTTP, locale opcional y contadores de hits sin alterar el contrato actual.';
        }

        if ($slug === 'newsletter-subscribers') {
            $items[] = 'Cobertura de alta/baja reutilizando `PrepareNewsletterSubscriberData` para `subscribed_at` y `unsubscribed_at`.';
        }

        if ($slug === 'newsletter-campaigns') {
            $items[] = 'Cobertura de accion especial de negocio para encolar la campana con `QueueNewsletterCampaign`.';
            $items[] = 'Cobertura de consulta de logs por campana desde la propia ficha editorial.';
        }

        if ($slug === 'newsletter-logs') {
            $items[] = 'Cobertura de listado de solo lectura y filtro por campana sobre persistencia real.';
        }

        return $items;
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function musicPlatformOptions(): array
    {
        return [
            ['value' => 'soundcloud', 'label' => 'SoundCloud'],
            ['value' => 'spotify', 'label' => 'Spotify'],
            ['value' => 'youtube', 'label' => 'YouTube'],
            ['value' => 'apple_music', 'label' => 'Apple Music'],
            ['value' => 'custom', 'label' => 'Custom'],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function partnerTypeOptions(): array
    {
        return [
            ['value' => 'sponsor', 'label' => 'Sponsor'],
            ['value' => 'partner', 'label' => 'Partner'],
            ['value' => 'media', 'label' => 'Media'],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function socialLocationOptions(): array
    {
        return [
            ['value' => 'global', 'label' => 'Global'],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function downloadableAttachableOptions(): array
    {
        return [
            ['value' => PageBlock::class, 'label' => 'PageBlock'],
            ['value' => MusicTrack::class, 'label' => 'MusicTrack'],
            ['value' => LegalDocument::class, 'label' => 'LegalDocument'],
            ['value' => Event::class, 'label' => 'Event'],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function legalDocumentTypeOptions(): array
    {
        return [
            ['value' => 'terms', 'label' => 'Terminos'],
            ['value' => 'privacy', 'label' => 'Privacidad'],
            ['value' => 'cookies', 'label' => 'Cookies'],
            ['value' => 'custom', 'label' => 'Custom'],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function redirectStatusOptions(): array
    {
        return [
            ['value' => '301', 'label' => '301'],
            ['value' => '302', 'label' => '302'],
            ['value' => '307', 'label' => '307'],
            ['value' => '308', 'label' => '308'],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function newsletterCampaignStatusOptions(): array
    {
        return [
            ['value' => 'draft', 'label' => 'draft'],
            ['value' => 'queued', 'label' => 'queued'],
            ['value' => 'sent', 'label' => 'sent'],
            ['value' => 'cancelled', 'label' => 'cancelled'],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function newsletterLogStatusOptions(): array
    {
        return [
            ['value' => 'sent', 'label' => 'sent'],
            ['value' => 'failed', 'label' => 'failed'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function column(string $key, string $label, bool $sortable = true, bool $badge = false, string $align = 'left'): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'sortable' => $sortable,
            'badge' => $badge,
            'align' => $align,
        ];
    }

    /**
     * @param  array<int, array<string, string>>  $options
     * @return array<string, mixed>
     */
    private static function selectFilter(string $key, string $label, array $options): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'kind' => 'select',
            'placeholder' => 'Todos',
            'options' => $options,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function ternaryFilter(string $key, string $label): array
    {
        return self::selectFilter($key, $label, [
            ['value' => '1', 'label' => 'Si'],
            ['value' => '0', 'label' => 'No'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private static function text(string $key, string $label, bool $required = false): array
    {
        return self::field($key, $label, 'text', $required);
    }

    /**
     * @return array<string, mixed>
     */
    private static function textarea(string $key, string $label, bool $required = false): array
    {
        return self::field($key, $label, 'textarea', $required);
    }

    /**
     * @return array<string, mixed>
     */
    private static function json(string $key, string $label, bool $required = false): array
    {
        return self::field($key, $label, 'json', $required);
    }

    /**
     * @param  array<int, array<string, string>>  $options
     * @return array<string, mixed>
     */
    private static function select(string $key, string $label, bool $required = false, array $options = []): array
    {
        return self::field($key, $label, 'select', $required, $options);
    }

    /**
     * @return array<string, mixed>
     */
    private static function toggle(string $key, string $label, bool $required = false): array
    {
        return self::field($key, $label, 'toggle', $required);
    }

    /**
     * @return array<string, mixed>
     */
    private static function number(string $key, string $label, bool $required = false): array
    {
        return self::field($key, $label, 'number', $required);
    }

    /**
     * @return array<string, mixed>
     */
    private static function datetime(string $key, string $label, bool $required = false): array
    {
        return self::field($key, $label, 'datetime', $required);
    }

    /**
     * @return array<string, mixed>
     */
    private static function url(string $key, string $label, bool $required = false): array
    {
        return self::field($key, $label, 'url', $required);
    }

    /**
     * @return array<string, mixed>
     */
    private static function image(string $key, string $label, bool $required = false): array
    {
        return self::field($key, $label, 'image', $required);
    }

    /**
     * @param  array<int, array<string, string>>  $options
     * @return array<string, mixed>
     */
    private static function field(string $key, string $label, string $type, bool $required = false, array $options = []): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'type' => $type,
            'surface' => 'input',
            'required' => $required,
            'options' => $options,
            'help' => 'Contrato de campo alineado con el recurso Filament vigente y la persistencia real del modulo.',
        ];
    }
}

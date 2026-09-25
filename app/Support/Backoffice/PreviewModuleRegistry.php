<?php

namespace App\Support\Backoffice;

final class PreviewModuleRegistry
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
                'group' => 'Editorial / contenido',
                'icon' => 'calendar',
                'description' => 'Gestion del calendario publico, publicaciones y estado editorial de eventos.',
                'columns' => ['Titulo', 'Slug', 'Ubicacion', 'Inicio', 'Destacado', 'Publicado'],
                'filters' => ['Publicado', 'Destacado'],
                'formSections' => [
                    ['title' => 'Identidad', 'fields' => ['Slug', 'Titulo', 'Ubicacion']],
                    ['title' => 'Publicacion', 'fields' => ['Inicio', 'Fin', 'Publicado', 'Fecha de publicacion']],
                    ['title' => 'Contenido', 'fields' => ['Resumen', 'Descripcion', 'URL externa']],
                ],
            ],
            'pages' => [
                'slug' => 'pages',
                'title' => 'Paginas',
                'singular' => 'Pagina',
                'group' => 'Editorial / contenido',
                'icon' => 'file',
                'description' => 'Gestion estructural de paginas, plantillas, bloques y traducciones.',
                'columns' => ['Slug', 'Template', 'Traducciones', 'Publicado', 'Fecha de publicacion', 'Actualizado'],
                'filters' => ['Publicado'],
                'formSections' => [
                    ['title' => 'Base', 'fields' => ['Slug', 'Template', 'Publicado', 'Fecha de publicacion']],
                    ['title' => 'Relaciones', 'fields' => ['Traducciones', 'Bloques']],
                ],
            ],
            'page-blocks' => [
                'slug' => 'page-blocks',
                'title' => 'Bloques de pagina',
                'singular' => 'Bloque de pagina',
                'group' => 'Editorial / contenido',
                'icon' => 'layout',
                'description' => 'Bloques composables por pagina con posicion, tipo y traducciones por locale.',
                'columns' => ['Pagina', 'Clave', 'Tipo', 'Posicion', 'Traducciones', 'Activo'],
                'filters' => ['Pagina', 'Activo'],
                'formSections' => [
                    ['title' => 'Bloque', 'fields' => ['Pagina', 'Clave', 'Tipo', 'Posicion', 'Activo']],
                    ['title' => 'Configuracion', 'fields' => ['Settings', 'Traducciones']],
                ],
            ],
            'music-tracks' => [
                'slug' => 'music-tracks',
                'title' => 'Tracks musicales',
                'singular' => 'Track musical',
                'group' => 'Editorial / contenido',
                'icon' => 'music',
                'description' => 'Gestion de tracks, plataformas, orden editorial y traducciones musicales.',
                'columns' => ['Slug', 'Plataforma', 'Genero', 'Posicion', 'Traducciones', 'Publicado'],
                'filters' => ['Plataforma', 'Destacado', 'Publicado'],
                'formSections' => [
                    ['title' => 'Identidad', 'fields' => ['Slug', 'Plataforma', 'Genero', 'Year', 'Posicion']],
                    ['title' => 'Media', 'fields' => ['Cover', 'Label image', 'Stream URL', 'External URL']],
                    ['title' => 'Editorial', 'fields' => ['Publicado', 'Destacado', 'Traducciones']],
                ],
            ],
            'media-assets' => [
                'slug' => 'media-assets',
                'title' => 'Assets',
                'singular' => 'Asset',
                'group' => 'Media',
                'icon' => 'image',
                'description' => 'Catalogo tecnico de media con metadata, dimensiones y alt text.',
                'columns' => ['Filename', 'Disk', 'Path', 'Mime', 'Tamano', 'Actualizado'],
                'filters' => ['Disk'],
                'formSections' => [
                    ['title' => 'Archivo', 'fields' => ['Disk', 'Path', 'Filename', 'Mime type']],
                    ['title' => 'Metadata', 'fields' => ['Tamano', 'Width', 'Height', 'Alt text', 'Metadata']],
                ],
            ],
            'downloadable-files' => [
                'slug' => 'downloadable-files',
                'title' => 'Archivos descargables',
                'singular' => 'Archivo descargable',
                'group' => 'Media',
                'icon' => 'download',
                'description' => 'Recursos descargables asociados a entidades o expuestos como documentos publicos.',
                'columns' => ['Nombre', 'Slug', 'Coleccion', 'Disk', 'Adjunto a', 'Activo'],
                'filters' => ['Activo'],
                'formSections' => [
                    ['title' => 'Archivo', 'fields' => ['Slug', 'Nombre visible', 'Disk', 'Coleccion']],
                    ['title' => 'Origen', 'fields' => ['File path', 'File name', 'Mime type', 'External URL']],
                    ['title' => 'Asociacion', 'fields' => ['Attachable type', 'Attachable id', 'Posicion', 'Activo']],
                ],
            ],
            'partners' => [
                'slug' => 'partners',
                'title' => 'Sponsors',
                'singular' => 'Sponsor',
                'group' => 'Marketing',
                'icon' => 'users',
                'description' => 'Gestion de sponsors y partners con posicion, tipo y visibilidad.',
                'columns' => ['Nombre', 'Slug', 'Tipo', 'Web', 'Posicion', 'Activo'],
                'filters' => ['Tipo', 'Activo'],
                'formSections' => [
                    ['title' => 'Identidad', 'fields' => ['Slug', 'Nombre', 'Tipo de partner']],
                    ['title' => 'Branding', 'fields' => ['Website URL', 'Logo path', 'Posicion', 'Activo']],
                ],
            ],
            'social-links' => [
                'slug' => 'social-links',
                'title' => 'Redes sociales',
                'singular' => 'Red social',
                'group' => 'Marketing',
                'icon' => 'share',
                'description' => 'Fuente comun de enlaces sociales reutilizada en todas las superficies compartidas del proyecto.',
                'columns' => ['Label', 'Plataforma', 'URL', 'Posicion', 'Activo'],
                'filters' => ['Activo'],
                'formSections' => [
                    ['title' => 'Enlace', 'fields' => ['Plataforma', 'Label', 'URL', 'Icon key']],
                    ['title' => 'Publicacion', 'fields' => ['Posicion', 'Activo']],
                ],
            ],
            'legal-documents' => [
                'slug' => 'legal-documents',
                'title' => 'Documentos legales',
                'singular' => 'Documento legal',
                'group' => 'Legal y footer',
                'icon' => 'shield',
                'description' => 'Documentacion legal con versionado, publicacion y traducciones asociadas.',
                'columns' => ['Slug', 'Tipo', 'Version', 'Posicion', 'Traducciones', 'Publicado'],
                'filters' => ['Tipo', 'Publicado'],
                'formSections' => [
                    ['title' => 'Documento', 'fields' => ['Slug', 'Tipo', 'Version', 'Posicion']],
                    ['title' => 'Publicacion', 'fields' => ['Publicado', 'Fecha de publicacion', 'Traducciones']],
                ],
            ],
            'settings' => [
                'slug' => 'settings',
                'title' => 'Settings',
                'singular' => 'Setting',
                'group' => 'Configuracion',
                'icon' => 'settings',
                'description' => 'Parametros globales publicos o privados, translatables y con posicion editorial.',
                'columns' => ['Grupo', 'Clave', 'Tipo', 'Traducciones', 'Publico', 'Posicion'],
                'filters' => ['Grupo', 'Traducible', 'Publico'],
                'formSections' => [
                    ['title' => 'Clave', 'fields' => ['Grupo', 'Key', 'Tipo', 'Posicion']],
                    ['title' => 'Valor', 'fields' => ['Traducible', 'Publico', 'Value', 'Traducciones']],
                ],
            ],
            'redirect-rules' => [
                'slug' => 'redirect-rules',
                'title' => 'Redirecciones',
                'singular' => 'Redireccion',
                'group' => 'SEO',
                'icon' => 'repeat',
                'description' => 'Reglas SEO de redireccion con locale, status code y tracking de hits.',
                'columns' => ['Source path', 'Destination URL', 'HTTP status', 'Locale', 'Activo', 'Hit count'],
                'filters' => ['HTTP status', 'Activo'],
                'formSections' => [
                    ['title' => 'Ruta', 'fields' => ['Source path', 'Destination URL', 'HTTP status', 'Locale']],
                    ['title' => 'Control', 'fields' => ['Hit count', 'Activo', 'Notes']],
                ],
            ],
            'seo-metas' => [
                'slug' => 'seo-metas',
                'title' => 'SEO meta',
                'singular' => 'SEO meta',
                'group' => 'SEO',
                'icon' => 'search',
                'description' => 'Metadata SEO por entidad y locale con payloads Open Graph, Twitter y JSON-LD.',
                'columns' => ['Entidad', 'Entity ID', 'Locale', 'Meta title', 'Canonical URL', 'Actualizado'],
                'filters' => ['Locale'],
                'formSections' => [
                    ['title' => 'Identidad SEO', 'fields' => ['Entity type', 'Entity id', 'Locale']],
                    ['title' => 'Metadata', 'fields' => ['Meta title', 'Meta description', 'Canonical URL']],
                    ['title' => 'Payloads', 'fields' => ['Open Graph', 'Twitter card', 'JSON-LD']],
                ],
            ],
            'newsletter-subscribers' => [
                'slug' => 'newsletter-subscribers',
                'title' => 'Suscriptores',
                'singular' => 'Suscriptor',
                'group' => 'Marketing',
                'icon' => 'mail',
                'description' => 'Base de suscriptores con estado, fecha de alta y fecha de baja.',
                'columns' => ['Email', 'Nombre', 'Activo', 'Subscribed at', 'Unsubscribed at'],
                'filters' => ['Activo'],
                'formSections' => [
                    ['title' => 'Identidad', 'fields' => ['Email', 'Nombre']],
                    ['title' => 'Estado', 'fields' => ['Activo', 'Subscribed at', 'Unsubscribed at']],
                ],
            ],
            'newsletter-campaigns' => [
                'slug' => 'newsletter-campaigns',
                'title' => 'Campanas',
                'singular' => 'Campana',
                'group' => 'Marketing',
                'icon' => 'send',
                'description' => 'Campanas de newsletter con estados, planificacion, cola y metricas de envio.',
                'columns' => ['Nombre', 'Subject', 'Status', 'Scheduled at', 'Sent at', 'Sent count'],
                'filters' => ['Status'],
                'formSections' => [
                    ['title' => 'Campana', 'fields' => ['Nombre', 'Subject', 'Status']],
                    ['title' => 'Planificacion', 'fields' => ['Scheduled at', 'Sent at', 'Sent count']],
                    ['title' => 'Contenido', 'fields' => ['HTML body', 'Queue action']],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function find(string $slug): ?array
    {
        return self::modules()[$slug] ?? null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function groups(): array
    {
        $groups = [];

        foreach (self::modules() as $module) {
            $groupName = $module['group'];

            if (! array_key_exists($groupName, $groups)) {
                $groups[$groupName] = [
                    'label' => $groupName,
                    'items' => [],
                ];
            }

            $groups[$groupName]['items'][] = [
                'slug' => $module['slug'],
                'label' => $module['title'],
                'href' => BackofficePath::official($module['slug']),
                'icon' => $module['icon'],
                'description' => $module['description'],
            ];
        }

        return array_values($groups);
    }
}

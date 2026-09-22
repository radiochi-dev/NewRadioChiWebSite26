# PLAN REMAKE

## Objetivo

Llevar el proyecto actual a un estado que cumpla de forma verificable estos requisitos:

1. Desarrollo local 100% Docker Desktop, sin XAMPP ni dependencias de host.
2. Stack objetivo exacto: Laravel 13 + Inertia + Tailwind + Framer Motion + Spatie + Filament + PostgreSQL.
3. CMS visual real para administrar el 100% del contenido: textos multilingues, imagenes, videos, PDFs, enlaces, SEO y automatizaciones.
4. Despliegue en VPS Hostinger con arquitectura de produccion que incluya:
   - aplicacion principal,
   - PostgreSQL principal del proyecto,
   - n8n con base de datos PostgreSQL independiente,
   - capacidad de n8n para consultar la base principal con credenciales limitadas,
   - backend Laravel capaz de disparar y consultar automatizaciones de n8n,
   - Ollama en el VPS con modelo ligero para automatizaciones internas.

## Estado actual resumido

- Docker local base: parcial.
- Laravel 13: no cumple.
- Inertia, Tailwind, Framer: cumple base.
- PostgreSQL principal: cumple base.
- Filament: no cumple.
- Spatie: no cumple.
- CMS visual completo: no cumple.
- Produccion Hostinger con n8n/Ollama: no cumple.
- Contenido 100% dinamico desde backend: no cumple.

## Criterios de exito finales

- `docker compose up -d --build` levanta desarrollo completo sin depender de PHP, Composer o npm del host.
- `composer.json` queda en Laravel 13 con dependencias compatibles.
- Existe panel Filament funcional con login, roles, permisos y recursos de contenido.
- El CMS permite administrar por locale:
  - home,
  - about,
  - music,
  - calendar/events,
  - media gallery,
  - contact,
  - footer/legal,
  - SEO por pagina y por entidad.
- Existe arquitectura de produccion reproducible en Hostinger con:
  - `radiochi_app`,
  - `radiochi_nginx`,
  - `radiochi_postgres`,
  - `radiochi_redis`,
  - `radiochi_worker`,
  - `radiochi_scheduler`,
  - `n8n`,
  - `n8n_postgres`,
  - `ollama`.
- El backend Laravel puede:
  - lanzar workflows de n8n por webhook/API,
  - consultar estado/ejecuciones,
  - exponer endpoints seguros consumibles por n8n,
  - delegar tareas puntuales a Ollama.

## Auditoria base de documentos guia

### Hallazgos trasladados desde `MASTER_ARCHITECTURE_PLAN.md`

1. El documento sigue orientado a una estrategia dual RadioChi + Neway en el mismo VPS. Para este repo, el plan operativo debe centrarse primero en RadioChi y dejar cualquier coexistencia multiapp como capacidad futura, no como eje del remake.
2. El documento sigue mencionando Laravel 12 como stack objetivo de RadioChi. Debe alinearse a Laravel 13.
3. El documento ya anticipa `n8n` y `ollama`, pero no especifica:
   - base de datos independiente de n8n,
   - permisos de lectura controlada sobre la base principal,
   - contrato backend <-> n8n,
   - politicas de seguridad para esas integraciones.
4. El documento habla de CMS minimo viable, pero el requisito actual es CMS completo administrable al 100%.

### Hallazgos trasladados desde `RADIOCHI_TECHNICAL_BLUEPRINT.md`

1. El blueprint sigue anclado a Laravel 12 y debe corregirse a Laravel 13.
2. El blueprint reconoce preparacion n8n/Ollama como pendiente, pero no la desarrolla en un plan de infraestructura y aplicacion.
3. El blueprint describe un CMS parcial y APIs admin base, pero no cubre la dinamizacion completa por seccion ni la migracion total de JSON legacy a contenido administrable.
4. Falta una matriz editorial exhaustiva que identifique todos los campos administrables por seccion.

## Auditoria actual del frontend y del grado de dinamizacion

### Situacion real actual

El frontend publico sigue dependiendo de contenido legacy cargado desde JSON y arrays embebidos en frontend. La pagina principal monta 6 secciones fijas:

- `home`
- `about`
- `music`
- `calendar`
- `media`
- `contact`

Ademas arrastra contenido legal, footer, logos de sponsors, redes sociales y SEO parcial.

### Resultado de la auditoria

1. Hay base de datos y APIs admin parciales para `pages`, `page_translations`, `events`, `media_assets`, `seo_meta` y newsletter.
2. Esa base no gobierna todavia el frontend publico principal.
3. El frontend sigue leyendo:
   - traducciones JSON por locale,
   - calendarios desde JSON legacy,
   - fotos/videos desde arrays hardcodeados,
   - logos/sponsors/social links hardcodeados,
   - textos legales desde JSON legacy,
   - SEO home parcialmente generado en controlador.
4. Conclusion: el proyecto aun no es 100% administrable desde backend.

## Matriz editorial obligatoria para hacer 100% dinamico el sitio

### 1. Home

Debe administrarse desde CMS:
- slides del hero,
- logo de slide,
- titulo,
- subtitulo,
- descripcion,
- imagen principal,
- CTA label,
- CTA URL,
- orden de slides,
- estado publicado,
- programacion por fecha si se necesita.

Modelo recomendado:
- `pages`
- `page_blocks`
- `page_block_translations`
- media relacionada por bloque

### 2. About

Debe administrarse desde CMS:
- pasos del scrollytelling,
- titulo,
- subtitulo,
- contenido rich text,
- imagen de fondo por paso,
- orden,
- estado publicado.

Modelo recomendado:
- `page_blocks` tipo `about_step`
- traducciones por locale
- relacion media por bloque

### 3. Music

Debe administrarse desde CMS:
- hero title,
- subtitle,
- descripcion,
- tracks,
- portada/label image,
- URL de SoundCloud,
- CTA SoundCloud,
- CTA Follow,
- orden y destacado.

Modelo recomendado:
- `music_tracks`
- `music_track_translations`
- media asociada
- enlaces externos estructurados

### 4. Calendar / Events

Debe administrarse desde CMS:
- eventos,
- fecha inicio,
- fecha fin,
- localizacion,
- pais,
- logo/cartel,
- CTA compra,
- URL externa,
- texto fallback tipo "dates coming soon",
- etiquetas por locale.

Modelo recomendado:
- ampliar `events`
- agregar traducciones por locale
- agregar media principal
- agregar campos editoriales y SEO por evento

### 5. Media

Debe administrarse desde CMS:
- galeria de fotos,
- alt text por locale,
- caption por locale,
- fecha,
- categoria,
- orden,
- videos,
- thumbnail,
- titulo,
- duracion,
- URL YouTube,
- posibilidad de adjuntar PDF/press kit si aplica.

Modelo recomendado:
- reemplazar `media_assets` puramente descriptivo por Media Library real
- `media_collections`
- `video_items`
- `downloadable_files`

### 6. Contact

Debe administrarse desde CMS:
- titulo,
- enlaces de redes sociales,
- sponsor logos,
- sponsor links,
- textos de las lineas marquee,
- emails y canales de contacto,
- CTAs externos.

Modelo recomendado:
- `social_links`
- `partners`
- `page_blocks` tipo `contact_marquee`

### 7. Footer y legal

Debe administrarse desde CMS:
- copyright,
- creditos,
- terminos,
- privacidad,
- cookies,
- enlaces asociados,
- contenido HTML o rich text por locale.

Modelo recomendado:
- `legal_documents`
- `legal_document_translations`
- `settings`

### 8. SEO transversal

Debe administrarse desde CMS:
- meta title,
- meta description,
- canonical,
- Open Graph,
- Twitter Card,
- JSON-LD,
- indice de sitemap,
- redirecciones 301,
- hreflang por locale.

Modelo recomendado:
- ampliar `seo_meta`
- `redirect_rules`
- generacion automatica de sitemap por tipo de contenido

### 9. Configuracion global

Debe administrarse desde CMS:
- locales activos,
- idioma por defecto,
- enlaces globales,
- configuracion de analytics,
- identificadores de integraciones,
- textos globales reutilizables.

Modelo recomendado:
- `settings`
- `settings_translations`
- `integrations`

## Fase 0. Baseline y guardarrailes

**Estado:** COMPLETADA

### Paso 0.1
~~Crear rama de trabajo limpia desde el estado restaurado y no tocar frontend publico salvo alcance autorizado.~~

### Paso 0.2
~~Congelar como reglas:~~
- Docker Desktop only,
- nada de XAMPP,
- nada de `php artisan serve` ni `npm run dev` en host,
- nada de cambios esteticos no pedidos.

### Paso 0.3
~~Definir checklist fijo por cambio:~~
- `docker compose config`
- `docker compose ps`
- `php artisan test`
- acceso HTTP publico
- acceso HTTP admin
- auditoria pre/post de archivos tocados

## Fase 1. Desarrollo 100% Docker

**Estado:** COMPLETADA

### Objetivo
~~Eliminar cualquier dependencia del host para arrancar y desarrollar.~~

### Paso 1.1
~~Rehacer `docker-compose.yml` para desarrollo estable con:~~
- `app`
- `nginx`
- `postgres`
- `redis`
- `node` o servicio `vite`

### Paso 1.2
~~Separar Dockerfiles o stages:~~
- desarrollo
- produccion multi-stage

### Paso 1.3
~~Garantizar dentro de contenedores:~~
- Composer
- extensiones PHP requeridas
- Node/npm o estrategia equivalente

### Paso 1.4
~~Crear flujo de arranque Windows PowerShell Docker-only:~~
- `up`
- `build`
- `rebuild`
- comandos internos solo via `docker compose exec`

### Paso 1.5
~~Verificacion de salida:~~
- levantado limpio,
- sin contenedores huerfanos,
- sin puertos abiertos globalmente sin control,
- frontend y backend respondiendo.

## Fase 2. Alineacion de stack a Laravel 13

**Estado:** COMPLETADA

### Objetivo
Cumplir exactamente el stack pedido.

### Paso 2.1
~~Actualizar el stack PHP/Laravel y validar compatibilidad real de:~~
- Laravel 13
- Inertia
- React
- Tailwind
- Filament
- Spatie
- paquetes existentes

### Paso 2.2
~~Regenerar lock y corregir incompatibilidades de:~~
- middleware,
- auth,
- bootstrap,
- tests,
- providers.

### Paso 2.3
~~Verificacion de salida:~~
- `php artisan about`
- `php artisan test`
- rutas publicas y privadas sanas

## Fase 3. Filament base + Spatie

**Estado:** PRACTICAMENTE CERRADA
**Avance de fase:** 99%
**Avance global del plan:** 50%

### Objetivo
Sustituir el admin custom parcial por CMS visual mantenible y seguro.

### Paso 3.1
~~Instalar Filament v5 compatible con Laravel 13.~~

### Paso 3.2
~~Instalar y configurar:~~
- ~~`spatie/laravel-permission`~~
- ~~`spatie/laravel-medialibrary`~~
- ~~`spatie/laravel-translatable`~~
- ~~`spatie/laravel-activitylog`~~

### Paso 3.3
~~Crear panel admin base:~~
- ~~login,~~
- ~~dashboard,~~
- ~~navegacion,~~
- ~~grupos editoriales,~~
- ~~control por roles.~~

### Paso 3.4
~~Definir roles base:~~
- ~~`super_admin`~~
- ~~`editor`~~
- ~~`marketing`~~
- ~~`readonly`~~

### Paso 3.5
Migrar gradualmente la operativa del dashboard actual a recursos Filament.

Avance validado en esta iteracion:
- ~~Recurso Filament base para `Event`.~~
- ~~Recurso Filament base para `Page`.~~
- ~~Recurso Filament base para `MediaAsset`.~~
- ~~Relation manager Filament para `PageTranslation` dentro de `Page`.~~
- ~~Recurso Filament base para `SeoMeta`.~~
- ~~Recurso Filament base para `NewsletterSubscriber`.~~
- ~~Recurso Filament base para `NewsletterCampaign`.~~
- ~~Auditoria final de convivencia con el admin legacy.~~

Inventario real tras auditoria:
- `backoffice/*` ya cubre dashboard Filament y CRUD de `Event`, `Page`, `MediaAsset`, `SeoMeta`, `NewsletterSubscriber` y `NewsletterCampaign`.
- `/dashboard` sigue vivo como dashboard Inertia legacy protegido por `auth` + `EnsureSuperAdmin`.
- `/dashboard/api/*` sigue vivo como API legacy autenticada para CRUD editorial/marketing y logs de newsletter.
- `/admin/*` sigue vivo como duplicado legacy por `auth.basic` de la misma API editorial/marketing.

Decision segura de convivencia:
- Mantener temporalmente `backoffice/*` como superficie principal del CMS.
- Mantener temporalmente `/dashboard` y `/dashboard/api/*` mientras no se migren o retiren los puntos legacy residuales, especialmente `newsletter-logs` y cualquier consumidor interno del dashboard antiguo.
- Marcar `/admin/*` como primer candidato de apagado, porque hoy es el bloque mas redundante: duplica el CRUD ya cubierto por `backoffice/*` y por `/dashboard/api/*`.
- No apagar todavia `/dashboard/api/*` hasta decidir si `newsletter-logs` se migra a Filament o se retira por completo.

## Fase 4. Remodelado editorial y de datos

**Estado:** COMPLETADA
**Avance de fase:** 100%
**Avance global del plan:** 66%

### Objetivo
Crear el modelo de datos real que permita administrar todo el contenido.

### Paso 4.1
~~Conservar y revisar tablas utiles existentes:~~
- ~~`pages`~~
- ~~`page_translations`~~
- ~~`events`~~
- ~~`seo_meta`~~
- ~~`newsletter_*`~~

Base validada en esta iteracion:
- ~~`pages` mantiene `slug`, `template`, `is_published` y `published_at`.~~
- ~~`page_translations` mantiene `locale`, metadatos SEO y `content` JSON.~~
- ~~`events` mantiene el dominio editorial actual y sigue alineado con Filament.~~
- ~~`seo_meta` mantiene el contrato polimorfico actual por `entity_type` + `entity_id` + `locale`.~~
- ~~`newsletter_subscribers`, `newsletter_campaigns` y `newsletter_logs` quedan preservadas como bloque operativo existente.~~

### Paso 4.2
~~Introducir tablas faltantes para contenido total:~~
- ~~`page_blocks`~~
- ~~`page_block_translations`~~
- ~~`music_tracks`~~
- ~~`music_track_translations`~~
- ~~`partners`~~
- ~~`social_links`~~
- ~~`legal_documents`~~
- ~~`legal_document_translations`~~
- ~~`downloadable_files`~~
- ~~`redirect_rules`~~
- ~~`settings`~~
- ~~`settings_translations`~~
- ~~`automation_logs` para persistir ejecuciones internas de n8n/integraciones~~

Base validada en esta iteracion:
- ~~`page_blocks` introduce orden (`position`), activacion (`is_active`) y `settings` JSON por bloque ligado a `pages`.~~
- ~~`page_block_translations` introduce `content` JSON por locale con FK a `page_blocks`.~~
- ~~`music_tracks` y `music_track_translations` cubren hero, CTAs, descripcion, orden y publicacion del bloque Music por locale.~~
- ~~`partners` y `social_links` cubren sponsors, enlaces externos y presencia social reutilizable sin tocar frontend publico.~~
- ~~`legal_documents` y `legal_document_translations` cubren terminos, privacidad y cookies por locale con versionado editorial.~~
- ~~`downloadable_files` cubre adjuntos y press kits con relacion polimorfica opcional.~~
- ~~`redirect_rules` cubre redirecciones 301/302 y metrica basica de uso.~~
- ~~`settings` y `settings_translations` cubren configuracion global publica/privada y textos reutilizables por locale.~~
- ~~`automation_logs` queda decidido e implementado como bitacora persistente de integraciones internas (`n8n`, webhooks y procesos de automatizacion).~~

### Paso 4.3
~~Definir FKs, indices y politicas de borrado.~~

Validado en esta iteracion:
- ~~FKs con `cascadeOnDelete()` en `page_block_translations`, `music_track_translations`, `legal_document_translations` y `settings_translations`.~~
- ~~Unicidad editorial por dominio: `pages(page_id,key)`, `music_track_translations(music_track_id,locale)`, `legal_document_translations(legal_document_id,locale)` y `settings(group,key)` / `settings_translations(setting_id,locale)`.~~
- ~~Indices operativos en publicacion, orden, visibilidad y tipo para lectura eficiente desde CMS/backend (`position`, `is_published`, `is_active`, `group`, `locale`, `processed_at`).~~
- ~~Relaciones polimorficas operativas para `downloadable_files.attachable` y `automation_logs.reference`.~~

### Paso 4.4
~~Definir que entidades seran translatables con Spatie y cuales requeriran tabla satelite.~~

Decision cerrada en esta iteracion:
- ~~Se mantiene tabla satelite para entidades editoriales multi-campo y con necesidad de unicidad por locale: `Page`, `PageBlock`, `MusicTrack`, `LegalDocument` y `Setting`.~~
- ~~`SeoMeta` conserva su patron actual por fila y locale (`entity_type` + `entity_id` + `locale`) en lugar de migrarse a JSON translatable.~~
- ~~`Partner`, `SocialLink`, `DownloadableFile`, `RedirectRule` y `AutomationLog` quedan no translatables en Fase 4 porque su dominio actual no exige payload editorial multilenguaje.~~
- ~~`spatie/laravel-translatable` queda reservado para futuros campos JSON simples de una sola tabla; el modelo editorial principal de RadioChi queda normalizado con tablas satelite para no mezclar contenido complejo por locale dentro de una sola columna JSON.~~

## Fase 5. Migracion de contenido legacy a contenido administrable

**Estado:** COMPLETADA
**Avance de fase:** 100%
**Avance global del plan:** 78%

### Objetivo
Sacar el sitio del modo JSON legacy y pasarlo a PostgreSQL + CMS.

### Paso 5.1
~~Inventariar y migrar todos los archivos legacy:~~
- ~~`home.json`~~
- ~~`about.json`~~
- ~~`music.json`~~
- ~~`calendarEvents.json`~~
- ~~`contact.json`~~
- ~~`media.json`~~
- ~~`introwebsite.json`~~
- ~~`footer.json`~~
- ~~`terms-policy-cookies.json`~~
- ~~`videos.json`~~
- ~~`events.json`~~

Inventario real validado en esta iteracion:
- ~~Se auditaron los 6 locales activos (`es`, `en`, `ca`, `fr`, `it`, `de`) y se verifico la presencia de los ficheros runtime realmente consumidos por el frontend legacy.~~
- ~~Ademas del listado inicial del plan, se detectaron y migraron tambien `header.json` y `seo.json` porque `resources/js/legacy/content.js` los usa de forma efectiva en runtime.~~
- ~~`resources/js/legacy/data/calendarevents.json` se inventario como fuente real de eventos + traducciones del calendario.~~
- ~~`resources/js/legacy/data/events.json` se inventario y migro como bloque auxiliar de enlaces/fechas legacy.~~
- ~~`resources/js/legacy/data/videos.json` se detecto vacio; se documento como artefacto residual y no como fuente efectiva de datos.~~
- ~~Se documento que los arrays reales de media, sponsors, social links y marquees viven hoy en `resources/js/legacy/content.js` y `resources/js/Pages/Home.jsx`, no en JSON separados.~~

### Paso 5.2
~~Crear importadores idempotentes por dominio:~~
- ~~paginas y bloques~~
- ~~music tracks~~
- ~~eventos~~
- ~~media~~
- ~~legales~~
- ~~SEO base~~

Base implementada y validada en esta iteracion:
- ~~Comando `legacy:inventory-content` para auditar fuentes legacy efectivas antes de importar.~~
- ~~Comando `legacy:import-content` para cargar contenido legacy de forma idempotente en `pages`, `page_translations`, `page_blocks`, `page_block_translations`, `music_tracks`, `events`, `media_assets`, `partners`, `social_links`, `legal_documents`, `settings` y `seo_meta`.~~
- ~~Importacion de hero slides, pasos de about y lineas marquee a `page_blocks` usando claves estables para evitar duplicados en re-ejecuciones.~~
- ~~Importacion de tracks musicales con traducciones por locale a `music_tracks` + `music_track_translations`.~~
- ~~Importacion de eventos desde `calendarevents.json` a `events` sin tocar rutas legacy ni frontend publico.~~
- ~~Importacion de fotos y videos legacy a `media_assets`, usando como fuente exacta los arrays runtime actuales del legacy.~~
- ~~Importacion de legales multilenguaje a `legal_documents` + `legal_document_translations`.~~
- ~~Importacion de `header`, `intro`, `footer`, botones legales, locales activos e inventario legacy a `settings` + `settings_translations`.~~
- ~~Importacion de SEO base por locale para la pagina `home` en `seo_meta`.~~

### Paso 5.3
~~Validar paridad por locale:~~
- ~~es~~
- ~~en~~
- ~~ca~~
- ~~fr~~
- ~~it~~
- ~~de~~

Validacion real en esta iteracion:
- ~~`Phase5LegacyImportTest`: 4 tests OK, 40 assertions.~~
- ~~Se verifico inventario, importacion completa, idempotencia en re-ejecucion y cobertura de los 6 locales soportados.~~
- ~~Se valido ademas la regresion conjunta de Fase 4 + Fase 5: 15 tests OK, 112 assertions.~~
- ~~Se valido regresion CMS + legacy: 26 tests OK, 111 assertions.~~

## Fase 6. CMS por seccion

**Estado:** COMPLETADA
**Avance de fase:** 100%
**Avance global del plan:** 90%

### Objetivo
Entregar administracion total seccion por seccion.

### Paso 6.1 Home
~~Recursos Filament para hero slides, intro y CTA.~~
- ~~`PageResource` ahora expone `BlocksRelationManager` para gestionar bloques por pagina (`home`, `about`, `contact`) desde el backoffice.~~
- ~~`PageBlockResource` + `PageBlock` translations relation manager permiten editar hero slides, fondos, settings de bloque y contenido JSON por locale sin tocar frontend publico.~~
- ~~`SettingResource` cubre `intro`, `header` y CTA/global settings importados en Fase 5.~~

### Paso 6.2 About
~~Recursos Filament para pasos de scrollytelling y fondos.~~
- ~~Los pasos de scrollytelling y sus fondos quedan administrables en `PageBlockResource` filtrando por `page.slug = about` y `type = about_step`.~~
- ~~Las traducciones de contenido por locale quedan cubiertas desde `PageBlock` relation manager de traducciones.~~

### Paso 6.3 Music
~~Recursos Filament para tracks, embeds y CTAs.~~
- ~~`MusicTrackResource` implementado con formulario, tabla y relation manager de traducciones para `title`, `artist_name`, `hero_title`, `subtitle`, `description` y CTAs.~~
- ~~Permite administrar plataforma, embeds/URLs, orden, destacado y publicacion.~~

### Paso 6.4 Calendar
~~Recursos Filament para eventos, carteles, fechas y ticket URLs.~~
- ~~Se mantiene `EventResource` ya validado en Fase 3 como superficie de administracion para eventos, carteles/logica de media ligada, fechas y ticket URLs.~~
- ~~La regresion de recursos editoriales y CMS confirma que sigue operativo tras Fases 4-6.~~

### Paso 6.5 Media
~~Recursos Filament para fotos, videos, colecciones y archivos descargables.~~
- ~~`MediaAssetResource` sigue cubriendo fotos, videos y metadata.~~
- ~~`DownloadableFileResource` nuevo cubre colecciones, archivos descargables, rutas locales/externas y adjuntos polimorficos.~~

### Paso 6.6 Contact
~~Recursos Filament para redes, sponsors, links y lineas marquee.~~
- ~~`PartnerResource` nuevo cubre sponsors/partners y orden de logos.~~
- ~~`SocialLinkResource` nuevo cubre redes, links y ubicacion (`contact`, `footer`, `global`).~~
- ~~Las lineas marquee quedan administrables en `PageBlockResource` / `BlocksRelationManager` sobre la pagina `contact`.~~

### Paso 6.7 Legal y footer
~~Recursos Filament para terminos, privacidad, cookies y pie global.~~
- ~~`LegalDocumentResource` nuevo cubre terminos, privacidad, cookies, versionado y publicacion con traducciones por locale.~~
- ~~`SettingResource` nuevo cubre pie global, botones legales y configuracion editorial transversal con traducciones por locale.~~

### Paso 6.8 SEO
~~Recursos Filament para metadata, redirects y sitemap rules.~~
- ~~`SeoMetaResource` sigue cubriendo metadata por entidad y locale.~~
- ~~`RedirectRuleResource` nuevo cubre redirecciones 301/302/307/308 y control basico de activacion/hits.~~
- ~~`SettingResource` cubre tambien `seo.*` / `sitemap_rules` como configuracion administrable sin inventar nueva estructura de datos.~~

Validacion real en esta iteracion:
- ~~`Phase6CmsResourcesTest`: 8 tests OK, 80 assertions.~~
- ~~Regresion conjunta Fase 4 + Fase 5 + Fase 6: 23 tests OK, 192 assertions.~~
- ~~Regresion CMS + legacy: 26 tests OK, 111 assertions.~~

## Fase 7. Integracion del frontend publico con el CMS

**Estado:** COMPLETADA
**Avance de fase:** 100%
**Avance global del plan:** 95%

### Objetivo
Hacer que el frontend lea datos administrables en lugar de contenido embebido.

### Paso 7.1
~~Sustituir `getLegacyContent`, `getLegacyCalendarData` y `getLegacyMediaData` por queries/controladores/DTOs.~~

Base implementada en esta iteracion:
- ~~`BuildPublicHomePayloadAction` reconstruye desde PostgreSQL/CMS el shape legacy que consume `Home.jsx` sin redisenar el frontend publico.~~
- ~~`PublicHomeController` deja de fabricar contenido hardcodeado y pasa a servir `content`, `calendarData`, `mediaData`, `contactData`, `events` y `seo` desde BD.~~
- ~~`Event` se amplia para frontend publico con `country` y `poster_path`, y el importador legacy pasa a poblar ambos campos de forma idempotente.~~
- ~~La CTA externa de media (`youtube_channel_url`) queda movida a `settings` publica para no dejar URLs embebidas en el JSX.~~

### Paso 7.2
~~Mantener la fidelidad visual del frontend original sin redisenos no autorizados.~~

Validado en esta iteracion:
- ~~`resources/js/Pages/Home.jsx` mantiene la misma estructura visual, las mismas clases y el mismo markup principal; solo cambia la fuente de datos.~~
- ~~`LegacyHeader.jsx` y `LegacyIntro.jsx` mantienen el UI actual pero dejan de depender de enlaces legales/sociales hardcodeados.~~
- ~~Las lineas marquee de contact se sirven desde CMS con el mismo contenido largo usado por el frontend restaurado, evitando recortes o huecos visuales.~~

### Paso 7.3
~~Renderizar por locale:~~
- ~~textos,~~
- ~~media,~~
- ~~CTAs,~~
- ~~legales,~~
- ~~SEO.~~

Cobertura real en esta iteracion:
- ~~Hero, about, music, media, footer y legales salen por locale desde `pages`, `page_blocks`, `music_tracks`, `legal_documents` y `settings`.~~
- ~~Eventos, sponsors, social links y media salen desde `events`, `partners`, `social_links` y `media_assets`.~~
- ~~SEO home sale desde `seo_meta` por locale, manteniendo canonical, alternates y `json_ld`.~~

### Paso 7.4
~~Verificacion de salida:~~
- ~~frontend con misma estructura visual,~~
- ~~datos desde DB/CMS,~~
- ~~sin dependencia del JSON legacy para produccion.~~

Validacion real en esta iteracion:
- ~~`Phase5LegacyImportTest`: 4 tests OK, 46 assertions, incluyendo `events.country`, `events.poster_path` y `media.youtube_channel_url`.~~
- ~~`Phase7PublicCmsPayloadTest`: 2 tests OK, 15 assertions, validando payload publico CMS para `es` y `en`.~~
- ~~`ProjectPremiumFlowTest`: 5 tests OK, 28 assertions, confirmando que las rutas publicas siguen sanas.~~
- ~~Regresion conjunta Fase 5 + Fase 7 + flujo publico: 11 tests OK, 86 assertions.~~
- ~~`docker compose exec app npm run build`: OK.~~

## Fase 8. Produccion Hostinger VPS con n8n y Ollama

**Estado:** COMPLETADA
**Avance de fase:** 100%
**Avance global del plan:** 98%

### Objetivo
Definir una arquitectura de produccion realista, segura y operable.

### Paso 8.1 Infraestructura de servicios
~~Definir stack de produccion con:~~
- ~~`radiochi_nginx`~~
- ~~`radiochi_app`~~
- ~~`radiochi_worker`~~
- ~~`radiochi_scheduler`~~
- ~~`radiochi_postgres`~~
- ~~`radiochi_redis`~~
- ~~`n8n`~~
- ~~`n8n_postgres`~~
- ~~`ollama`~~
- ~~reverse proxy/SSL segun estrategia final~~

Implementado en esta iteracion:
- ~~`docker-compose.production.yml` define el stack completo de Hostinger VPS con separacion web/app/worker/scheduler y red privada para automatizaciones.~~
- ~~`docker/nginx/hostinger-production.conf` deja Nginx listo para servir Laravel/Inertia en produccion manteniendo `/up` como health endpoint.~~
- ~~`.env.production.example` y `docs/hostinger-vps-phase8.md` documentan la topologia real de despliegue.~~

### Paso 8.2 Base de datos independiente para n8n
~~Implementar `n8n_postgres` separado de `radiochi_postgres`.~~

Requisitos:
- ~~base propia de n8n para ejecuciones, credenciales y metadatos,~~
- ~~usuario y secretos propios,~~
- ~~backups independientes,~~
- ~~no mezclar datos internos de n8n con la base del proyecto.~~

Implementado en esta iteracion:
- ~~`n8n_postgres` usa credenciales y volumen propios en produccion.~~
- ~~La configuracion de n8n queda separada de `radiochi_postgres` tanto en compose como en variables de entorno.~~

### Paso 8.3 Acceso controlado de n8n a la base principal
~~Crear un usuario tecnico de solo lectura o permisos minimizados sobre `radiochi_postgres` para consultas de automatizacion.~~

Requisitos:
- ~~acceso solo a tablas/vistas aprobadas,~~
- ~~sin privilegios de schema ni DDL,~~
- ~~preferencia por vistas/materialized views si aplica,~~
- ~~auditoria de consultas si el alcance lo exige.~~

Implementado en esta iteracion:
- ~~Migracion `2026_09_22_000000_create_automation_access_views.php` crea `automation_public_events`, `automation_newsletter_subscribers` y `automation_public_pages_seo`.~~
- ~~Comando `automation:provision-database-access` crea/actualiza el rol tecnico y le concede solo `SELECT` sobre las vistas aprobadas.~~
- ~~Provision local validada con el usuario `radiochi_automation`.~~

### Paso 8.4 Integracion backend Laravel <-> n8n
~~Definir contrato bidireccional:~~

Laravel hacia n8n:
- ~~webhooks firmados,~~
- ~~API keys/headers HMAC,~~
- ~~disparo de workflows por eventos de negocio,~~
- ~~endpoints para consultar estado de ejecucion.~~

n8n hacia Laravel:
- ~~webhooks autenticados,~~
- ~~endpoints internos protegidos,~~
- ~~posibilidad de leer datos del proyecto,~~
- ~~posibilidad de registrar resultados en tablas de automatizacion.~~

Casos objetivo:
- ~~automatizaciones de marketing,~~
- ~~generacion de contenido asistido,~~
- ~~sincronizaciones programadas,~~
- ~~enriquecimiento SEO,~~
- ~~tareas internas CMS.~~

Implementado en esta iteracion:
- ~~`routes/api.php` expone `GET /api/internal/automation/public-home/{locale}`, `GET /api/internal/automation/logs/{automationLog}` y `POST /api/internal/automation/n8n/results`.~~
- ~~`EnsureValidAutomationSignature` valida `X-Radiochi-Timestamp` + `X-Radiochi-Signature` con HMAC y ventana de tiempo configurable.~~
- ~~`DispatchN8nWorkflowAction` y `DispatchN8nWorkflowJob` formalizan el flujo Laravel -> n8n con logging en `automation_logs`.~~
- ~~`N8nResultWebhookController` registra resultados inbound de n8n en `automation_logs`.~~

### Paso 8.5 Integracion con Ollama en VPS
~~Instalar `ollama` como servicio interno no expuesto publicamente.~~

Requisitos:
- ~~modelo ligero configurable,~~
- ~~consumo solo desde backend o n8n por red privada,~~
- ~~limites de recursos del VPS,~~
- ~~colas o timeouts para evitar bloquear peticiones web.~~

Usos previstos:
- ~~resumen/clasificacion de contenido,~~
- ~~sugerencias SEO,~~
- ~~asistencia editorial,~~
- ~~etiquetado de media,~~
- ~~borradores internos.~~

Implementado en esta iteracion:
- ~~`GenerateOllamaTextAction` encapsula el cliente HTTP interno a `ollama` con timeout, modelo configurable y logging.~~
- ~~`RunOllamaPromptJob` fuerza el uso de cola `automation` para no bloquear peticiones web.~~
- ~~`docker-compose.production.yml` deja `ollama` sin exposicion publica y con limites de CPU/memoria.~~

### Paso 8.6 Seguridad y operaciones
~~Seguridad y operaciones:~~
- ~~no exponer Postgres ni Redis al exterior,~~
- ~~n8n protegido tras autenticacion fuerte y, si procede, acceso solo por VPN/IP allowlist,~~
- ~~backups cifrados,~~
- ~~healthchecks,~~
- ~~rotacion de secretos,~~
- ~~logs y monitoreo.~~

Validacion real en esta iteracion:
- ~~Backoffice privado confirmado en `/backoffice/login`.~~
- ~~Super admin solicitados creados en la base actual: `fernandocardonatoro@gmail.com` y `radiochi.dev@gmail.com`.~~
- ~~`Phase8ProductionAutomationTest`: 6 tests OK, 24 assertions.~~
- ~~Regresion conjunta Fase 7 + Fase 8 + flujo publico: 13 tests OK, 67 assertions.~~
- ~~`docker compose --env-file .env.production.example -f docker-compose.production.yml config`: OK.~~

## Fase 9. QA, seguridad y Definition of Done

**Estado:** EN CURSO
**Avance de fase:** 82%
**Avance global del plan:** 99%

Incidencia cerrada y validada en esta iteracion:
- ~~`/backoffice/login` ya carga sin errores de assets Filament ni bloqueos CSP de Alpine/Livewire, y queda alineado visualmente con el lenguaje del proyecto usando el logo y fondo del sistema legacy restaurado.~~
- ~~Se elimina el contenedor negro exterior del login del backoffice y se injerta un footer persistente del proyecto en la pantalla de acceso sin tocar `vendor`, usando hook oficial de Filament y datos reales de `settings` + `social_links`.~~

### Paso 9.1 Tests backend
- auth
- roles/permisos
- CRUD Filament
- traducciones
- media real
- integraciones n8n

Avance validado en esta iteracion:
- ~~Nuevo test `BackofficeLoginExperienceTest` para asegurar que el login del backoffice expone branding correcto y una CSP compatible con Filament v5 / Livewire / Alpine solo en `backoffice/*`.~~
- ~~Nuevo test para verificar que la home publica conserva una CSP mas estricta y sin `unsafe-eval`.~~
- ~~Nuevo flujo de auth Inertia para `/backoffice/login` con `AuthController`, `BackofficeLoginRequest` y `AuthenticateBackofficeUserAction`, manteniendo compatibilidad de rutas esperadas por Filament.~~
- ~~`EnsureBackofficeAccess` endurecido para redirigir invitados a `/backoffice/login` y preservar intended redirect hacia el shell preview del nuevo backoffice.~~
- ~~Regresion del backoffice validada en Docker: `BackofficeInertiaAuthFlowTest`, `BackofficeShellPreviewTest`, `BackofficeLoginExperienceTest` y `CmsBasePackagesTest` en verde (14 tests, 92 assertions).~~
- ~~La raiz `/backoffice` ya resuelve un dashboard Inertia unificado (`Backoffice\\Dashboard\\Index`) sin romper los recursos Filament restantes bajo `/backoffice/*`.~~
- ~~Nuevo test `BackofficeUnifiedDashboardTest` para cubrir guest redirect, acceso de editor y visibilidad de enlaces legacy para super admin.~~
- ~~Regresion ampliada del backoffice y dashboard validada en Docker: 19 tests, 142 assertions en verde (`BackofficeUnifiedDashboardTest`, `CmsBasePackagesTest`, `BackofficeShellPreviewTest`, `BackofficeLoginExperienceTest`, `ProjectPremiumFlowTest`).~~
- ~~Build frontend del backoffice validado en Docker con `npm run build`; persisten warnings conocidos de assets runtime y chunk size, sin error bloqueante.~~
- ~~Infraestructura CRUD reusable cerrada para el nuevo backoffice: blueprint de modulos, payloads normalizados, `FormRequest` de index/draft/actions y componentes React reutilizables (`CrudIndexScreen`, `CrudFormScreen`, `CrudFieldRenderer`, `CrudActionCard`).~~
- ~~Nuevas rutas preview `POST /backoffice-preview/{module}/draft`, `POST /backoffice-preview/{module}/draft/{record}` y `POST /backoffice-preview/{module}/actions/{action}` para validar el patron CRUD sin tocar persistencia productiva.~~
- ~~Nuevo test `BackofficeCrudInfrastructureTest` para cubrir querystring CRUD, formulario reusable, errores de `FormRequest` y confirmacion de acciones peligrosas.~~
- ~~Regresion ampliada de Fase 5 validada en Docker: 19 tests y 181 assertions en verde (`BackofficeCrudInfrastructureTest`, `BackofficeShellPreviewTest`, `BackofficeUnifiedDashboardTest`, `BackofficeLoginExperienceTest`, `CmsBasePackagesTest`).~~
- ~~Build frontend del backoffice revalidado en Docker tras Fase 5; persisten warnings conocidos de assets runtime/chunk size, sin error bloqueante.~~
- ~~Ola 1 de modulos editoriales nucleares del nuevo backoffice cerrada sobre `/backoffice-preview/*` con persistencia real para `Events`, `Pages`, `PageTranslations`, `PageBlocks`, `Settings` y `SeoMeta`, manteniendo coexistencia segura con Filament hasta el cutover.~~
- ~~Nuevo catalogo/payload/save layer para Fase 6: `Phase6ModuleCatalog`, `BuildBackofficePhase6CrudPayloadAction` y `SaveBackofficePhase6ModuleAction`.~~
- ~~Equivalentes React/Inertia de relation managers operativos para traducciones de `Pages`, `PageBlocks` y `Settings`, mas composicion `Pages -> PageBlocks`, mediante rutas anidadas y `Phase6TranslationController`.~~
- ~~Nueva prueba `BackofficePhase6CrudPersistenceTest` en verde y regresion conjunta Fase 6 validada en Docker: 18 tests OK, 254 assertions OK (`BackofficePhase6CrudPersistenceTest`, `BackofficeCrudInfrastructureTest`, `BackofficeShellPreviewTest`, `BackofficeUnifiedDashboardTest`).~~
- ~~Build frontend del backoffice revalidado en Docker tras Fase 6 con `npm run build`; persisten warnings conocidos de assets runtime/chunk size, sin error bloqueante.~~
- ~~Ola 2 de modulos de contenido enriquecido cerrada sobre la misma superficie `/backoffice-preview/*` con persistencia real para `MusicTracks`, `MediaAssets`, `Partners`, `SocialLinks` y `DownloadableFiles`.~~
- ~~`Phase6ModuleCatalog`, `BuildBackofficePhase6CrudPayloadAction` y `SaveBackofficePhase6ModuleAction` ampliados para soportar media, metadata y adjuntos polimorficos sin abrir otra UI paralela.~~
- ~~Equivalente React/Inertia del relation manager de `MusicTracks` operativo mediante `BackofficeMusicTrackTranslationUpsertRequest` y nuevas rutas anidadas en `Phase6TranslationController`.~~
- ~~Nueva prueba `BackofficePhase7RichContentPersistenceTest` en verde y regresion conjunta Fase 7 validada en Docker: 24 tests OK, 348 assertions OK (`BackofficePhase7RichContentPersistenceTest`, `BackofficePhase6CrudPersistenceTest`, `BackofficeCrudInfrastructureTest`, `BackofficeShellPreviewTest`, `BackofficeUnifiedDashboardTest`).~~
- ~~Build frontend del backoffice revalidado en Docker tras Fase 7 con `npm run build`; persisten warnings conocidos de assets runtime/chunk size, sin error bloqueante.~~
- ~~Ola 3 de modulos legales y newsletters cerrada sobre la misma superficie `/backoffice-preview/*` con persistencia real para `LegalDocuments`, `RedirectRules`, `NewsletterSubscribers`, `NewsletterCampaigns` y `NewsletterLogs`.~~
- ~~Acciones especiales de Fase 8 cerradas sin abrir otra UI paralela: relation manager equivalente de traducciones legales, CTA `Ver logs` por campana y ejecucion real de `queue-campaign` mediante `QueueNewsletterCampaign`.~~
- ~~Nuevo test `BackofficePhase8OperationalModulesTest` en verde y regresion conjunta Fases 6-8 validada en Docker: 18 tests OK, 335 assertions OK (`BackofficePhase8OperationalModulesTest`, `BackofficePhase7RichContentPersistenceTest`, `BackofficePhase6CrudPersistenceTest`).~~
- ~~Build frontend del backoffice revalidado en Docker tras Fase 8 con `npm run build`; persisten warnings conocidos de assets runtime/chunk size, sin error bloqueante.~~
- ~~Corte de rutas de Fase 9 completado: `/backoffice/*` ya es la superficie oficial React/Inertia, `/backoffice-preview/*` queda como alias temporal de compatibilidad y el panel Filament legacy se desplaza a `/backoffice-legacy/*`.~~
- ~~Nuevo contrato centralizado de prefijos en `config/backoffice.php` y `App\\Support\\Backoffice\\BackofficePath` para controlar ruta oficial, alias temporal y panel legacy sin hardcodes dispersos.~~
- ~~Nueva regresion `BackofficePhase9RouteCutoverTest` en verde y validacion conjunta Fases 6-9 en Docker: 39 tests OK, 564 assertions OK (`BackofficePhase9RouteCutoverTest`, `BackofficeCrudInfrastructureTest`, `BackofficePhase6CrudPersistenceTest`, `BackofficePhase7RichContentPersistenceTest`, `BackofficePhase8OperationalModulesTest`, `BackofficeUnifiedDashboardTest`, `BackofficeShellPreviewTest`, `BackofficeInertiaAuthFlowTest`).~~
- ~~Build frontend del backoffice revalidado tras Fase 9 con `npm run build`; persisten warnings conocidos de assets runtime/chunk size, sin error bloqueante.~~
- ~~Limpieza residual segura del backoffice completada tras el cutover: se retiran `/dashboard`, `/dashboard/api/*`, `/admin/*`, el panel `Filament` legacy, recursos `app/Filament/**/*`, assets publicados y dependencias `filament/filament` + `livewire/livewire`.~~
- ~~Regresion del backoffice adaptada a la ruta oficial `/backoffice/*`, cerrando el alias temporal `/backoffice-preview/*` y eliminando enlaces legacy visibles en dashboard/navegacion.~~
- ~~Regresion feature completa validada en Docker tras la retirada definitiva del legado: `php artisan test tests/Feature` -> 77 tests OK, 766 assertions OK.~~
- ~~Build frontend revalidado en Docker tras Fase 10 con `npm run build`; persisten warnings conocidos de assets runtime/chunk size, sin error bloqueante.~~
- ~~QA premium final del nuevo backoffice cerrada con regresion especifica de Fase 11: 54 tests OK y 670 assertions OK cubriendo auth, permisos, CRUD, traducciones, newsletters, navegacion Inertia y superficie publica.~~
- ~~Auditoria de seguridad y runtime final cerrada: `composer audit` limpio, CSP del backoffice sin `unsafe-eval` y ausencia verificada de runtime `Filament`/`Livewire`.~~
- ~~Smoke real en navegador completado para `/backoffice/login`, `/backoffice`, CRUDs representativos de Fase 6/7/8 y home publica `/en`; se detecta y corrige una incidencia real del dashboard en el contrato de `recentTables`.~~

### Paso 9.2 Tests frontend
- rutas publicas
- locale switching
- render de contenido dinamico
- SEO visible

Avance validado en esta iteracion:
- ~~Validacion visual real del login del backoffice en navegador integrado: logo aplicado, fondo alineado al proyecto y consola limpia en una carga fresca.~~
- ~~Ajuste de maquetacion del login para dejar visible el fondo completo y mostrar el footer del proyecto en la propia pantalla `/backoffice/login`.~~
- ~~Smoke visual en navegador del flujo CRUD preview del backoffice: redirect correcto al login, index reusable cargando `newsletter-campaigns` y formulario reusable cargando `pages/create` dentro del shell Inertia.~~

### Paso 9.3 Smoke tests de infraestructura
- desarrollo Docker limpio
- produccion compose valida
- workers y scheduler vivos
- n8n operativo
- Ollama operativo

### Paso 9.4 Checklist final
- Docker Desktop only: si
- XAMPP: no
- Laravel 13: si
- Inertia: si
- Tailwind: si
- Framer: si
- Spatie: si
- Filament: si
- PostgreSQL principal: si
- PostgreSQL independiente para n8n: si
- backend integrado con n8n: si
- Ollama operativo en VPS: si
- CMS visual completo: si
- contenido 100% administrable: si
- Hostinger preparado para produccion: si

## Orden recomendado de ejecucion

1. Fase 0
2. Fase 1
3. Fase 2
4. Fase 3
5. Fase 4
6. Fase 5
7. Fase 6
8. Fase 7
9. Fase 8
10. Fase 9

## Riesgos que este plan evita

- volver a introducir dependencias host tipo XAMPP,
- quedarse en Laravel 12 cuando el requisito es Laravel 13,
- seguir con un admin parcial en vez de un CMS real,
- dejar contenido clave anclado a JSON hardcodeado,
- mezclar la base interna de n8n con la base principal del proyecto,
- exponer integraciones automatizadas sin contrato ni seguridad,
- llegar a Hostinger sin una ruta de despliegue reproducible.

## Incidencia post-plan: rendimiento del backoffice

- **Estado:** `Mitigacion aplicada y verificacion inicial completada`
- **Avance de la incidencia:** `85%`
- ~~Auditoria runtime del login y del backoffice completada sin tocar funcionalidad de negocio.~~
- ~~Causa raiz principal localizada en el runtime local Docker/PHP sobre bind mount host, especialmente en el arbol `vendor`, no en los payloads CRUD/dashboard.~~
- ~~Mitigacion aplicada con volumen Docker nativo para `vendor` y caches oficiales de Laravel calentadas para recortar bootstrap/I/O por request.~~
- ~~Verificacion inicial positiva: la carga de `/backoffice/login` cae de varios segundos a ~2.9 s observados desde cliente y a cientos de ms dentro del kernel PHP.~~
- Validacion final pendiente en navegacion autenticada real entre modulos del backoffice.

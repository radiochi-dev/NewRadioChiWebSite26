# FASE 1. CONGELACION FUNCIONAL Y CONTRATO DE PARIDAD

## Objetivo

Congelar el backend actual **tal y como existe hoy** para que la futura migracion a `React + Inertia + Tailwind` no pierda:

- funcionalidad,
- permisos,
- maquetacion funcional,
- relaciones editoriales,
- validaciones,
- ni flujos legacy aun activos.

Este documento define la paridad **exigible** del nuevo backend.

## Fuentes auditadas para esta congelacion

- `routes/web.php`
- `app/Providers/Filament/BackofficePanelProvider.php`
- `app/Http/Middleware/EnsureSuperAdmin.php`
- `app/Http/Middleware/HandleInertiaRequests.php`
- `app/Http/Middleware/SecurityHeaders.php`
- `app/Http/Controllers/DashboardController.php`
- `app/Http/Controllers/Admin/*`
- `app/Filament/Resources/**/*`
- `resources/js/Pages/Dashboard/Index.jsx`
- `resources/css/filament/backoffice/theme.css`
- `resources/views/filament/backoffice/footer.blade.php`
- tests:
  - `BackofficeLoginExperienceTest.php`
  - `CmsBasePackagesTest.php`
  - `FilamentEditorialResourcesTest.php`
  - `Phase6CmsResourcesTest.php`
  - `ProjectPremiumFlowTest.php`

## 1. Inventario de pantallas y flujos actuales

### 1.1 Auth publica y admin

1. `GET /login`
   - no pinta un formulario propio;
   - redirige a `/`.

2. `POST /login`
   - login publico legacy.

3. `POST /logout`
   - logout de usuario autenticado.

4. `GET /backoffice/login`
   - login principal actual del backoffice.
   - hoy renderizado por Filament.

5. `POST /backoffice/logout`
   - logout del panel Filament.

### 1.2 Dashboards activos

1. `GET /backoffice`
   - dashboard principal actual del CMS.
   - hoy renderizado por Filament.

2. `GET /dashboard`
   - dashboard legacy Inertia.
   - protegido por `auth + EnsureSuperAdmin`.

### 1.3 Pantallas Filament activas por modulo

Cada recurso Filament tiene hoy el triángulo de pantallas:

- `index`
- `create`
- `edit`

Rutas reales:

| Modulo | Index | Create | Edit |
|---|---|---|---|
| DownloadableFiles | `/backoffice/downloadable-files` | `/backoffice/downloadable-files/create` | `/backoffice/downloadable-files/{record}/edit` |
| Events | `/backoffice/events` | `/backoffice/events/create` | `/backoffice/events/{record}/edit` |
| LegalDocuments | `/backoffice/legal-documents` | `/backoffice/legal-documents/create` | `/backoffice/legal-documents/{record}/edit` |
| MediaAssets | `/backoffice/media-assets` | `/backoffice/media-assets/create` | `/backoffice/media-assets/{record}/edit` |
| MusicTracks | `/backoffice/music-tracks` | `/backoffice/music-tracks/create` | `/backoffice/music-tracks/{record}/edit` |
| NewsletterCampaigns | `/backoffice/newsletter-campaigns` | `/backoffice/newsletter-campaigns/create` | `/backoffice/newsletter-campaigns/{record}/edit` |
| NewsletterSubscribers | `/backoffice/newsletter-subscribers` | `/backoffice/newsletter-subscribers/create` | `/backoffice/newsletter-subscribers/{record}/edit` |
| PageBlocks | `/backoffice/page-blocks` | `/backoffice/page-blocks/create` | `/backoffice/page-blocks/{record}/edit` |
| Pages | `/backoffice/pages` | `/backoffice/pages/create` | `/backoffice/pages/{record}/edit` |
| Partners | `/backoffice/partners` | `/backoffice/partners/create` | `/backoffice/partners/{record}/edit` |
| RedirectRules | `/backoffice/redirect-rules` | `/backoffice/redirect-rules/create` | `/backoffice/redirect-rules/{record}/edit` |
| SeoMetas | `/backoffice/seo-metas` | `/backoffice/seo-metas/create` | `/backoffice/seo-metas/{record}/edit` |
| Settings | `/backoffice/settings` | `/backoffice/settings/create` | `/backoffice/settings/{record}/edit` |
| SocialLinks | `/backoffice/social-links` | `/backoffice/social-links/create` | `/backoffice/social-links/{record}/edit` |

### 1.4 Flujos legacy aun operativos

#### Dashboard legacy Inertia

`/dashboard` expone una pantalla React/Inertia con estos tabs activos:

- `events`
- `media`
- `pages`
- `seo`
- `subscribers`
- `campaigns`
- `logs`

Cada tab usa `window.axios` contra `API = /dashboard/api`.

#### API admin con Basic Auth

Superficie aun viva bajo `/admin/*`:

- `events`
- `pages`
- `pages/{page}/translations`
- `media`
- `seo-meta`
- `newsletter-subscribers`
- `newsletter-campaigns`
- `newsletter-campaigns/{campaign}/queue`
- `newsletter-logs`
- `newsletter-campaigns/{campaign}/logs`

#### API dashboard interna

La misma superficie vive tambien bajo `/dashboard/api/*`.

## 2. Matriz de permisos congelada

### 2.1 Reglas actuales

- `User::hasBackofficeAccess()`
- `User::canViewBackofficeContent()`
- `User::canManageBackofficeContent()`
- `EnsureSuperAdmin`
- policies de cada modulo

### 2.2 Matriz obligatoria

| Rol | `/backoffice/*` | ver indices | create/edit/delete | `/dashboard` | `/dashboard/api/*` |
|---|---|---|---|---|---|
| `super_admin` | si | si | si | si | si |
| `editor` | si | si | si | no por `EnsureSuperAdmin` | no |
| `marketing` | si | si | si | no por `EnsureSuperAdmin` | no |
| `readonly` | si | si | no | no | no |
| legacy `SuperAdmin` | si | si | si | si | si |

### 2.3 Implicacion obligatoria para la migracion

El nuevo backoffice Inertia tiene que respetar esta dualidad:

- **panel backoffice**: acceso para `super_admin`, `editor`, `marketing`, `readonly`.
- **dashboard legacy / endpoints super admin**: acceso restringido a `super_admin`.

## 3. Contrato visual congelado

## 3.1 Login backoffice

Fuente:
- `BackofficePanelProvider.php`
- `resources/css/filament/backoffice/theme.css`
- `resources/views/filament/backoffice/footer.blade.php`

Contrato visual actual:

- modo oscuro fijo.
- logo `RC_Logo_white.svg`.
- fondo de gradiente animado multicolor sobre negro.
- textura `bg-texture-2.png`.
- caja principal sin contenedor exterior negro adicional.
- inputs pill redondeados.
- CTA submit en gradiente rosa -> morado -> cyan.
- footer inferior con:
  - credits
  - legal labels
  - social links

Esto es la referencia visual minima del futuro `/backoffice/login`.

## 3.2 Dashboard legacy Inertia

Fuente:
- `resources/js/Pages/Dashboard/Index.jsx`

Contrato visual actual:

- fondo `radial-gradient` oscuro cian/azul.
- titulo `Dashboard SuperAdmin`.
- boton `Refrescar`.
- boton `Cerrar sesión`.
- grid de 6 stats:
  - eventos
  - paginas
  - media
  - seo
  - subscriptores
  - campanas
- tabs pills para los 7 dominios.
- tablas con acciones inline `Editar`, `Borrar`, `Queue` donde aplica.

## 3.3 Shell admin funcional

Contrato funcional actual a conservar:

- navegación por grupos;
- index/create/edit por módulo;
- badges, iconos booleanos y filtros simples;
- experience clara de CRUD sin pasos ocultos;
- edición de relaciones desde la propia entidad padre donde ya exista.

No se exige replicar píxel por píxel el HTML interno de Filament, pero sí:

- misma jerarquía de información,
- mismas affordances de tabla/filtro/acción,
- y misma cobertura operativa real.

## 4. Contrato de props Inertia objetivo

## 4.1 Props globales obligatorias del futuro backoffice

El nuevo backoffice Inertia deberá compartir como mínimo:

```txt
auth.user
auth.roles
auth.capabilities
backoffice.navigation
backoffice.branding
backoffice.locale
flash.success
flash.error
```

## 4.2 Contrato de props para pantallas `Index`

Cada pantalla `Index` deberá exponer, como mínimo:

```txt
filters
sort
columns
rows
pagination
actions
capabilities
```

## 4.3 Contrato de props para pantallas `Create/Edit`

Cada pantalla `Create` / `Edit` deberá exponer, como mínimo:

```txt
mode
record
form
options
relations
capabilities
validation
```

## 4.4 Contrato de props para dashboard

`Backoffice/Dashboard/Index.jsx` deberá exponer, como mínimo:

```txt
stats
widgets
recent
capabilities
```

## 5. Matriz de rutas objetivo para la migracion

### 5.1 Regla general

Se mantiene `/backoffice/*` como entrada oficial del admin.

### 5.2 Mapa objetivo

| Ruta actual | Ruta objetivo | Observacion |
|---|---|---|
| `/backoffice/login` | `/backoffice/login` | misma URL, nueva UI Inertia |
| `/backoffice` | `/backoffice` | dashboard Inertia unificado |
| `/backoffice/<recurso>` | `/backoffice/<recurso>` | mantener slugs actuales para evitar ruptura |
| `/dashboard` | retirar tras corte | se absorbe en `/backoffice` |
| `/dashboard/api/*` | retirar tras corte | sustituido por controladores Inertia/HTTP del nuevo admin |
| `/admin/*` | retirar tras corte | sustituido por nueva superficie ya unificada |

## 6. Inventario modulo por modulo

## 6.1 Events

- grupo de navegación: `Agenda`
- label: `Eventos`
- rutas: `index/create/edit`
- fuente primaria de paridad UI: Filament
- fuente primaria de paridad HTTP legacy: `Admin/EventController.php`

### Tabla actual

- columnas:
  - `title`
  - `slug`
  - `location`
  - `event_starts_at`
  - `is_featured`
  - `is_published`
  - `published_at`
- orden por defecto:
  - `event_starts_at desc`
- filtros:
  - `is_featured`
  - `is_published`
- acciones:
  - `CreateAction` en index
  - `EditAction` por fila
  - `DeleteBulkAction`

### Form actual

- `slug` requerido y único
- `title` requerido
- `location`
- `external_url` URL
- `event_starts_at`
- `event_ends_at`
- `published_at`
- `is_featured`
- `is_published`
- `excerpt`
- `body`

### Validacion legacy

- `slug` requerido/único
- `title` requerido
- `external_url` URL
- fechas válidas
- flags booleanos

## 6.2 Pages

- grupo: `Editorial`
- label: `Paginas`
- fuente de paridad UI: Filament
- fuente de paridad HTTP legacy: `Admin/PageController.php` + `Admin/PageTranslationController.php`

### Tabla actual

- columnas:
  - `slug`
  - `template`
  - `translations_count`
  - `is_published`
  - `published_at`
  - `updated_at`
- orden:
  - `updated_at desc`
- filtros:
  - `is_published`
- acciones:
  - `CreateAction`
  - `EditAction`
  - `DeleteBulkAction`

### Form actual

- `slug` requerido y único
- `template` requerido
- `published_at`
- `is_published`

### Relation managers actuales

1. `TranslationsRelationManager`
   - columnas:
     - `locale`
     - `title`
     - `meta_title`
     - `updated_at`
   - orden:
     - `locale`
   - acciones:
     - crear
     - editar
     - delete bulk
   - campos:
     - `locale` requerido
     - `title` requerido
     - `meta_title`
     - `meta_description`
     - `content` JSON con helper para mantener estructura editorial
   - comportamiento:
     - `updateOrCreate` por `page_id + locale`

2. `BlocksRelationManager`
   - columnas:
     - `key`
     - `type`
     - `position`
     - `translations_count`
     - `is_active`
   - acciones:
     - crear
     - `editarCompleto` que redirige al edit global de `PageBlock`
     - delete bulk
   - campos:
     - `key` requerido
     - `type` requerido
     - `position` numérico
     - `is_active`
     - `settings` JSON/texto

### Validacion legacy

- `slug` requerido/único
- `template` requerido
- `is_published` boolean
- `published_at` date
- traducciones:
  - `locale` requerida y acotada a `es,en,ca,fr,it,de`
  - `title` requerido
  - `content` array

## 6.3 PageBlocks

- grupo: `Editorial`
- label: `Bloques de pagina`

### Tabla actual

- `page.slug`
- `key`
- `type`
- `position`
- `translations_count`
- `is_active`
- `updated_at`

### Filtros

- `page`
- `is_active`

### Form actual

- `page_id` requerido con relationship a `page.slug`
- `key` requerido
- `type` requerido
- `position` numérico
- `is_active`
- `settings` con helper de preservación estructural

### Relation manager actual

- `TranslationsRelationManager`
- campos:
  - `locale` requerida
  - `content` JSON/textarea con helper de preservación
- comportamiento:
  - `updateOrCreate` por bloque + locale

## 6.4 MusicTracks

- grupo: `Editorial`
- label: `Tracks musicales`

### Tabla actual

- `slug`
- `platform`
- `genre`
- `position`
- `translations_count`
- `is_featured`
- `is_published`
- `published_at`

### Filtros

- `platform`
- `is_featured`
- `is_published`

### Form actual

- `slug` requerido y único
- `platform` requerida
- `label_image_path`
- `cover_image_path`
- `stream_url` URL
- `external_url` URL
- `genre`
- `year` numérico
- `position` numérico
- `published_at`
- `is_featured`
- `is_published`
- `settings`

### Relation manager actual

- traducciones de track
- campos:
  - `locale` requerida
  - `artist_name`
  - `title` requerida
  - `hero_title`
  - `subtitle`
  - `cta_primary_label`
  - `cta_secondary_label`
  - `description`
- comportamiento:
  - `updateOrCreate`

## 6.5 MediaAssets

- grupo: `Media`
- label: `Assets`
- fuente legacy adicional: `Admin/MediaAssetController.php`

### Tabla actual

- `filename`
- `disk`
- `path`
- `mime_type`
- `size`
- `width`
- `height`
- `updated_at`

### Form actual

- `disk` requerido
- `path` requerido
- `filename` requerido
- `mime_type`
- `size` numérico
- `width` numérico
- `height` numérico
- `alt_text`
- `metadata` key/value

### Legacy API actual

- paginado `25`
- crea/actualiza con:
  - `disk`
  - `path`
  - `filename`
  - `mime_type`
  - `size`
  - `width`
  - `height`
  - `alt_text`
  - `metadata` array

## 6.6 DownloadableFiles

- grupo: `Media`
- label: `Archivos descargables`

### Tabla actual

- `display_name`
- `slug`
- `collection`
- `disk`
- `attachable_type`
- `position`
- `is_active`
- `updated_at`

### Filtros

- `is_active`

### Form actual

- `slug` requerido y único
- `display_name` requerido
- `disk` requerido
- `collection`
- `file_path`
- `file_name`
- `mime_type`
- `size` numérico
- `external_url` URL
- `attachable_type` con opciones cerradas
- `attachable_id` numérico
- `position` numérico
- `is_active`
- `description`
- `settings`

## 6.7 Partners

- grupo: `Contacto`
- label: `Sponsors`

### Tabla actual

- `name`
- `slug`
- `partner_type`
- `website_url`
- `position`
- `is_active`

### Filtros

- `partner_type`
- `is_active`

### Form actual

- `slug` requerido y único
- `name` requerido
- `partner_type` requerido
- `website_url` URL
- `logo_path`
- `position` numérico
- `is_active`
- `settings`

## 6.8 SocialLinks

- grupo: `Contacto`
- label: `Redes sociales`

### Tabla actual

- `label`
- `platform`
- `location`
- `url`
- `position`
- `is_active`

### Filtros

- `location`
- `is_active`

### Form actual

- `platform` requerido
- `label`
- `url` requerida y URL
- `icon_key`
- `location` requerida
- `position` numérico
- `is_active`
- `settings`

## 6.9 LegalDocuments

- grupo: `Legal y footer`
- label: `Documentos legales`

### Tabla actual

- `slug`
- `document_type`
- `version`
- `position`
- `translations_count`
- `is_published`
- `updated_at`

### Filtros

- `document_type`
- `is_published`

### Form actual

- `slug` requerido y único
- `document_type` requerido
- `version`
- `position` numérico
- `published_at`
- `is_published`
- `settings`

### Relation manager actual

- traducciones legales
- campos:
  - `locale` requerida
  - `title` requerida
  - `cta_label`
  - `summary`
  - `content` requerido
- comportamiento:
  - `updateOrCreate`

## 6.10 Settings

- grupo: `Configuracion`
- label: `Settings`

### Tabla actual

- `group`
- `key`
- `type`
- `translations_count`
- `is_translatable`
- `is_public`
- `position`

### Filtros

- `group`
- `is_translatable`
- `is_public`

### Form actual

- `group` requerido
- `key` requerido
- `type` requerido
- `position` numérico
- `is_translatable`
- `is_public`
- `value`
- `settings`

### Relation manager actual

- traducciones de setting
- campos:
  - `locale` requerida
  - `value`
- comportamiento:
  - `updateOrCreate`

## 6.11 RedirectRules

- grupo: `SEO`
- label: `Redirecciones`

### Tabla actual

- `source_path`
- `destination_url`
- `http_status`
- `locale`
- `is_active`
- `hit_count`

### Filtros

- `http_status`
- `is_active`

### Form actual

- `source_path` requerido y único
- `destination_url` requerido
- `http_status` requerido
- `locale`
- `hit_count` numérico
- `is_active`
- `notes`

## 6.12 SeoMetas

- grupo: `SEO`
- label: `SEO meta`
- fuente legacy adicional: `Admin/SeoMetaController.php`

### Tabla actual

- `entity_type`
- `entity_id`
- `locale`
- `meta_title`
- `canonical_url`
- `updated_at`

### Filtros

- `locale`

### Form actual

- identidad SEO:
  - `entity_type` requerido
  - `entity_id` requerida
  - `locale` requerida
- metadata:
  - `meta_title`
  - `meta_description`
  - `canonical_url` URL
- payloads JSON:
  - `open_graph`
  - `twitter_card`
  - `json_ld`
- helpers de preservación de estructura JSON legacy

### Comportamiento especial

- create en Filament hace `updateOrCreate`
- create en controller legacy hace `updateOrCreate`

## 6.13 NewsletterSubscribers

- grupo: `Marketing`
- label: `Suscriptores`
- fuente legacy adicional: `Admin/NewsletterSubscriberController.php`

### Tabla actual

- `email`
- `name`
- `is_active`
- `subscribed_at`
- `unsubscribed_at`

### Filtros

- `is_active`

### Form actual

- `email` requerida, email, única
- `name`
- `is_active`
- `subscribed_at`
- `unsubscribed_at`

### Comportamiento especial

- create Filament:
  - fuerza `subscribed_at = now()`
- edit Filament:
  - si `is_active = false`, pone `unsubscribed_at`
  - si `is_active = true`, limpia `unsubscribed_at`
- controller legacy reproduce la misma lógica

## 6.14 NewsletterCampaigns

- grupo: `Marketing`
- label: `Campanas`
- fuente legacy adicional: `Admin/NewsletterCampaignController.php`

### Tabla actual

- `name`
- `subject`
- `status`
- `scheduled_at`
- `sent_at`
- `sent_count`

### Filtros

- `status`

### Form actual

- `name` requerido
- `subject` requerido
- `status` requerido
- `scheduled_at`
- `sent_at`
- `sent_count` numérico
- `html_body` requerido

### Comportamientos especiales

- create Filament:
  - fuerza `status = draft`
- table action:
  - `queueCampaign`
- edit page:
  - también expone `queueCampaign`
- controller legacy:
  - endpoint `POST /newsletter-campaigns/{campaign}/queue`
  - cambia estado a `queued`
  - despacha `SendNewsletterCampaignJob` a cola `newsletter`

## 6.15 NewsletterLogs

- sin recurso Filament propio
- vive en dashboard legacy / API legacy

### Contrato actual

- listado paginado
- orden por `processed_at desc`
- endpoint general y por campaña

## 7. Fuente exacta de paridad por dominio

| Dominio | Fuente de paridad principal | Fuente secundaria obligatoria |
|---|---|---|
| Login backoffice | Filament login + theme + footer | `BackofficeLoginExperienceTest` |
| Dashboard principal | Filament dashboard + `BackofficeOverview` | dashboard Inertia legacy |
| Dashboard legacy | `Dashboard/Index.jsx` | `DashboardController.php` |
| Events | Filament resource | `Admin/EventController.php` |
| Pages | Filament resource + relation managers | `Admin/PageController.php` + `Admin/PageTranslationController.php` |
| PageBlocks | Filament resource | page relation manager |
| MusicTracks | Filament resource | relation manager de traducciones |
| MediaAssets | Filament resource | `Admin/MediaAssetController.php` |
| Partners | Filament resource | tests phase 6 |
| SocialLinks | Filament resource | tests phase 6 |
| LegalDocuments | Filament resource | relation manager de traducciones |
| Settings | Filament resource | relation manager de traducciones |
| DownloadableFiles | Filament resource | tests phase 6 |
| RedirectRules | Filament resource | tests phase 6 |
| SeoMetas | Filament resource | `Admin/SeoMetaController.php` |
| NewsletterSubscribers | Filament resource | `Admin/NewsletterSubscriberController.php` |
| NewsletterCampaigns | Filament resource | `Admin/NewsletterCampaignController.php` |
| NewsletterLogs | dashboard/api legacy | `Admin/NewsletterLogController.php` |

## 8. Lista exacta de residuos por ola de retirada

## 8.1 No borrar antes de completar paridad

- `app/Providers/Filament/BackofficePanelProvider.php`
- `app/Filament/**/*`
- `resources/css/filament/**/*`
- `resources/views/filament/**/*`
- `app/Actions/Backoffice/BuildBackofficeLoginFooterPayloadAction.php`
- assets publicados:
  - `public/css/filament/**/*`
  - `public/js/filament/**/*`
  - `public/fonts/filament/**/*`
- rama CSP para `/backoffice/*`
- `resources/js/Pages/Dashboard/Index.jsx`
- `app/Http/Controllers/DashboardController.php`
- `app/Http/Controllers/Admin/*`
- rutas `/dashboard`, `/dashboard/api/*`, `/admin/*`

## 8.2 Retirada en ola de corte

Solo tras sustitución validada:

1. dashboard legacy:
   - `DashboardController`
   - `resources/js/Pages/Dashboard/Index.jsx`
   - rutas `/dashboard`

2. API legacy:
   - `app/Http/Controllers/Admin/*`
   - rutas `/dashboard/api/*`
   - rutas `/admin/*`

3. Filament:
   - panel provider
   - resources
   - views/css de filament
   - dependencias composer
   - CSP especial
   - assets publicados

## 9. Definicion de completitud de Fase 1

La Fase 1 se considera completa cuando:

- existe inventario de pantallas,
- existe inventario por módulo,
- existe contrato de paridad funcional,
- existe contrato visual,
- existe matriz de permisos,
- existe mapa de rutas objetivo,
- existe contrato de props objetivo,
- existe lista exacta de residuos por ola,
- y todo ello está basado en el código real auditado.

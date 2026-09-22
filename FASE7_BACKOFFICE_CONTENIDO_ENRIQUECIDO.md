# FASE 7. MODULOS DE CONTENIDO ENRIQUECIDO

## Objetivo real ejecutado

Extender la misma superficie React + Inertia + Tailwind de `/backoffice-preview/*` para cubrir ya con persistencia real:

- `MusicTracks`
- `MediaAssets`
- `Partners`
- `SocialLinks`
- `DownloadableFiles`

La fase se ha implementado sin abrir una UI paralela y sin inventar un contrato distinto al que hoy fijan los recursos Filament, los modelos Eloquent y el controlador legacy de `MediaAsset`.

## Contrato funcional aplicado

### MusicTracks

- CRUD real sobre `music_tracks`
- relation manager equivalente para `music_track_translations`
- filtros reales por `platform`, `is_featured`, `is_published`
- orden editorial por `position`
- metadata JSON real en `settings`

### MediaAssets

- CRUD real sobre `media_assets`
- contrato heredado de `Admin/MediaAssetController`
- metadata JSON real en `metadata`
- dimensiones, MIME, peso, alt text y disco/ruta persistidos
- se mantiene el contrato actual basado en rutas/path, sin inventar un upload binario nuevo que el backend actual no exige

### Partners

- CRUD real sobre `partners`
- soporte de `partner_type`, `website_url`, `logo_path`, `position`, `is_active`

### SocialLinks

- CRUD real sobre `social_links`
- soporte de `platform`, `label`, `url`, `icon_key`, `location`, `position`, `is_active`
- ubicaciones alineadas a la superficie pública: `global`, `contact`, `footer`

### DownloadableFiles

- CRUD real sobre `downloadable_files`
- soporte de archivo local o URL externa
- adjunto polimórfico real mediante `attachable_type` + `attachable_id`
- validación extra para no permitir referencias polimórficas rotas

## Backend Laravel ampliado

Se amplió la infraestructura reusable de Fase 6 en lugar de crear otra capa:

- `app/Support/Backoffice/Phase6ModuleCatalog.php`
- `app/Actions/Backoffice/BuildBackofficePhase6CrudPayloadAction.php`
- `app/Actions/Backoffice/SaveBackofficePhase6ModuleAction.php`
- `app/Http/Requests/Backoffice/BackofficePreviewDraftRequest.php`

## Traducciones equivalentes

Se añadió equivalente React/Inertia del relation manager de `MusicTracks`:

- request dedicado: `BackofficeMusicTrackTranslationUpsertRequest`
- rutas anidadas de traducciones bajo `/backoffice-preview/music-tracks/{musicTrack}/translations/*`
- persistencia con `updateOrCreate(music_track_id, locale)`

Controlador implicado:

- `app/Http/Controllers/Backoffice/Phase6TranslationController.php`

## Validación y guardrails

Se endurecieron reglas reales para:

- `music_tracks.slug` único
- `partners.slug` único
- `downloadable_files.slug` único
- `platform`, `partner_type` y `location` con listas cerradas
- `metadata` / `settings` como payload JSON real
- referencias polimórficas de `DownloadableFiles` solo si el registro relacionado existe

## Cobertura validada

Pruebas ejecutadas en Docker:

- `BackofficePhase7RichContentPersistenceTest`
- `BackofficePhase6CrudPersistenceTest`
- `BackofficeCrudInfrastructureTest`
- `BackofficeShellPreviewTest`
- `BackofficeUnifiedDashboardTest`

Resultado:

- **24 tests OK**
- **348 assertions OK**

Build frontend en Docker:

- `docker compose exec app npm run build` OK
- persisten warnings conocidos de assets runtime y chunk size, sin error bloqueante

## Cierre funcional de la fase

La Fase 7 queda cerrada porque la nueva superficie React/Inertia ya administra en lectura/escritura real:

- tracks musicales,
- assets multimedia,
- sponsors,
- redes sociales,
- y archivos descargables,

manteniendo la convivencia segura con Filament hasta las fases de corte y limpieza.

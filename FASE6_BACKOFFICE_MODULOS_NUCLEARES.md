# FASE 6. MODULOS EDITORIALES NUCLEARES

## Objetivo real ejecutado

Extender la infraestructura reusable de la Fase 5 para que `Events`, `Pages`, `PageTranslations`, `PageBlocks`, `Settings` y `SeoMeta` funcionen ya con:

- lectura real desde base de datos,
- formularios React + Inertia reutilizables,
- validacion servidor en Laravel,
- persistencia real,
- permisos del backoffice ya congelados,
- y equivalentes a relation managers donde aplica.

Todo se ha implementado sobre `/backoffice-preview/*` para mantener la convivencia segura con Filament hasta la Fase 9.

## Backend Laravel implementado

- Catalogo real de modulos Fase 6 en `app/Support/Backoffice/Phase6ModuleCatalog.php`.
- Payloads reales de index/form en `app/Actions/Backoffice/BuildBackofficePhase6CrudPayloadAction.php`.
- Persistencia real de modulos y traducciones en `app/Actions/Backoffice/SaveBackofficePhase6ModuleAction.php`.
- `PreviewController` ampliado para conmutar entre:
  - preview estructural de Fase 5 para modulos aun no migrados,
  - y CRUD real para los modulos nucleares de Fase 6.
- Nuevas rutas anidadas para traducciones equivalentes a relation managers:
  - paginas,
  - bloques de pagina,
  - settings.
- Nuevo `Phase6TranslationController` para create/edit/store/update de traducciones.

## Validacion servidor cerrada

Se endurecio `BackofficePreviewDraftRequest` para los modulos reales de Fase 6 con reglas de:

- `unique` para `events.slug`, `pages.slug`, `page_blocks(page_id,key)` y `settings(group,key)`,
- `exists` para relaciones reales,
- `url`, `date`, `boolean`, `integer`,
- y decodificacion segura de payloads JSON para:
  - `settings`,
  - `page_blocks`,
  - `seo_meta`.

Ademas se crearon `FormRequest` dedicados para:

- `BackofficePageTranslationUpsertRequest`
- `BackofficePageBlockTranslationUpsertRequest`
- `BackofficeSettingTranslationUpsertRequest`

## Equivalentes a relation managers

### Pages

- panel de traducciones con listado real y acceso a create/edit,
- panel de bloques hijos con listado real y acceso a create/edit,
- persistencia de traducciones con `updateOrCreate(page_id, locale)`.

### PageBlocks

- panel de traducciones con listado real y acceso a create/edit,
- persistencia con `updateOrCreate(page_block_id, locale)`.

### Settings

- panel de traducciones con listado real y acceso a create/edit,
- persistencia con `updateOrCreate(setting_id, locale)`.

## React / Inertia reutilizado

No se ha creado una UI paralela.

Se ha mantenido el armazon reusable de la Fase 5 y se ha ampliado para soportar:

- submit real (`Guardar` / `Crear registro`),
- campos deshabilitados por contexto,
- paneles de relaciones con registros reales,
- enlaces reales a traducciones y composicion hija.

Archivos clave:

- `resources/js/Components/Backoffice/crud/CrudFormScreen.jsx`
- `resources/js/Components/Backoffice/crud/CrudFieldRenderer.jsx`

## Cobertura validada

Pruebas ejecutadas en Docker:

- `BackofficePhase6CrudPersistenceTest`
- `BackofficeCrudInfrastructureTest`
- `BackofficeShellPreviewTest`
- `BackofficeUnifiedDashboardTest`

Resultado:

- **18 tests OK**
- **254 assertions OK**

Build frontend en Docker:

- `docker compose exec app npm run build` OK
- persisten warnings conocidos de assets runtime y chunk size, sin error bloqueante.

## Cierre funcional de la fase

La Fase 6 queda cerrada porque ya existe administracion real en React/Inertia/Tailwind para el nucleo editorial sin romper la convivencia actual con Filament:

- `Events`
- `Pages`
- `PageTranslations`
- `PageBlocks`
- `Settings`
- `SeoMeta`

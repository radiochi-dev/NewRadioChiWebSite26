# FASE 9. CORTE DE RUTAS Y CONVIVENCIA CONTROLADA

## Estado

- Estado: COMPLETADA
- Avance de fase: 100%
- Objetivo alcanzado: `/backoffice/*` ya es la superficie oficial del backoffice React + Inertia

## Decisiones ejecutadas

### 1. Prefijos centralizados

- `config/backoffice.php`
  - `official_prefix`
  - `preview_alias_prefix`
  - `preview_alias_enabled`
  - `legacy_panel_path`

- `app/Support/Backoffice/BackofficePath.php`
  - `official()`
  - `active()`
  - `previewAlias()`
  - `legacyPanel()`

Esto evita seguir repartiendo rutas hardcodeadas por payloads, controladores y tests.

### 2. Superficie oficial

- `routes/web.php`
  - `/backoffice/login` y `/backoffice/logout` se mantienen como acceso oficial Inertia
  - `/backoffice/*` registra ya los CRUD React/Inertia y los flujos anidados de traducciones
  - `/backoffice-preview/*` permanece como alias temporal de compatibilidad

### 3. Legacy aislado

- `app/Providers/Filament/BackofficePanelProvider.php`
  - el panel Filament ya no vive en `/backoffice`
  - ahora queda movido a `/backoffice-legacy`

Con esto el corte de rutas queda limpio sin borrar todavia Filament/Livewire.

### 4. Payloads y redirects coherentes

Se ajustaron para usar el prefijo activo correcto:

- `app/Actions/Backoffice/BuildBackofficeCrudModulePayloadAction.php`
- `app/Actions/Backoffice/BuildBackofficePhase6CrudPayloadAction.php`
- `app/Http/Controllers/Backoffice/PreviewController.php`
- `app/Http/Controllers/Backoffice/Phase6TranslationController.php`
- `app/Support/Backoffice/CrudModuleBlueprintFactory.php`
- `app/Support/Backoffice/PreviewModuleRegistry.php`

Resultado:

- si entras por `/backoffice/*`, formularios y redirects permanecen en `/backoffice/*`
- si entras por `/backoffice-preview/*`, el alias sigue siendo funcional durante la convivencia

### 5. Navegacion y dashboard

Se marcan como legacy:

- `/backoffice-legacy/*`
- `/dashboard`
- `/dashboard/api/*`

Archivos actualizados:

- `app/Actions/Backoffice/BuildBackofficeNavigationAction.php`
- `app/Actions/Backoffice/BuildBackofficeDashboardPayloadAction.php`
- `resources/js/Pages/Backoffice/Dashboard/Index.jsx`
- `resources/js/Pages/Backoffice/Preview/Dashboard.jsx`

## Verificacion ejecutada

### Tests

Ejecutado en Docker:

```bash
docker compose exec app php artisan test tests/Feature/BackofficePhase9RouteCutoverTest.php tests/Feature/BackofficeCrudInfrastructureTest.php tests/Feature/BackofficePhase6CrudPersistenceTest.php tests/Feature/BackofficePhase7RichContentPersistenceTest.php tests/Feature/BackofficePhase8OperationalModulesTest.php tests/Feature/BackofficeUnifiedDashboardTest.php tests/Feature/BackofficeShellPreviewTest.php tests/Feature/BackofficeInertiaAuthFlowTest.php
```

Resultado:

- 39 tests OK
- 564 assertions OK

### Build

Ejecutado en Docker:

```bash
docker compose exec app npm run build
```

Resultado:

- build OK
- warnings ya conocidos de assets runtime y chunk size
- sin error bloqueante

## Criterio de salida alcanzado

- `/backoffice/*` ya es la entrada oficial del backoffice React/Inertia
- `/backoffice-preview/*` queda como alias temporal de compatibilidad
- `Filament` queda desplazado a `/backoffice-legacy/*`
- `/dashboard` y `/dashboard/api/*` siguen vivos solo como legacy en retirada

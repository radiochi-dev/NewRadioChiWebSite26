# FASE 10. Limpieza residual segura

## Objetivo

Cerrar el cutover de Fase 9 retirando solo el legado que ya no formaba parte del runtime real del backoffice React + Inertia.

## Alcance ejecutado

### 1. Superficies legacy retiradas

- `/dashboard`
- `/dashboard/api/*`
- `/admin/*`
- alias temporal `/backoffice-preview/*`
- panel legacy `/backoffice-legacy/*`

### 2. Runtime Filament/Livewire retirado

- `bootstrap/providers.php` deja de registrar `App\Providers\Filament\BackofficePanelProvider`
- eliminado `app/Providers/Filament/BackofficePanelProvider.php`
- eliminado `app/Filament/**/*`
- eliminado `resources/css/filament/**/*`
- eliminado `resources/views/filament/**/*`
- eliminado el dashboard legacy:
  - `app/Http/Controllers/DashboardController.php`
  - `resources/js/Pages/Dashboard/Index.jsx`
- eliminados controladores legacy de `app/Http/Controllers/Admin/*`

### 3. Contratos internos simplificados

- `config/backoffice.php` queda con `official_prefix` y mantiene `super_admins`
- `App\Support\Backoffice\BackofficePath` queda reducido a la superficie oficial
- `routes/web.php` deja solo `/backoffice/*` como superficie activa del backoffice
- `App\Http\Controllers\AuthController` ya redirige a `/backoffice`
- `App\Models\User` deja de implementar `FilamentUser`
- `App\Http\Middleware\SecurityHeaders` ya no necesita rama CSP especial con `unsafe-eval`
- `vite.config.js` deja de compilar `resources/css/filament/backoffice/theme.css`

### 4. Dependencias y artefactos retirados

- eliminadas de `composer.json` y `composer.lock`:
  - `filament/filament`
  - `livewire/livewire`
- eliminados assets publicados de Filament en:
  - `public/css/filament/**/*`
  - `public/js/filament/**/*`
  - `public/fonts/filament/**/*`

### 5. Suite de tests adaptada

- eliminados tests exclusivamente ligados a Filament:
  - `tests/Feature/FilamentEditorialResourcesTest.php`
  - `tests/Feature/Phase6CmsResourcesTest.php`
- migradas las regresiones del nuevo backoffice desde `/backoffice-preview/*` a `/backoffice/*`
- actualizado `ProjectPremiumFlowTest` para validar el flujo oficial del nuevo backoffice
- endurecido `BackofficePhase9RouteCutoverTest` para verificar:
  - 404 en `/backoffice-preview/*`
  - 404 en `/backoffice-legacy/*`
  - 404 en `/dashboard`, `/dashboard/api/*` y `/admin/*`

## Incidencia resuelta durante la fase

Tras retirar Livewire, una vista compilada de `resources/views/sitemap.blade.php` seguia cacheada con hooks de Livewire. Se resolvio con:

- `docker compose exec app php artisan view:clear`

Despues de limpiar la cache de vistas, la regresion completa volvio a verde.

## Validacion ejecutada

### Composer y runtime

- `docker compose exec app composer update --with-all-dependencies --no-interaction`

### Regresion

- `docker compose exec app php artisan view:clear`
- `docker compose exec app php artisan test tests/Feature`
  - resultado: `77 tests OK`
  - assertions: `766`

### Build

- `docker compose exec app npm run build`
  - resultado: `OK`
  - warnings conocidos no bloqueantes por assets resueltos en runtime y chunk size

## Estado de salida

- `/backoffice/*` es la unica superficie activa del backoffice
- el runtime admin ya no depende de Filament ni Livewire
- el repo queda listo para Fase 11 de QA premium y cierre definitivo

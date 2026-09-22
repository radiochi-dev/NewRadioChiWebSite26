# FASE 4. DASHBOARD UNIFICADO DEL BACKOFFICE

## Estado

- Fase: COMPLETADA
- Ambito: sustitucion de la raiz `/backoffice` por un dashboard Inertia nuevo, manteniendo convivencia segura con los recursos Filament y con el dashboard legacy `/dashboard`

## Objetivo cerrado

Reemplazar el dashboard legado por una superficie Inertia propia del backoffice que:

- use el shell React + Inertia ya validado en Fase 2,
- conserve las metricas reales del CMS expuestas por Filament,
- incorpore el contexto de sesion y acceso del usuario,
- mantenga los listados recientes del dashboard legacy,
- y no rompa los recursos Filament que siguen vivos bajo `/backoffice/*`.

## Auditoria previa que fijo el contrato real

Fuentes auditadas antes de implementar:

- `app/Http/Controllers/DashboardController.php`
- `resources/js/Pages/Dashboard/Index.jsx`
- `app/Filament/Widgets/BackofficeOverview.php`
- `app/Providers/Filament/BackofficePanelProvider.php`
- `app/Actions/Backoffice/BuildBackofficePreviewPageAction.php`
- `resources/js/Pages/Backoffice/Preview/Dashboard.jsx`

Hallazgo cerrado:

1. `Dashboard/Index.jsx` era un CRUD monolitico acoplado a `/dashboard/api/*`, no una base sana para el nuevo backoffice.
2. `BackofficeOverview` ya congelaba las metricas canonicas del CMS: eventos, paginas, media, SEO, suscriptores y campañas.
3. `AccountWidget` aportaba valor de sesion/contexto, no logica editorial.
4. El shell preview ya estaba listo para alojar el dashboard nuevo sin tocar el frontend publico.

Conclusion aplicada:

- no se reutiliza el JSX legacy,
- se extrae un payload serio del dashboard,
- y se monta una pagina nueva sobre `BackofficeLayout`.

## Implementacion realizada

### 1. Payload backend dedicado del dashboard

Se implemento:

- `app/Actions/Backoffice/BuildBackofficeDashboardPayloadAction.php`

Responsabilidades:

- contar dominios reales del CMS,
- resolver datos de sesion y rol del usuario,
- exponer accesos rapidos a recursos Filament,
- construir tablas recientes para eventos, paginas, media y campañas,
- y entregar un payload estable para Inertia.

### 2. Controlador propio del dashboard nuevo

Se implemento:

- `app/Http/Controllers/Backoffice/DashboardController.php`

Responsabilidad:

- renderizar `Backoffice/Dashboard/Index` usando el payload dedicado.

### 3. Nueva pagina Inertia del dashboard

Se implemento:

- `resources/js/Pages/Backoffice/Dashboard/Index.jsx`

Sobre el shell existente:

- `resources/js/Layouts/BackofficeLayout.jsx`
- `resources/js/Components/Backoffice/ui/DataTable.jsx`
- `resources/js/Components/Backoffice/ui/Button.jsx`

Superficie final:

- resumen con las metricas reales del CMS,
- bloque de sesion actual,
- criterio de convivencia,
- accesos rapidos a recursos,
- tablas recientes de actividad editorial.

### 4. Corte controlado de la raiz `/backoffice`

Se ajusto:

- `routes/web.php`

Resultado:

- `GET /backoffice` pasa a resolver el dashboard Inertia nuevo,
- conserva el nombre de ruta `filament.backoffice.pages.dashboard`,
- mantiene `backoffice.access` para redirects y control de acceso,
- y no toca las demas rutas Filament bajo `/backoffice/*`.

### 5. Convivencia segura con Filament

Se ajusto:

- `app/Providers/Filament/BackofficePanelProvider.php`

Cambio aplicado:

- se retira el registro del `Dashboard::class` de Filament para evitar conflicto de ruta en la raiz,
- pero se conservan recursos, widgets y resto del panel bajo `/backoffice/*`.

### 6. Navegacion y preview alineados con Fase 4

Se ajustaron:

- `app/Actions/Backoffice/BuildBackofficeNavigationAction.php`
- `app/Actions/Backoffice/BuildBackofficePreviewPageAction.php`

Resultado:

- la entrada principal del backoffice ahora apunta al dashboard nuevo,
- el shell preview sigue vivo como superficie de convivencia,
- y la trazabilidad de fase pasa a reflejar Fase 4.

## Paridad funcional alcanzada

### Equivalencia con `BackofficeOverview`

Queda reflejada en `summaryCards` con:

- Eventos
- Paginas
- Media
- SEO
- Suscriptores
- Campanas

### Equivalencia con `AccountWidget`

Queda reflejada en el panel de sesion con:

- nombre y email,
- roles activos,
- nivel de acceso al backoffice,
- links legacy visibles solo para super admin.

### Equivalencia con `DashboardController`

Queda reflejada con tablas recientes para:

- eventos,
- paginas,
- media,
- campañas.

## Archivos principales tocados en la fase

- `app/Actions/Backoffice/BuildBackofficeDashboardPayloadAction.php`
- `app/Actions/Backoffice/BuildBackofficeNavigationAction.php`
- `app/Actions/Backoffice/BuildBackofficePreviewPageAction.php`
- `app/Http/Controllers/Backoffice/DashboardController.php`
- `app/Providers/Filament/BackofficePanelProvider.php`
- `resources/js/Pages/Backoffice/Dashboard/Index.jsx`
- `routes/web.php`
- `tests/Feature/BackofficeUnifiedDashboardTest.php`

## Validacion ejecutada

### Rutas

Validado en Docker con:

- `php artisan route:list --path=backoffice`

Resultado relevante:

- `GET /backoffice` ya resuelve `Backoffice\DashboardController`
- el resto de rutas Filament bajo `/backoffice/*` siguen vivas

### Tests

Validado en Docker con:

- `php artisan test tests/Feature/BackofficeUnifiedDashboardTest.php tests/Feature/CmsBasePackagesTest.php tests/Feature/BackofficeShellPreviewTest.php tests/Feature/BackofficeLoginExperienceTest.php tests/Feature/ProjectPremiumFlowTest.php`

Resultado:

- 19 tests OK
- 142 assertions OK

Cobertura cerrada:

- redirect de guest a `/backoffice/login`
- acceso del editor al dashboard unificado
- visibilidad de links legacy para super admin
- compatibilidad base del panel Filament restante
- shell preview intacto
- login Inertia intacto
- `/dashboard` legacy sigue operativo

### Build frontend

Validado en Docker con:

- `npm run build`

Resultado:

- build OK
- se mantienen warnings ya conocidos de assets runtime no resueltos en build y warning de chunk grande, sin bloquear esta fase

## Criterio de salida alcanzado

La raiz `/backoffice` ya carga el dashboard Inertia nuevo sobre el shell del backoffice, mientras `/dashboard` queda formalmente marcado como legacy y los recursos Filament siguen conviviendo sin ruptura.

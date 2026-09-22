# FASE 5. INFRAESTRUCTURA CRUD REUTILIZABLE DEL BACKOFFICE

## Estado

- Fase: COMPLETADA
- Ambito: base reusable para indices, formularios, validacion, relaciones, acciones especiales y acciones peligrosas del nuevo backoffice React + Inertia

## Objetivo cerrado

Definir una infraestructura CRUD comun para no reimplementar desde cero cada modulo editorial en las fases 6-8.

La fase cierra los patrones base de:

- `Index / Create / Edit / Form`
- filtros, busqueda, ordenacion y paginacion
- tablas con acciones de fila y masivas
- validacion backend mediante `FormRequest`
- errores de formulario con `useForm` de Inertia
- uploads y previews
- relaciones y traducciones
- acciones peligrosas con confirmacion
- acciones de negocio especiales
- checklist minima de pruebas por modulo

## Auditoria previa que fijo la implementacion

Fuentes auditadas:

- `resources/js/Pages/Backoffice/Preview/ModuleIndex.jsx`
- `resources/js/Pages/Backoffice/Preview/ModuleForm.jsx`
- `app/Actions/Backoffice/BuildBackofficePreviewPageAction.php`
- `app/Support/Backoffice/PreviewModuleRegistry.php`
- `tests/Feature/BackofficeShellPreviewTest.php`
- `tests/Feature/FilamentEditorialResourcesTest.php`
- `tests/Feature/Phase6CmsResourcesTest.php`
- `tests/Feature/ProjectPremiumFlowTest.php`

Hallazgos cerrados:

1. El preview de Fase 2 seguia siendo demasiado placeholder para servir como base real de migracion modular.
2. El repo ya nos daba la referencia funcional de acciones especiales reales:
   - queue de newsletter campaign
   - relation managers de traducciones
   - bloques hijos de pagina
   - formularios con JSON estructurado
3. Los componentes base UI ya existian, pero todavia no expresaban un patron CRUD reusable de verdad.

## Implementacion realizada

### 1. Factorizacion del blueprint CRUD reusable

Se implemento:

- `app/Support/Backoffice/CrudModuleBlueprintFactory.php`

Responsabilidad:

- normalizar columnas, filtros y campos del registry actual,
- derivar tipos de campo, defaults y reglas de validacion,
- declarar relation managers, acciones especiales, acciones peligrosas y checklist de pruebas,
- generar filas sample para el preview con search/sort/filter/pagination,
- y centralizar el contrato reusable de cada modulo.

### 2. Payload backend reusable para indices y formularios

Se implemento:

- `app/Actions/Backoffice/BuildBackofficeCrudModulePayloadAction.php`

Responsabilidad:

- construir el payload estable del index CRUD,
- construir el payload estable del formulario CRUD,
- normalizar querystring y paginacion,
- preparar `bulkActions`, `rowActions`, `validationSummary`, `dangerousActions`, `specialActions` y `testChecklist`.

### 3. Validacion servidor con FormRequests

Se implementaron:

- `app/Http/Requests/Backoffice/BackofficePreviewIndexRequest.php`
- `app/Http/Requests/Backoffice/BackofficePreviewDraftRequest.php`
- `app/Http/Requests/Backoffice/BackofficePreviewActionRequest.php`

Patrones cerrados:

- validacion de querystring del index (`search`, `sort`, `direction`, `page`, `perPage`, `filters`)
- validacion de formularios create/edit via `FormRequest`
- validacion de confirmacion para acciones peligrosas

### 4. Endpoints preview para submit y acciones

Se ajustaron:

- `routes/web.php`
- `app/Http/Controllers/Backoffice/PreviewController.php`

Rutas nuevas:

- `POST /backoffice-preview/{module}/draft`
- `POST /backoffice-preview/{module}/draft/{record}`
- `POST /backoffice-preview/{module}/actions/{action}`

Resultado:

- ya existe un flujo real de submit preview con `useForm` + `FormRequest`
- ya existe un flujo real de accion preview con confirmacion y `flash`

### 5. Componentes React reutilizables del CRUD

Se implementaron:

- `resources/js/Components/Backoffice/crud/CrudIndexScreen.jsx`
- `resources/js/Components/Backoffice/crud/CrudFormScreen.jsx`
- `resources/js/Components/Backoffice/crud/CrudFieldRenderer.jsx`
- `resources/js/Components/Backoffice/crud/CrudActionCard.jsx`

Y se adaptaron:

- `resources/js/Components/Backoffice/ui/Input.jsx`
- `resources/js/Components/Backoffice/ui/Select.jsx`
- `resources/js/Components/Backoffice/ui/Textarea.jsx`
- `resources/js/Components/Backoffice/ui/FilterBar.jsx`
- `resources/js/Components/Backoffice/ui/DataTable.jsx`
- `resources/js/Components/Backoffice/ui/Pagination.jsx`

Patrones cerrados:

- formulario reusable con `useForm`
- errores inline por campo
- querystring via `router.get`
- sort por columnas
- bulk actions
- row actions
- pagination real del preview
- file input reusable para uploads
- paneles de relaciones/traducciones
- cards de acciones peligrosas y especiales

### 6. Pages preview ya montadas sobre la infraestructura reusable

Se simplificaron:

- `resources/js/Pages/Backoffice/Preview/ModuleIndex.jsx`
- `resources/js/Pages/Backoffice/Preview/ModuleForm.jsx`

Resultado:

- el preview ya no es una maqueta suelta
- ahora consume la misma infraestructura reusable que reutilizaran las fases 6-8

### 7. Dashboard preview alineado con la nueva fase

Se ajusto:

- `app/Actions/Backoffice/BuildBackofficePreviewPageAction.php`

Resultado:

- la preview general ya refleja Fase 5 como fase activa
- el copy del shell queda alineado con la base CRUD reusable

## Patrones cerrados por la fase

### Patron `Index / Create / Edit / Form`

Queda resuelto por:

- `CrudIndexScreen.jsx`
- `CrudFormScreen.jsx`
- `BuildBackofficeCrudModulePayloadAction.php`

### Patron filtros, busqueda y ordenacion

Queda resuelto por:

- `FilterBar.jsx`
- `BackofficePreviewIndexRequest.php`
- normalizacion de querystring en `BuildBackofficeCrudModulePayloadAction`

### Patron tablas con acciones fila/masivas

Queda resuelto por:

- `DataTable.jsx`
- `rowActions()` y `bulkActions()` del payload builder

### Patron validacion servidor + errores Inertia

Queda resuelto por:

- `BackofficePreviewDraftRequest.php`
- `CrudFormScreen.jsx`
- `Input.jsx`, `Select.jsx`, `Textarea.jsx`

### Patron uploads y preview

Queda resuelto por:

- `CrudFieldRenderer.jsx` para campos `file`
- reglas backend `file|max` en la factoría del blueprint

### Patron relaciones y traducciones

Queda resuelto por:

- `CrudModuleBlueprintFactory::relationManagers()`
- panel `Relaciones y traducciones` del formulario reusable

### Patron acciones peligrosas con confirmacion

Queda resuelto por:

- `CrudActionCard.jsx`
- `BackofficePreviewActionRequest.php`
- `PreviewController::action()`

### Patron acciones especiales de negocio

Queda resuelto por:

- `CrudModuleBlueprintFactory::specialActions()`
- `CrudActionCard.jsx`

Casos cubiertos ya en el blueprint:

- queue newsletter campaign
- relation managers de traducciones
- bloques hijos de pagina
- payloads JSON estructurados

### Patron de pruebas por modulo

Queda resuelto por:

- `CrudModuleBlueprintFactory::testChecklist()`
- `BackofficeCrudInfrastructureTest.php`

## Archivos principales tocados en la fase

- `app/Actions/Backoffice/BuildBackofficeCrudModulePayloadAction.php`
- `app/Actions/Backoffice/BuildBackofficePreviewPageAction.php`
- `app/Http/Controllers/Backoffice/PreviewController.php`
- `app/Http/Requests/Backoffice/BackofficePreviewActionRequest.php`
- `app/Http/Requests/Backoffice/BackofficePreviewDraftRequest.php`
- `app/Http/Requests/Backoffice/BackofficePreviewIndexRequest.php`
- `app/Support/Backoffice/CrudModuleBlueprintFactory.php`
- `resources/js/Components/Backoffice/crud/CrudActionCard.jsx`
- `resources/js/Components/Backoffice/crud/CrudFieldRenderer.jsx`
- `resources/js/Components/Backoffice/crud/CrudFormScreen.jsx`
- `resources/js/Components/Backoffice/crud/CrudIndexScreen.jsx`
- `resources/js/Components/Backoffice/ui/DataTable.jsx`
- `resources/js/Components/Backoffice/ui/FilterBar.jsx`
- `resources/js/Components/Backoffice/ui/Input.jsx`
- `resources/js/Components/Backoffice/ui/Pagination.jsx`
- `resources/js/Components/Backoffice/ui/Select.jsx`
- `resources/js/Components/Backoffice/ui/Textarea.jsx`
- `resources/js/Pages/Backoffice/Preview/ModuleForm.jsx`
- `resources/js/Pages/Backoffice/Preview/ModuleIndex.jsx`
- `routes/web.php`
- `tests/Feature/BackofficeCrudInfrastructureTest.php`

## Validacion ejecutada

### Rutas

Validado en Docker con:

- `php artisan route:list --path=backoffice-preview`

Resultado:

- 7 rutas activas del preview CRUD
- includes GET index/create/edit y POST draft/actions

### Tests

Validado en Docker con:

- `php artisan test tests/Feature/BackofficeCrudInfrastructureTest.php tests/Feature/BackofficeShellPreviewTest.php tests/Feature/BackofficeUnifiedDashboardTest.php tests/Feature/BackofficeLoginExperienceTest.php tests/Feature/CmsBasePackagesTest.php`

Resultado:

- 19 tests OK
- 181 assertions OK

Cobertura cerrada:

- querystring CRUD preview
- index reusable con filtros/sort/bulk actions
- readonly sin mutacion
- formulario reusable con relaciones, acciones especiales y resumen de validacion
- errores de `FormRequest` en preview
- confirmacion de acciones peligrosas
- convivencia intacta con shell, login y dashboard del backoffice

### Build frontend

Validado en Docker con:

- `npm run build`

Resultado:

- build OK
- se mantienen warnings conocidos de assets runtime no resueltos y warning de chunk size, sin bloquear esta fase

## Criterio de salida alcanzado

Existe ya una base reusable premium para migrar modulos reales del backoffice a velocidad controlada, sin rehacer index, form, validacion, acciones y paneles auxiliares cada vez.

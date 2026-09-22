# FASE 8. BACKOFFICE MODULOS LEGALES, SEO AVANZADO Y NEWSLETTERS

## Estado

- Estado: COMPLETADA
- Avance de fase: 100%
- Superficie afectada: `/backoffice-preview/*`

## Alcance cerrado

- `LegalDocuments`
- `RedirectRules`
- `NewsletterSubscribers`
- `NewsletterCampaigns`
- `NewsletterLogs`

## Implementacion cerrada

### Catalogo y payloads

- `app/Support/Backoffice/Phase6ModuleCatalog.php`
  - alta de los 5 modulos reales de Fase 8
  - `readOnly` para `newsletter-logs`
  - filtros, options y checklist especifico
  - accion especial `queue-campaign`
  - formulario equivalente para traducciones de `LegalDocuments`

- `app/Actions/Backoffice/BuildBackofficePhase6CrudPayloadAction.php`
  - index/form reales con BD para Fase 8
  - busqueda, filtros y ordenacion reales
  - relation manager equivalente de traducciones legales
  - relacion `campana -> logs` con CTA `Ver logs`
  - detalle read-only de `newsletter-logs`

### Persistencia y validacion

- `app/Actions/Backoffice/SaveBackofficePhase6ModuleAction.php`
  - persistencia real de `LegalDocuments`, `RedirectRules`, `NewsletterSubscribers` y `NewsletterCampaigns`
  - reutilizacion de `PrepareNewsletterSubscriberData`
  - `saveLegalDocumentTranslation()`

- `app/Http/Requests/Backoffice/BackofficePreviewDraftRequest.php`
  - reglas reales de Fase 8 por modulo
  - casts y normalizacion de datos

- `app/Http/Requests/Backoffice/BackofficeLegalDocumentTranslationUpsertRequest.php`
  - request dedicado para create/edit de traducciones legales

### Controladores, rutas y permisos

- `app/Http/Controllers/Backoffice/PreviewController.php`
  - bloqueo de mutacion en `newsletter-logs`
  - ejecucion real de `QueueNewsletterCampaign`

- `app/Http/Requests/Backoffice/BackofficePreviewActionRequest.php`
  - resolucion de acciones especiales del catalogo Fase 8

- `app/Http/Controllers/Backoffice/Phase6TranslationController.php`
  - create/edit/store/update de traducciones legales

- `routes/web.php`
  - rutas anidadas para traducciones de `LegalDocuments`

- `app/Policies/NewsletterLogPolicy.php`
  - policy dedicada para lectura coherente con `BackofficeContentPolicy`

### Relaciones Eloquent

- `app/Models/NewsletterCampaign.php` -> `logs()`
- `app/Models/NewsletterSubscriber.php` -> `logs()`
- `app/Models/NewsletterLog.php` -> `campaign()` y `subscriber()`

## Verificacion ejecutada

### Tests

Ejecutado en Docker:

```bash
docker compose exec app php artisan test tests/Feature/BackofficePhase8OperationalModulesTest.php tests/Feature/BackofficePhase7RichContentPersistenceTest.php tests/Feature/BackofficePhase6CrudPersistenceTest.php
```

Resultado:

- 18 tests OK
- 335 assertions OK

Suites:

- `BackofficePhase8OperationalModulesTest`
- `BackofficePhase7RichContentPersistenceTest`
- `BackofficePhase6CrudPersistenceTest`

### Build frontend

Ejecutado en Docker:

```bash
docker compose exec app npm run build
```

Resultado:

- build OK
- warnings no bloqueantes ya conocidos de assets runtime y chunk size

## Criterio de salida alcanzado

- La ola de modulos restantes del backoffice ya tiene equivalente React/Inertia sobre la infraestructura reusable.
- `newsletter-logs` queda cubierto como modulo real de solo lectura.
- `newsletter-campaigns` puede encolar campañas y consultar logs asociados.
- `legal-documents` ya soporta traducciones equivalentes al relation manager legacy.

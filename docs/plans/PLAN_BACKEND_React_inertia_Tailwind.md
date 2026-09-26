# PLAN BACKEND REACT + INERTIA + TAILWIND

## Objetivo

Sustituir el backoffice actual basado en `Filament + Livewire + Blade` por un backoffice propio basado en `Laravel + React + Inertia + Tailwind`, manteniendo:

- la misma cobertura funcional del backend actual,
- la misma paridad visual y de comportamiento que el backend existente,
- el frontend publico intacto,
- la seguridad, robustez y trazabilidad del proyecto,
- una limpieza residual controlada y sin borrados prematuros.

## Estado actual resumido

- **Frontend publico**: `React + Inertia + Tailwind`.
- **Backoffice oficial actual**: `React + Inertia + Tailwind` en `/backoffice/*`.
- **Superficies legacy retiradas en Fase 10**: `/dashboard`, `/dashboard/api/*`, `/admin/*` y runtime `Filament + Livewire`.
- **Stack base del repo**: `Laravel 13`, `React 19`, `Inertia 3`, `Tailwind 4`, `PostgreSQL`, `Docker Desktop`.

## Nota tecnica obligatoria

### Verdad arquitectonica

Un backend hecho con `React + Inertia + Tailwind` **no elimina Blade al 100%** a nivel tecnico, porque Inertia necesita una vista root de servidor, actualmente `resources/views/app.blade.php`.

Por tanto, el objetivo real y honesto es este:

- **eliminar Blade/Livewire como superficie UI del backoffice**, y
- **mantener solo el Blade tecnico minimo de arranque de Inertia** y otras vistas tecnicas no-UI que Laravel pueda necesitar.

No se promete una falsedad tipo "cero Blade absoluto" porque eso no seria riguroso con Inertia.

## Base documental oficial para esta migracion

Esta planificacion se apoya en:

- Laravel Starter Kits / React + Inertia:
  `https://laravel.com/docs/13.x/starter-kits`
- Inertia v3 server-side setup:
  `https://inertiajs.com/docs/v3/installation/server-side-setup`
- Inertia v3 client-side setup:
  `https://inertiajs.com/docs/v3/installation/client-side-setup`
- Inertia v3 forms:
  `https://inertiajs.com/docs/v3/the-basics/forms`
- Inertia v3 shared data:
  `https://inertiajs.com/docs/v3/data-props/shared-data`
- Inertia v3 code splitting:
  `https://inertiajs.com/docs/v3/advanced/code-splitting`
- Tailwind CSS v4 upgrade guide:
  `https://tailwindcss.com/docs/upgrade-guide`

## Fuentes de verdad del proyecto

- `docs/plans/PLAN-REMAKE.md`
- este archivo `PLAN_BACKEND_React_inertia_Tailwind.md`

## Criterio de paridad exacta

La referencia de "igual que lo que tenemos ahora" se interpretara asi, por orden de prioridad:

1. **Backoffice principal actual**: `Filament` bajo `/backoffice/*`.
2. **Superficie legacy aun operativa**: `/dashboard` y `/dashboard/api/*`.
3. **API admin residual aun viva**: `/admin/*`.

No se dara por valida una migracion porque "haga lo mismo de forma aproximada". La paridad exigida es:

- mismas rutas funcionales o rutas sustitutas documentadas,
- mismos permisos por rol,
- mismos formularios y validaciones,
- mismas acciones operativas,
- mismas relaciones editoriales,
- misma maquetacion funcional del backend,
- misma capacidad de administracion real del contenido,
- misma seguridad y mismos redirects esperados,
- y ausencia de residuos conflictivos al final del proceso.

## Auditoria real del repo en el momento de crear este plan

### Superficies admin activas

1. `routes/web.php` mantiene estas tres superficies:
   - `/backoffice/*` -> Filament
   - `/dashboard` -> Inertia legacy
   - `/dashboard/api/*` y `/admin/*` -> API admin legacy

2. El dashboard Inertia legacy actual existe en:
   - `app/Http/Controllers/DashboardController.php`
   - `resources/js/Pages/Dashboard/Index.jsx`

3. El panel Filament actual existe en:
   - `app/Providers/Filament/BackofficePanelProvider.php`
   - `app/Filament/**/*`
   - `resources/css/filament/backoffice/theme.css`
   - `resources/views/filament/backoffice/footer.blade.php`

4. Los controladores admin legacy reutilizables hoy son:
   - `app/Http/Controllers/Admin/EventController.php`
   - `app/Http/Controllers/Admin/MediaAssetController.php`
   - `app/Http/Controllers/Admin/NewsletterCampaignController.php`
   - `app/Http/Controllers/Admin/NewsletterLogController.php`
   - `app/Http/Controllers/Admin/NewsletterSubscriberController.php`
   - `app/Http/Controllers/Admin/PageController.php`
   - `app/Http/Controllers/Admin/PageTranslationController.php`
   - `app/Http/Controllers/Admin/SeoMetaController.php`

### Inventario real de rutas auditadas

- `backoffice/*`: **45 rutas**
- `dashboard` + `dashboard/api/*`: **38 rutas**
- `admin/*`: **37 rutas**

Implicacion:
- el backend actual no es una sola superficie, sino una convivencia de **tres capas activas**.
- la migracion debe planificarse como sustitucion de ecosistema admin, no solo de una pantalla.

### Modulos funcionales del backoffice actual

El backend actual cubre, como minimo, estos dominios:

1. Auth / login backoffice
2. Dashboard
3. Events
4. Pages
5. PageTranslations
6. PageBlocks
7. MusicTracks
8. MediaAssets
9. Partners
10. SocialLinks
11. LegalDocuments
12. Settings
13. DownloadableFiles
14. RedirectRules
15. SeoMeta
16. NewsletterSubscribers
17. NewsletterCampaigns
18. NewsletterLogs

### Superficies y comportamientos concretos ya verificados

- Login Filament en `/backoffice/login`.
- Logout Filament en `/backoffice/logout`.
- Dashboard Filament en `/backoffice`.
- Widget real `BackofficeOverview` con estadisticas de:
  - eventos
  - paginas
  - media
  - SEO
  - suscriptores
  - campanas
- `AccountWidget` activo en Filament.
- Dashboard legacy Inertia en `/dashboard`.
- CRUD legacy por API para:
  - eventos
  - media
  - pages
  - page translations
  - seo meta
  - newsletter subscribers
  - newsletter campaigns
  - newsletter logs

### Matriz real de acceso por rol

Segun `User.php` y `BackofficeContentPolicy.php`, hoy el backend funciona asi:

| Rol | Acceso al backoffice | Ver contenido | Crear/editar/borrar |
|---|---|---|---|
| `super_admin` | si | si | si |
| `editor` | si | si | si |
| `marketing` | si | si | si |
| `readonly` | si | si | no |
| legacy `SuperAdmin` | si | si | si |

Esto obliga a que el nuevo backend Inertia mantenga exactamente esta matriz salvo cambio expreso aprobado.

## Estado objetivo exacto

Al final de esta migracion, el backend debe quedar asi:

- `/backoffice/login` -> pagina React + Inertia + Tailwind
- `/backoffice/*` -> paginas React + Inertia + Tailwind
- sin Livewire en la UI del backoffice
- sin vistas Blade de UI para el backoffice
- con `controllers + FormRequests + policies + actions` en Laravel
- con props Inertia bien estructuradas
- con layouts y componentes React reutilizables
- con Tailwind como unico sistema de estilos utilitarios
- con limpieza final de Filament/Livewire y residuos asociados **solo despues del cutover validado**

## Principios de implementacion obligatorios

- Controllers finos.
- Validacion en `FormRequest`.
- Autorizacion en `Policies`.
- Logica de negocio en `Actions` o servicios de aplicacion.
- Props Inertia tipadas y estables por modulo.
- Nada de formularios masivos improvisados en una sola pagina gigante.
- Nada de estados React descontrolados mezclando varios modulos sin separacion.
- Nada de CSS residual si puede resolverse con Tailwind.
- Nada de borrado temprano de infraestructura Filament/Livewire.

## Restricciones no negociables

1. No tocar el frontend publico salvo orden expresa.
2. No borrar archivos activos del backoffice actual antes de alcanzar paridad real.
3. No hacer limpieza destructiva por intuicion.
4. No introducir Bootstrap, Blade UI nueva, Livewire ni librerias visuales no pedidas.
5. Mantener Docker Desktop como unico flujo de desarrollo local.
6. Validar cada corte con tests y smoke checks reales.

## Politica de limpieza residual

### Regla principal

**No se elimina nada de Filament/Livewire al principio.**

La limpieza residual se hara **por fases**, solo cuando:

- el modulo React/Inertia equivalente exista,
- la ruta nueva este operativa,
- la politica/permisos esten validados,
- los tests y smoke checks pasen,
- y el modulo viejo haya quedado formalmente sustituido.

### Residuo identificado hoy que NO debe borrarse todavia

- `app/Filament/**/*`
- `app/Providers/Filament/BackofficePanelProvider.php`
- `resources/css/filament/**/*`
- `resources/views/filament/**/*`
- `app/Actions/Backoffice/BuildBackofficeLoginFooterPayloadAction.php`
- `tests/Feature/BackofficeLoginExperienceTest.php`
- dependencias Composer:
  - `filament/filament`
  - `livewire/livewire`

Todo eso sigue siendo backend activo hoy.

### Residuos adicionales ya identificados y que no deben tocarse todavia

- rama CSP especial de `SecurityHeaders.php` para `backoffice/*`
- assets publicados de Filament en `public/css/filament/**/*`
- assets publicados de Filament en `public/js/filament/**/*`
- assets publicados de Filament en `public/fonts/filament/**/*`
- `BackofficeOverview` como referencia funcional del dashboard actual
- `AccountWidget` como referencia funcional de la cabecera/widget de cuenta actual

## Matriz de migracion modulo a modulo

| Dominio | Estado actual | Destino React/Inertia |
|---|---|---|
| Auth backoffice | Filament login + reglas de acceso | `Backoffice/Auth/Login.jsx` + controlador/session flow |
| Dashboard | `Dashboard/Index.jsx` legacy limitado | `Backoffice/Dashboard/Index.jsx` unificado |
| Events | Filament + API legacy | modulo React completo |
| Pages | Filament + API legacy | modulo React completo |
| PageTranslations | Filament + API legacy | integrado en Pages |
| PageBlocks | Filament | integrado en Pages |
| MusicTracks | Filament | modulo React |
| MediaAssets | Filament + API legacy | modulo React con uploads |
| Partners | Filament | modulo React |
| SocialLinks | Filament | modulo React |
| LegalDocuments | Filament | modulo React |
| Settings | Filament | modulo React |
| DownloadableFiles | Filament | modulo React |
| RedirectRules | Filament | modulo React |
| SeoMeta | Filament + API legacy | modulo React |
| NewsletterSubscribers | Filament + API legacy | modulo React |
| NewsletterCampaigns | Filament + API legacy | modulo React |
| NewsletterLogs | API legacy | modulo React integrado en newsletters |

## Baseline de pruebas que el nuevo backend debe igualar o superar

Suites ya existentes y obligatorias como referencia de cobertura:

- `BackofficeLoginExperienceTest.php`
- `CmsBasePackagesTest.php`
- `FilamentEditorialResourcesTest.php`
- `Phase6CmsResourcesTest.php`
- `ProjectPremiumFlowTest.php`

Cobertura minima ya verificada que el plan nuevo debe conservar:

- login backoffice y branding
- CSP admin vs CSP publica
- acceso por roles
- readonly sin acceso a create
- creacion de recursos editoriales
- relation managers de traducciones
- pages + blocks
- music tracks + translations
- legal documents + translations
- settings + translations
- newsletter queue action
- dashboard access flow
- API legacy actual mientras siga conviviente

La migracion no podra considerarse premium si al final ofrece menos cobertura de tests que esta base.

## Arquitectura objetivo recomendada

### Backend Laravel

- `app/Http/Controllers/Backoffice/*`
- `app/Http/Requests/Backoffice/*`
- `app/Http/Resources/Backoffice/*` si hace falta transformar colecciones complejas
- `app/Actions/*` para logica reutilizable
- `app/Policies/*` reutilizadas o endurecidas
- `routes/web.php` o `routes/backoffice.php` para la superficie admin Inertia

### Frontend React/Inertia

- `resources/js/Pages/Backoffice/Auth/Login.jsx`
- `resources/js/Pages/Backoffice/Dashboard/Index.jsx`
- `resources/js/Pages/Backoffice/<Modulo>/Index.jsx`
- `resources/js/Pages/Backoffice/<Modulo>/Create.jsx`
- `resources/js/Pages/Backoffice/<Modulo>/Edit.jsx`
- `resources/js/Layouts/BackofficeLayout.jsx`
- `resources/js/Components/Backoffice/*`
- `resources/js/hooks/backoffice/*` si se necesitan hooks reutilizables

### Datos compartidos Inertia

En `HandleInertiaRequests.php` se compartira de forma namespaced:

- `auth.user`
- `auth.permissions`
- `backoffice.navigation`
- `backoffice.locale`
- `flash.success`
- `flash.error`

## Fase 0. Auditoria, decision tecnica y plan director

**Estado:** COMPLETADA
**Avance de fase:** 100%
**Avance global del plan:** 6%

- [x] ~~Auditar las superficies admin reales del repo antes de proponer la migracion.~~
- [x] ~~Verificar que el backend actual es mixto: Filament + dashboard Inertia + API admin legacy.~~
- [x] ~~Identificar modulos funcionales reales a migrar, sin inventar alcance.~~
- [x] ~~Documentar la restriccion tecnica real de Inertia respecto al Blade root.~~
- [x] ~~Crear este plan director exhaustivo con fases, criterios de salida y limpieza controlada.~~
- [x] ~~Contrastar el plan contra `route:list` real y fijar el inventario exacto de rutas activas del backend actual.~~
- [x] ~~Contrastar el plan contra `User.php`, `BackofficeContentPolicy.php`, recursos Filament y tests existentes para fijar matriz real de roles, baseline de cobertura y condiciones de paridad exacta.~~

## Fase 1. Congelacion funcional y contrato de paridad

**Estado:** COMPLETADA
**Avance de fase:** 100%

Documento de soporte de esta fase:
- `FASE1_BACKOFFICE_CONTRATO_PARIDAD.md`

Objetivo:
definir exactamente que debe igualar el nuevo backend React/Inertia antes de poder apagar Filament/legacy.

- [x] ~~Inventariar cada pantalla y flujo del backoffice actual con captura funcional documentada.~~
- [x] ~~Definir mapa de rutas objetivo definitivo bajo `/backoffice/*`.~~
- [x] ~~Definir contrato de props Inertia por modulo.~~
- [x] ~~Definir contrato visual del layout admin para mantener la paridad con el backend actual.~~
- [x] ~~Definir matriz de permisos por rol para cada modulo.~~
- [x] ~~Definir lista exacta de residuos que se podran borrar al final de cada ola.~~
- [x] ~~Inventariar por modulo:~~
  - [x] ~~columnas de tabla~~
  - [x] ~~filtros~~
  - [x] ~~ordenacion~~
  - [x] ~~busqueda~~
  - [x] ~~paginacion~~
  - [x] ~~acciones de fila~~
  - [x] ~~acciones masivas~~
  - [x] ~~widgets~~
  - [x] ~~relation managers~~
  - [x] ~~validaciones~~
  - [x] ~~redirects y mensajes flash~~
- [x] ~~Capturar baseline visual real del backend actual:~~
  - [x] ~~login~~
  - [x] ~~dashboard~~
  - [x] ~~index de cada recurso~~
  - [x] ~~create/edit de cada recurso~~
- [x] ~~Definir la fuente exacta de paridad para los dominios que hoy conviven entre Filament y dashboard/api legacy.~~

Criterio de salida:
- existe una matriz de paridad por modulo y una lista exacta de rutas viejas/nuevas.
- existe baseline visual y funcional verificable del backend actual.

## Fase 2. Shell base del nuevo backoffice React/Inertia

**Estado:** COMPLETADA
**Avance de fase:** 100%

Documento de soporte de esta fase:
- `FASE2_BACKOFFICE_SHELL_BASE.md`

Objetivo:
crear la base comun del nuevo backend sin tocar todavia los modulos editoriales complejos.

- [x] ~~Crear layout `BackofficeLayout.jsx`.~~
- [x] ~~Crear sistema de navegacion lateral/topbar en React.~~
- [x] ~~Crear sistema de breadcrumbs y titulo de pagina.~~
- [x] ~~Crear sistema de flash messages Inertia.~~
- [x] ~~Crear componentes base reutilizables:~~
  - [x] ~~botones~~
  - [x] ~~inputs~~
  - [x] ~~selects~~
  - [x] ~~textarea~~
  - [x] ~~modales~~
  - [x] ~~tablas~~
  - [x] ~~paginacion~~
  - [x] ~~filtros~~
  - [x] ~~empty states~~
  - [x] ~~loaders~~
- [x] ~~Namespacer props globales del backoffice en `HandleInertiaRequests.php`.~~
- [x] ~~Definir componente de navegacion con visibilidad por rol.~~
- [x] ~~Definir slots/zonas equivalentes a:~~
  - [x] ~~cabecera~~
  - [x] ~~area principal~~
  - [x] ~~acciones de pagina~~
  - [x] ~~widgets/resumen~~
- [x] ~~Garantizar que todo el shell usa solo React + Inertia + Tailwind.~~
- [x] ~~Crear una superficie preview segura para el shell bajo rutas separadas sin pisar Filament.~~
- [x] ~~Validar la fase con tests feature y build del frontend.~~

Criterio de salida:
- el shell admin existe y puede renderizar paginas vacias reales del backoffice.

## Fase 3. Auth y control de acceso del backoffice Inertia

**Estado:** COMPLETADA
**Avance de fase:** 100%

Documento de soporte de esta fase:
- `FASE3_BACKOFFICE_AUTH_Y_ACCESO.md`

Objetivo:
sustituir el acceso UI de Filament por un flujo propio Inertia.

- [x] ~~Diseñar la nueva pagina `/backoffice/login` en React/Inertia.~~
- [x] ~~Reutilizar la logica segura existente de autenticacion si sigue siendo valida.~~
- [x] ~~Separar claramente auth publica y auth backoffice.~~
- [x] ~~Mantener `EnsureSuperAdmin` y permisos, o endurecerlos donde proceda.~~
- [x] ~~Compartir estado de usuario y permisos via Inertia.~~
- [x] ~~Reducir la necesidad de CSP especial para `unsafe-eval` en el login del backoffice mientras siga conviviendo Filament/Livewire.~~
- [x] ~~Validar logout, expiracion de sesion y redirects.~~
- [x] ~~Mantener el comportamiento actual de:~~
  - [x] ~~`/login` publica~~
  - [x] ~~`/backoffice/login` admin~~
  - [x] ~~redirects correctos tras login~~
  - [x] ~~denegacion correcta para usuarios sin rol valido~~

Validacion real en esta fase:
- [x] ~~Controller, request y action dedicados para auth backoffice (`AuthController`, `BackofficeLoginRequest`, `AuthenticateBackofficeUserAction`).~~
- [x] ~~Compatibilidad preservada con nombres de ruta Filament `filament.backoffice.auth.login` y `filament.backoffice.auth.logout`.~~
- [x] ~~Middleware `EnsureBackofficeAccess` endurecido para redirigir invitados a `/backoffice/login` y conservar intended redirect.~~
- [x] ~~CSP del login Inertia endurecida sin `unsafe-eval`, manteniendo compatibilidad del resto del panel Filament.~~
- [x] ~~Regresion validada en Docker: 14 tests OK, 92 assertions OK (`BackofficeInertiaAuthFlowTest`, `BackofficeShellPreviewTest`, `BackofficeLoginExperienceTest`, `CmsBasePackagesTest`).~~

Criterio de salida:
- el login del backoffice ya no depende de Filament/Livewire para renderizar UI.

## Fase 4. Dashboard unificado del backoffice

**Estado:** COMPLETADA
**Avance de fase:** 100%

Documento de soporte de esta fase:
- `FASE4_BACKOFFICE_DASHBOARD_UNIFICADO.md`

Objetivo:
reemplazar el dashboard legado por un dashboard Inertia robusto y alineado con el nuevo shell.

- [x] ~~Migrar `Dashboard/Index.jsx` a `Backoffice/Dashboard/Index.jsx`.~~
- [x] ~~Limpiar el estado y la logica inline excesiva del dashboard actual.~~
- [x] ~~Separar widgets, stats y tablas en componentes.~~
- [x] ~~Eliminar dependencias visuales acopladas al dashboard legacy actual.~~
- [x] ~~Mantener los datos existentes de stats y listados recientes.~~
- [x] ~~Validar permisos y acceso.~~
- [x] ~~Igualar la informacion hoy expuesta por:~~
  - [x] ~~`BackofficeOverview`~~
  - [x] ~~`AccountWidget`~~
  - [x] ~~`DashboardController`~~

Validacion real en esta fase:
- [x] ~~Payload dedicado implementado en `BuildBackofficeDashboardPayloadAction` con metricas, sesion, accesos rapidos y tablas recientes.~~
- [x] ~~Nueva pagina `Backoffice/Dashboard/Index.jsx` construida sobre `BackofficeLayout` y `DataTable`, sin arrastrar el CRUD monolitico de `Dashboard/Index.jsx`.~~
- [x] ~~La raiz `/backoffice` ya resuelve `Backoffice\DashboardController` y conserva el nombre de ruta `filament.backoffice.pages.dashboard`.~~
- [x] ~~`BackofficePanelProvider` deja de registrar el dashboard Filament para evitar conflicto de ruta, manteniendo vivos los recursos Filament bajo `/backoffice/*`.~~
- [x] ~~Regresion validada en Docker: 19 tests OK, 142 assertions OK (`BackofficeUnifiedDashboardTest`, `CmsBasePackagesTest`, `BackofficeShellPreviewTest`, `BackofficeLoginExperienceTest`, `ProjectPremiumFlowTest`).~~
- [x] ~~Build frontend validado en Docker con `npm run build` sin errores bloqueantes.~~

Criterio de salida:
- `/backoffice` carga el dashboard nuevo y `/dashboard` queda marcado para retirada.

## Fase 5. Infraestructura CRUD reutilizable del backoffice

**Estado:** COMPLETADA
**Avance de fase:** 100%

Documento de soporte de esta fase:
- `FASE5_BACKOFFICE_CRUD_REUTILIZABLE.md`

Objetivo:
crear patrones estables para no rehacer cada modulo desde cero.

- [x] ~~Definir patron `Index / Create / Edit / Form`.~~
- [x] ~~Definir patron de filtros, busqueda y ordenacion.~~
- [x] ~~Definir patron de tablas con acciones fila/masivas.~~
- [x] ~~Definir patron de validacion servidor con FormRequests.~~
- [x] ~~Definir patron de errores de formulario con Inertia forms.~~
- [x] ~~Definir patron de uploads y preview.~~
- [x] ~~Definir patron para relaciones y traducciones.~~
- [x] ~~Definir patron para acciones peligrosas con confirmacion.~~
- [x] ~~Definir patron para acciones especiales de negocio:~~
  - [x] ~~queue newsletter campaign~~
  - [x] ~~relation managers de traducciones~~
  - [x] ~~bloques hijos de pagina~~
  - [x] ~~formularios JSON estructurados~~
- [x] ~~Definir patron de pruebas por modulo:~~
  - [x] ~~feature~~
  - [x] ~~autorizacion~~
  - [x] ~~smoke visual~~
  - [x] ~~regresion de payload~~

Validacion real en esta fase:
- [x] ~~Factorizacion del blueprint CRUD reusable en `CrudModuleBlueprintFactory` para normalizar columnas, filtros, campos, relation managers, acciones especiales y checklist de pruebas.~~
- [x] ~~Payload reusable de index/form implementado en `BuildBackofficeCrudModulePayloadAction` con querystring, paginacion, row actions, bulk actions, validation summary y paneles auxiliares.~~
- [x] ~~FormRequests reales de preview implementados: `BackofficePreviewIndexRequest`, `BackofficePreviewDraftRequest` y `BackofficePreviewActionRequest`.~~
- [x] ~~Rutas POST de preview para `draft` y `actions` activas en `/backoffice-preview/*` sin tocar la persistencia productiva.~~
- [x] ~~Componentes React reutilizables creados: `CrudIndexScreen`, `CrudFormScreen`, `CrudFieldRenderer` y `CrudActionCard`.~~
- [x] ~~Componentes UI base endurecidos (`FilterBar`, `DataTable`, `Pagination`, `Input`, `Select`, `Textarea`) para soportar el patron CRUD nuevo.~~
- [x] ~~Regresion validada en Docker: 19 tests OK, 181 assertions OK (`BackofficeCrudInfrastructureTest`, `BackofficeShellPreviewTest`, `BackofficeUnifiedDashboardTest`, `BackofficeLoginExperienceTest`, `CmsBasePackagesTest`).~~
- [x] ~~Build frontend validado en Docker con `npm run build` sin errores bloqueantes; persisten warnings conocidos de assets runtime/chunk size.~~

Criterio de salida:
- existe una base reutilizable premium para migrar modulos a velocidad controlada.

## Fase 6. Ola 1 de modulos editoriales nucleares

**Estado:** COMPLETADA
**Avance de fase:** 100%

Documento de soporte de esta fase:
- `FASE6_BACKOFFICE_MODULOS_NUCLEARES.md`

Objetivo:
migrar primero los dominios que gobiernan el contenido base.

- [x] ~~Events~~
- [x] ~~Pages~~
- [x] ~~PageTranslations~~
- [x] ~~PageBlocks~~
- [x] ~~Settings~~
- [x] ~~SeoMeta~~

Para cada modulo:
- [x] ~~index~~
- [x] ~~create~~
- [x] ~~edit~~
- [x] ~~validacion~~
- [x] ~~permisos~~
- [x] ~~smoke test de lectura/escritura~~
- [x] ~~test de regresion minima~~
- [x] ~~relation managers o composicion equivalente cuando aplique~~

Validacion real en esta fase:
- [x] ~~Catalogo real de modulos nucleares en `Phase6ModuleCatalog` para `events`, `pages`, `page-blocks`, `settings` y `seo-metas`, manteniendo la coexistencia segura con la preview reusable para el resto del backoffice.~~
- [x] ~~Payloads reales de index/form implementados en `BuildBackofficePhase6CrudPayloadAction`, conectando querystring, filtros, ordenacion, paginacion, defaults y filas persistidas de BD.~~
- [x] ~~Persistencia real implementada en `SaveBackofficePhase6ModuleAction` para `Events`, `Pages`, `PageBlocks`, `Settings` y `SeoMeta`.~~
- [x] ~~`PreviewController` ampliado para mutar desde preview estructural a CRUD real en los modulos de Fase 6 sin abrir una superficie paralela fuera de `/backoffice-preview/*`.~~
- [x] ~~FormRequests Laravel reales para Fase 6 y sus traducciones: `BackofficePreviewDraftRequest` endurecido por modulo, `BackofficePageTranslationUpsertRequest`, `BackofficePageBlockTranslationUpsertRequest` y `BackofficeSettingTranslationUpsertRequest`.~~
- [x] ~~Equivalentes a relation managers implementados en React/Inertia para: traducciones de `Pages`, composicion `Pages -> PageBlocks`, traducciones de `PageBlocks` y traducciones de `Settings`.~~
- [x] ~~Nueva capa de rutas y controlador anidado para traducciones (`Phase6TranslationController`) con `updateOrCreate` alineado a los flujos legacy/Filament.~~
- [x] ~~Componentes React reutilizables endurecidos (`CrudFormScreen`, `CrudFieldRenderer`) para soportar guardado real, campos bloqueados por contexto y paneles de relaciones reales.~~
- [x] ~~Regresion validada en Docker: 18 tests OK, 254 assertions OK (`BackofficePhase6CrudPersistenceTest`, `BackofficeCrudInfrastructureTest`, `BackofficeShellPreviewTest`, `BackofficeUnifiedDashboardTest`).~~
- [x] ~~Build frontend validado en Docker con `npm run build` sin errores bloqueantes; persisten warnings conocidos de assets runtime/chunk size.~~

Criterio de salida:
- el contenido estructural del sitio se administra en React/Inertia.

## Fase 7. Ola 2 de modulos de contenido enriquecido

**Estado:** COMPLETADA
**Avance de fase:** 100%

Documento de soporte de esta fase:
- `FASE7_BACKOFFICE_CONTENIDO_ENRIQUECIDO.md`

Objetivo:
migrar dominios con media, enlaces y assets.

- [x] ~~MusicTracks~~
- [x] ~~MediaAssets~~
- [x] ~~Partners~~
- [x] ~~SocialLinks~~
- [x] ~~DownloadableFiles~~

Para cada modulo:
- [x] ~~index~~
- [x] ~~create~~
- [x] ~~edit~~
- [x] ~~uploads~~
- [x] ~~preview/metadata~~
- [x] ~~permisos~~
- [x] ~~test de regresion minima~~
- [x] ~~traducciones o relaciones equivalentes cuando aplique~~

Validacion real en esta fase:
- [x] ~~Catalogo real ampliado en `Phase6ModuleCatalog` para `music-tracks`, `media-assets`, `partners`, `social-links` y `downloadable-files`, manteniendo una sola superficie React/Inertia coexistente.~~
- [x] ~~Payloads reales de index/form ampliados en `BuildBackofficePhase6CrudPayloadAction` con filtros, ordenacion, metadata, labels computadas y soporte de relation manager equivalente para `MusicTracks`.~~
- [x] ~~Persistencia real ampliada en `SaveBackofficePhase6ModuleAction` para los cinco modulos de Fase 7 y traducciones de `MusicTracks`.~~
- [x] ~~Validacion Laravel endurecida en `BackofficePreviewDraftRequest` para contratos reales de media, sponsors, redes sociales y archivos descargables, incluyendo adjuntos polimorficos validos.~~
- [x] ~~Nuevo request dedicado `BackofficeMusicTrackTranslationUpsertRequest` y rutas/controlador anidados para el equivalente al relation manager de traducciones de tracks musicales.~~
- [x] ~~Contrato de media y archivos respetado sin inventar un upload binario nuevo: la UI administra `disk`, `path`, `filename`, `file_path`, `external_url`, `metadata` y `settings` igual que la superficie actual.~~
- [x] ~~Preview shell actualizada para reflejar Fase 7 como fase activa dentro del backoffice coexistente.~~
- [x] ~~Regresion validada en Docker: 24 tests OK, 348 assertions OK (`BackofficePhase7RichContentPersistenceTest`, `BackofficePhase6CrudPersistenceTest`, `BackofficeCrudInfrastructureTest`, `BackofficeShellPreviewTest`, `BackofficeUnifiedDashboardTest`).~~
- [x] ~~Build frontend validado en Docker con `npm run build` sin errores bloqueantes; persisten warnings conocidos de assets runtime/chunk size.~~

Criterio de salida:
- media y activos editoriales quedan cubiertos en la nueva UI.

## Fase 8. Ola 3 de modulos legales, SEO avanzado y newsletters

**Estado:** COMPLETADA
**Avance de fase:** 100%

Documento de soporte de esta fase:
- `FASE8_BACKOFFICE_MODULOS_LEGALES_NEWSLETTERS.md`

Objetivo:
cerrar los dominios restantes y la cobertura funcional completa.

- [x] ~~LegalDocuments~~
- [x] ~~RedirectRules~~
- [x] ~~NewsletterSubscribers~~
- [x] ~~NewsletterCampaigns~~
- [x] ~~NewsletterLogs~~

Para cada modulo:
- [x] ~~index~~
- [x] ~~create~~
- [x] ~~edit~~
- [x] ~~validacion~~
- [x] ~~permisos~~
- [x] ~~acciones de negocio especiales~~
- [x] ~~test de regresion minima~~

Acciones especiales obligatorias de esta fase:
- [x] ~~queue de campañas newsletter~~
- [x] ~~consulta de logs por campaña~~
- [x] ~~traducciones de `LegalDocuments`~~
- [x] ~~traducciones de `Settings`~~

Validacion real en esta fase:
- [x] ~~Catalogo real ampliado en `Phase6ModuleCatalog` para `legal-documents`, `redirect-rules`, `newsletter-subscribers`, `newsletter-campaigns` y `newsletter-logs`, incluyendo soporte `readOnly`, filtros y checklist especifico de Fase 8.~~
- [x] ~~Payloads reales de index/form ampliados en `BuildBackofficePhase6CrudPayloadAction` con consultas persistidas, relation manager equivalente para traducciones legales, acceso de campanas a logs asociados y CTA explicito `Ver logs`.~~
- [x] ~~Persistencia real ampliada en `SaveBackofficePhase6ModuleAction` para `LegalDocuments`, `RedirectRules`, `NewsletterSubscribers` y `NewsletterCampaigns`, reutilizando `PrepareNewsletterSubscriberData` para fechas derivadas de alta/baja.~~
- [x] ~~Validacion Laravel endurecida en `BackofficePreviewDraftRequest` y nuevo request dedicado `BackofficeLegalDocumentTranslationUpsertRequest` para el equivalente al relation manager de traducciones legales.~~
- [x] ~~`PreviewController` y `BackofficePreviewActionRequest` ampliados para ejecutar la accion real `queue-campaign`, bloquear mutaciones en `newsletter-logs` y permitir consulta read-only con permisos de visualizacion.~~
- [x] ~~Nuevas relaciones Eloquent y policy dedicadas para logs newsletter: `NewsletterCampaign::logs()`, `NewsletterSubscriber::logs()`, `NewsletterLog::campaign()`, `NewsletterLog::subscriber()` y `NewsletterLogPolicy`.~~
- [x] ~~Rutas y controlador anidados para traducciones de `LegalDocuments` cerrados en `routes/web.php` y `Phase6TranslationController`.~~
- [x] ~~Nueva regresion de Fase 8 validada en Docker: 18 tests OK, 335 assertions OK (`BackofficePhase8OperationalModulesTest`, `BackofficePhase7RichContentPersistenceTest`, `BackofficePhase6CrudPersistenceTest`).~~
- [x] ~~Build frontend validado en Docker con `npm run build`; persisten warnings conocidos de assets runtime/chunk size, sin error bloqueante.~~
- [x] ~~Rollout adicional del patron multilingue integrado completado para `settings` y `music-tracks`: `BackofficePreviewDraftRequest`, `SaveBackofficePhase6ModuleAction` y `PreviewController` ya validan, guardan y redirigen con `?locale=` desde el formulario principal, sin separar el flujo editorial por idioma.~~
- [x] ~~Superficie page-centric de `pages` afinada para edicion inline en el mismo contenedor: `BuildBackofficePhase6CrudPayloadAction`, `CrudFormScreen`, `PreviewController` y `Phase6TranslationController` ya soportan tabs de bloque, alta/baja de slides y retorno con `focus` al tab activo sin reabrir una pantalla separada por item.~~
- [x] ~~Limpieza global de formularios del backoffice: `CrudFormScreen`, `BuildBackofficePhase6CrudPayloadAction` y `BuildBackofficeCrudModulePayloadAction` ya no muestran el panel ni la tarjeta resumen `Modo EDIT`.~~
- [x] ~~Ajuste visual global de campos del backoffice: `Input`, `Textarea`, `Select`, `CrudFieldRenderer`, `DataTable`, `ImageField`, `RichTextField` y `app.css` ya fuerzan contraste correcto de iconos/controles sobre la UI oscura.~~
- [x] ~~Refactor visual global de botones del backoffice: `Button`, CTA del login, tabs inline y toggle de filtros ya usan un unico acento sin gradients, con reposo transparente y hover/focus/active relleno para distinguir claramente ambos estados.~~
- [x] ~~Refuerzo visual del menu de idiomas del backoffice: `CrudFormScreen` y `SeoMetaIndexScreen` ya remarcan el locale activo y aplican fallback a `ES` cuando no exista un activo explicito en el payload.~~
- [x] ~~Correccion del selector de idiomas en el primer contenedor page-centric: `BackofficeLayout` ya soporta acciones activas y `BuildBackofficePhase6CrudPayloadAction` marca el locale activo en `editorialPageLocaleActions()`.~~

Criterio de salida:
- el backend React/Inertia cubre el 100% del alcance funcional actual del admin.

## Fase 9. Corte de rutas y convivencia controlada

**Estado:** COMPLETADA
**Avance de fase:** 100%

Documento de soporte de esta fase:
- `FASE9_BACKOFFICE_CORTE_RUTAS_Y_CONVIVENCIA.md`

Objetivo:
mover la entrada oficial del backoffice al nuevo stack sin apagar aun el legado de forma ciega.

- [x] ~~Apuntar `/backoffice/login` al nuevo login Inertia.~~
- [x] ~~Apuntar `/backoffice/*` a controladores Inertia del nuevo backend.~~
- [x] ~~Mantener feature flag o rollback claro durante el corte.~~
- [x] ~~Validar rutas equivalentes una por una.~~
- [x] ~~Marcar `/dashboard` como legacy en retirada.~~
- [x] ~~Marcar `/dashboard/api/*` como legacy en retirada.~~
- [x] ~~Marcar `/admin/*` como legacy en retirada.~~
- [x] ~~Validar que no se rompe ningun consumidor interno mientras convivan rutas viejas y nuevas.~~

Validacion real en esta fase:
- [x] ~~Nuevo contrato de rutas centralizado en `config/backoffice.php` y `App\\Support\\Backoffice\\BackofficePath`, separando prefijo oficial, alias temporal de compatibilidad y ruta del panel legacy.~~
- [x] ~~El panel Filament legado deja de ocupar `/backoffice/*` y pasa a `'/backoffice-legacy/*'` mediante `BackofficePanelProvider`, liberando la superficie oficial para Inertia sin borrar todavia Filament/Livewire.~~
- [x] ~~`routes/web.php` registra ya `/backoffice/*` como superficie oficial de CRUD React/Inertia y mantiene `/backoffice-preview/*` como alias temporal de compatibilidad controlada.~~
- [x] ~~Los payloads reales de CRUD, traducciones, relation managers y acciones especiales se regeneran con el prefijo activo correcto (`/backoffice/*` o alias temporal) sin romper redirects ni formularios durante la convivencia.~~
- [x] ~~Dashboard y navegacion del backoffice marcan ya `Backoffice legacy`, `/dashboard` y `/dashboard/api/*` como superficies legacy en retirada.~~
- [x] ~~Nueva regresion de Fase 9 validada en Docker: `BackofficePhase9RouteCutoverTest` en verde con 7 tests sobre acceso oficial, intended redirect, alias de compatibilidad y legacy panel desplazado.~~
- [x] ~~Regresion conjunta Fases 6-9 validada en Docker: 39 tests OK, 564 assertions OK (`BackofficePhase9RouteCutoverTest`, `BackofficeCrudInfrastructureTest`, `BackofficePhase6CrudPersistenceTest`, `BackofficePhase7RichContentPersistenceTest`, `BackofficePhase8OperationalModulesTest`, `BackofficeUnifiedDashboardTest`, `BackofficeShellPreviewTest`, `BackofficeInertiaAuthFlowTest`).~~
- [x] ~~Build frontend validado en Docker tras el cutover con `npm run build`; persisten warnings conocidos de assets runtime/chunk size, sin error bloqueante.~~

Criterio de salida:
- el acceso principal del backoffice ya es React/Inertia.

## Fase 10. Limpieza residual segura

**Estado:** COMPLETADA
**Avance de fase:** 100%

Documento de soporte de esta fase:
- `FASE10_BACKOFFICE_LIMPIEZA_RESIDUAL_SEGURA.md`

Objetivo:
eliminar residuos solo cuando el corte este validado.

### Bloque A. Retirada de superficies viejas

- [x] ~~Retirar ruta `/dashboard` antigua.~~
- [x] ~~Retirar `/dashboard/api/*`.~~
- [x] ~~Retirar `/admin/*`.~~

### Bloque B. Retirada de Filament/Livewire UI

- [x] ~~Retirar `app/Providers/Filament/BackofficePanelProvider.php`.~~
- [x] ~~Retirar `app/Filament/**/*`.~~
- [x] ~~Retirar `resources/css/filament/**/*`.~~
- [x] ~~Retirar `resources/views/filament/**/*`.~~
- [x] ~~Retirar acciones/helpers exclusivamente creados para el login Filament.~~
- [x] ~~Retirar tests exclusivamente de Filament si dejan de aplicar.~~
- [x] ~~Retirar `BuildBackofficeLoginFooterPayloadAction.php` cuando el nuevo login Inertia ya tenga su equivalente real.~~

### Bloque C. Retirada de dependencias y artefactos

- [x] ~~Eliminar dependencias Composer no usadas:~~
  - [x] ~~`filament/filament`~~
  - [x] ~~`livewire/livewire`~~
- [x] ~~Limpiar assets publicados de Filament en `public/`.~~
- [x] ~~Limpiar ramas de CSP especiales ya no necesarias para Filament.~~
- [x] ~~Limpiar referencias residuales en `vite.config.js`, `composer.json` y tests.~~
- [x] ~~Verificar con `rg` que no quedan referencias a:~~
  - [x] ~~`App\\Filament\\`~~
  - [x] ~~`Filament\\`~~
  - [x] ~~`Livewire\\`~~
  - [x] ~~`resources/css/filament`~~

### Regla de seguridad de esta fase

- [x] ~~No borrar nada sin haber validado primero el equivalente React/Inertia en produccion local Docker.~~

Validacion real en esta fase:
- [x] ~~Retirada fisica del runtime legacy completada: `app/Providers/Filament/BackofficePanelProvider.php`, `app/Filament/**/*`, `resources/css/filament/**/*`, `resources/views/filament/**/*`, assets publicados Filament en `public/**/*` y superficies `DashboardController`, `resources/js/Pages/Dashboard/Index.jsx` y `app/Http/Controllers/Admin/*`.~~
- [x] ~~Contrato final de rutas simplificado en `config/backoffice.php`, `App\\Support\\Backoffice\\BackofficePath`, `routes/web.php`, shared props y auth para dejar solo `/backoffice/*` como superficie activa.~~
- [x] ~~Dependencias Composer y lock sincronizados en Docker, retirando `filament/filament`, `livewire/livewire` y su arbol transitorio ya no usado.~~
- [x] ~~Suite legacy especifica de Filament retirada (`FilamentEditorialResourcesTest`, `Phase6CmsResourcesTest`) y regresion React/Inertia adaptada a la ruta oficial `/backoffice/*`, incluyendo el cierre de `/backoffice-preview/*` y la ausencia de enlaces legacy en dashboard.~~
- [x] ~~Regresion completa validada en Docker: `php artisan test tests/Feature` -> 77 tests OK, 766 assertions OK.~~
- [x] ~~Build frontend validado en Docker con `npm run build`; persisten warnings conocidos de assets runtime/chunk size, sin error bloqueante.~~
- [x] ~~Incidencia residual post-retirada resuelta: `php artisan view:clear` para invalidar vistas Blade compiladas con hooks de Livewire antes de repetir la regresion completa.~~

Criterio de salida:
- el repo queda sin superficies admin antiguas conflictivas.

## Fase 11. QA premium, seguridad y Definition of Done

**Estado:** COMPLETADA
**Avance de fase:** 100%

Documento de soporte de esta fase:
- `FASE11_BACKOFFICE_QA_SEGURIDAD_DOD.md`

Objetivo:
cerrar la migracion con validacion premium.

- [x] ~~Tests auth backoffice.~~
- [x] ~~Tests permisos por rol.~~
- [x] ~~Tests CRUD por modulo.~~
- [x] ~~Tests de traducciones.~~
- [x] ~~Tests de uploads y media.~~
- [x] ~~Tests de newsletters.~~
- [x] ~~Tests de navegacion Inertia.~~
- [x] ~~Smoke test navegador del login.~~
- [x] ~~Smoke test navegador del dashboard.~~
- [x] ~~Smoke test navegador de al menos un CRUD por cada ola.~~
- [x] ~~Comparativa visual contra el baseline del backend actual.~~
- [x] ~~Verificar que el frontend publico no ha cambiado.~~
- [x] ~~Verificar que ya no existe dependencia runtime de Livewire.~~
- [x] ~~Verificar que CSP del backoffice ya no requiere `unsafe-eval`.~~
- [x] ~~Verificar que la suite nueva iguala o supera a:~~
  - [x] ~~`BackofficeLoginExperienceTest`~~
  - [x] ~~`FilamentEditorialResourcesTest`~~
  - [x] ~~`Phase6CmsResourcesTest`~~
  - [x] ~~`ProjectPremiumFlowTest`~~

Validacion real de la fase:
- `docker compose exec app php artisan test tests/Feature/BackofficePhase11QualityGateTest.php tests/Feature/BackofficeLoginExperienceTest.php tests/Feature/BackofficeInertiaAuthFlowTest.php tests/Feature/BackofficeCrudInfrastructureTest.php tests/Feature/BackofficePhase6CrudPersistenceTest.php tests/Feature/BackofficePhase7RichContentPersistenceTest.php tests/Feature/BackofficePhase8OperationalModulesTest.php tests/Feature/BackofficePhase9RouteCutoverTest.php tests/Feature/BackofficeShellPreviewTest.php tests/Feature/BackofficeUnifiedDashboardTest.php tests/Feature/ProjectPremiumFlowTest.php tests/Feature/Phase7PublicCmsPayloadTest.php`
  - resultado: `54 tests OK`
  - assertions: `670`
- `docker compose exec app composer audit`
  - resultado: `No security vulnerability advisories found.`
- smoke navegador validado en `/backoffice/login`, `/backoffice`, `/backoffice/events`, `/backoffice/music-tracks`, `/backoffice/legal-documents` y `/en`

Criterio de salida:
- backend React/Inertia/Tailwind estable, seguro, limpio y sin residuos conflictivos.

## Checklist de definicion de hecho final

- [x] ~~El backoffice ya no renderiza UI con Filament/Livewire.~~
- [x] ~~El acceso principal es `/backoffice/*` en Inertia.~~
- [x] ~~Todos los modulos actuales del admin tienen equivalente React/Inertia.~~
- [x] ~~El layout admin esta componentizado.~~
- [x] ~~Los formularios estan normalizados y validados con Laravel.~~
- [x] ~~La navegacion y permisos son coherentes.~~
- [x] ~~El frontend publico sigue intacto.~~
- [x] ~~Filament/Livewire han sido retirados del runtime admin.~~
- [x] ~~Los archivos residuales conflictivos han sido eliminados de forma controlada.~~

## Orden de ejecucion recomendado

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
11. Fase 10
12. Fase 11

## Progreso actual

- **Fase actual trabajada en esta iteracion:** `Fase 11`
- **Avance de la fase actual:** `100%`
- **Avance global estimado del plan:** `100%`
- **Siguiente fase recomendada:** `Plan backend completado`

## Incidencia post-plan: rendimiento del backoffice

- **Estado:** `Mitigacion validada y cerrada`
- **Avance de la incidencia:** `100%`
- **Naturaleza:** `Auditoria post-cierre del plan; no reabre fases ya completadas`
- ~~Auditoria runtime del backoffice/login completada con instrumentacion minima en kernel, middleware global, Inertia share y payloads.~~
- ~~Payloads principales descartados como causa raiz principal: login ~24.78 ms, `share()` ~7.06 ms tras la optimizacion y CRUD/dashboard previamente muy por debajo de la latencia percibida.~~
- ~~Causa raiz principal acotada al runtime local Docker/PHP: bootstrap Laravel costoso sobre bind mount host, especialmente en el arbol `vendor`, con mejora adicional al calentar caches oficiales de Laravel.~~
- ~~Mitigacion aplicada en `docker-compose.yml`: volumen Docker nativo para `/var/www/html/vendor` y `composer install` automatico cuando falta `vendor/autoload.php` o cambia `composer.lock`.~~
- ~~Verificacion inicial completada: `GET /backoffice/login` baja aprox. de `8559.48 ms` a `2902.9 ms` observado desde cliente; `public/index.php` baja aprox. de `6357-7282 ms` a `370-752 ms` segun corrida.~~
- ~~Validacion final completada con confirmacion manual de mejora sustancial en navegador real tras el ajuste de runtime Docker/PHP.~~

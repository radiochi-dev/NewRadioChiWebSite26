# FASE 2. SHELL BASE DEL NUEVO BACKOFFICE REACT + INERTIA

## Objetivo cerrado en esta fase

Construir la base comun del nuevo backoffice sin tocar aun:

- las rutas reales de Filament en `/backoffice/*`,
- el frontend publico,
- ni los modulos editoriales definitivos.

La salida de esta fase es un **shell preview protegido** y reusable sobre el que se apoyaran las fases 3-8.

## Implementacion realizada

## 1. Superficie preview segura

Se creo una superficie separada para el nuevo shell:

- `GET /backoffice-preview`
- `GET /backoffice-preview/{module}`
- `GET /backoffice-preview/{module}/create`
- `GET /backoffice-preview/{module}/{record}/edit`

Garantias:

- no pisa `/backoffice/*` de Filament,
- no rompe `/dashboard`,
- no rompe `/dashboard/api/*`,
- no rompe `/admin/*`.

## 2. Control de acceso real

Se creo middleware dedicado:

- `app/Http/Middleware/EnsureBackofficeAccess.php`

Y alias:

- `backoffice.access`

Comportamiento:

- cualquier usuario con acceso real al backoffice puede entrar en la preview,
- `readonly` puede ver shell e indices,
- `readonly` no puede abrir create/edit preview,
- `super_admin` conserva visibilidad adicional de enlaces legacy.

## 3. Shared props Inertia namespaced

Se ampliaron los shared props de `HandleInertiaRequests.php` con:

### `auth`

- `auth.user`
- `auth.roles`
- `auth.capabilities.hasBackofficeAccess`
- `auth.capabilities.canViewBackofficeContent`
- `auth.capabilities.canManageBackofficeContent`
- `auth.capabilities.isSuperAdmin`

### `backoffice`

- `backoffice.branding.name`
- `backoffice.branding.logo`
- `backoffice.branding.previewPrefix`
- `backoffice.locale`
- `backoffice.navigation`

### `flash`

- `flash.success`
- `flash.error`

## 4. Acciones y soporte del shell

Se crearon:

- `app/Support/Backoffice/PreviewModuleRegistry.php`
- `app/Actions/Backoffice/BuildBackofficeNavigationAction.php`
- `app/Actions/Backoffice/BuildBackofficePreviewPageAction.php`
- `app/Http/Controllers/Backoffice/PreviewController.php`

## 5. Layout y slots ya operativos

Se creo:

- `resources/js/Layouts/BackofficeLayout.jsx`

Slots/zonas resueltas:

- cabecera
- breadcrumbs
- acciones de pagina
- resumen/widgets
- area principal
- sidebar responsive
- topbar
- flash messages

## 6. Sistema de navegacion

Se creo navegacion lateral con visibilidad por rol:

- grupo `General`
- grupos por dominio editorial congelado
- grupo extra `Super Admin` solo visible para super admin

Ficheros principales:

- `resources/js/Components/Backoffice/BackofficeSidebar.jsx`
- `resources/js/Components/Backoffice/BackofficeTopbar.jsx`
- `resources/js/Components/Backoffice/BackofficeBreadcrumbs.jsx`
- `resources/js/Components/Backoffice/BackofficeFlashMessages.jsx`

## 7. Componentes base reutilizables creados

Se creo el kit base del futuro backoffice:

- `Button.jsx`
- `Input.jsx`
- `Select.jsx`
- `Textarea.jsx`
- `Modal.jsx`
- `DataTable.jsx`
- `Pagination.jsx`
- `FilterBar.jsx`
- `EmptyState.jsx`
- `Loader.jsx`

Ruta:

- `resources/js/Components/Backoffice/ui/*`

## 8. Paginas preview creadas

Se crearon paginas Inertia reales para validar el shell:

- `resources/js/Pages/Backoffice/Preview/Dashboard.jsx`
- `resources/js/Pages/Backoffice/Preview/ModuleIndex.jsx`
- `resources/js/Pages/Backoffice/Preview/ModuleForm.jsx`

Estas paginas validan:

- layout global,
- indices vacios,
- formularios placeholder,
- acciones visibles/ocultas por rol,
- tabla, filtros, paginacion, modal y loader.

## 9. Cobertura y validacion ejecutada

Tests nuevos:

- `tests/Feature/BackofficeShellPreviewTest.php`

Cobertura verificada:

- guest redirigido
- editor accede a la preview
- shared props namespaced correctas
- readonly ve indice pero no create preview

Regresion adicional pasada:

- `BackofficeLoginExperienceTest`

Build:

- `npm run build` OK

Observacion:

- Vite sigue mostrando warnings ya existentes sobre assets de imagen no resueltos en build time, pero la compilacion completa correctamente y no bloquea esta fase.

## 10. Criterio de salida cumplido

La fase se considera cumplida porque:

- existe shell admin reusable,
- existe navegacion protegida,
- existen componentes base reutilizables,
- existen shared props namespaced,
- existen paginas vacias reales del backoffice,
- y todo convive sin romper Filament ni el frontend publico.

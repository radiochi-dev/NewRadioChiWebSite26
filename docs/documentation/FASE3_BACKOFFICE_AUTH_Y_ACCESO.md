# FASE 3. AUTH Y CONTROL DE ACCESO DEL BACKOFFICE INERTIA

## Estado

- Fase: COMPLETADA
- Ambito: autenticacion, login/logout, redirects, CSP y control de acceso del shell preview del nuevo backoffice Inertia

## Objetivo cerrado

Sustituir la UI de acceso del backoffice basada en Filament/Livewire por una pantalla propia en `React + Inertia + Tailwind`, manteniendo compatibilidad con el ecosistema actual del panel y sin tocar el frontend publico.

## Implementacion realizada

### 1. Login propio del backoffice en Inertia

Se implemento una pantalla dedicada:

- `resources/js/Pages/Backoffice/Auth/Login.jsx`

Componentes de soporte:

- `resources/js/Components/Backoffice/BackofficeLoginFooter.jsx`
- `resources/js/Layouts/BackofficeLayout.jsx` ya preparado en Fase 2

Datos reales reutilizados:

- `app/Actions/Backoffice/BuildBackofficeLoginFooterPayloadAction.php`

### 2. Separacion de auth publica y auth backoffice

Se implementaron piezas dedicadas para el backoffice:

- `app/Http/Controllers/Backoffice/AuthController.php`
- `app/Http/Requests/Backoffice/BackofficeLoginRequest.php`
- `app/Actions/Backoffice/AuthenticateBackofficeUserAction.php`

Resultado:

- `/login` publica sigue separada
- `/backoffice/login` queda bajo flujo propio del backoffice
- el frontend publico no se altera

### 3. Rutas y compatibilidad con Filament

Se ajustaron las rutas en `routes/web.php` para mantener compatibilidad con nombres de ruta esperados por Filament:

- `filament.backoffice.auth.login`
- `filament.backoffice.auth.logout`

Y al mismo tiempo exponer el flujo propio:

- `GET /backoffice/login`
- `POST /backoffice/login`
- `POST /backoffice/logout`

### 4. Guard de acceso y redirects

Se endurecio:

- `app/Http/Middleware/EnsureBackofficeAccess.php`

Comportamiento validado:

- guest en `backoffice-preview/*` redirige a `/backoffice/login`
- usuario sin acceso al backoffice recibe denegacion correcta
- super admin configurado y editor pueden entrar por el flujo nuevo
- se preserva el `intended redirect` hacia `/backoffice-preview`

### 5. Shared props y contrato del shell

Se mantuvieron y aprovecharon los shared props namespaced del backoffice desde:

- `app/Http/Middleware/HandleInertiaRequests.php`

Contrato validado:

- `auth.user`
- `auth.roles`
- `auth.capabilities`
- `backoffice.branding`
- `backoffice.navigation`
- `flash.success`
- `flash.error`

### 6. CSP del login Inertia

Se ajusto:

- `app/Http/Middleware/SecurityHeaders.php`

Resultado:

- `/backoffice/login` ya no necesita `unsafe-eval`
- el resto de superficies Filament/Livewire del panel siguen manteniendo la excepcion necesaria mientras exista convivencia

## Archivos principales tocados en la fase

- `app/Actions/Backoffice/AuthenticateBackofficeUserAction.php`
- `app/Http/Controllers/Backoffice/AuthController.php`
- `app/Http/Middleware/EnsureBackofficeAccess.php`
- `app/Http/Middleware/HandleInertiaRequests.php`
- `app/Http/Middleware/SecurityHeaders.php`
- `app/Http/Requests/Backoffice/BackofficeLoginRequest.php`
- `resources/js/Components/Backoffice/BackofficeLoginFooter.jsx`
- `resources/js/Pages/Backoffice/Auth/Login.jsx`
- `routes/web.php`
- `tests/Feature/BackofficeInertiaAuthFlowTest.php`
- `tests/Feature/BackofficeLoginExperienceTest.php`
- `tests/Feature/BackofficeShellPreviewTest.php`

## Validacion ejecutada

Validacion real en contenedor `app`:

- `php artisan test tests/Feature/BackofficeInertiaAuthFlowTest.php tests/Feature/BackofficeShellPreviewTest.php tests/Feature/BackofficeLoginExperienceTest.php tests/Feature/CmsBasePackagesTest.php`

Resultado:

- 14 tests OK
- 92 assertions OK

Cobertura cerrada por esa regresion:

- login Inertia del backoffice
- redirect de invitados a `/backoffice/login`
- acceso permitido para `editor`
- denegacion para usuarios sin rol valido
- provision del super admin configurado
- logout del flujo nuevo
- CSP endurecida para el login Inertia
- compatibilidad base del panel Filament existente

## Decisiones tecnicas cerradas

1. No se toca `vendor`.
2. No se elimina Filament en esta fase; solo se sustituye la UI de login.
3. Se preservan nombres de ruta esperados por Filament para no romper middleware ni recursos del panel.
4. La retirada completa de `unsafe-eval` del backoffice queda diferida hasta apagar totalmente las pantallas Filament/Livewire.

## Criterio de salida alcanzado

La UI de login del backoffice ya no depende de Filament/Livewire para renderizarse, y el flujo de acceso del nuevo backoffice Inertia queda operativo y cubierto por tests de regresion.

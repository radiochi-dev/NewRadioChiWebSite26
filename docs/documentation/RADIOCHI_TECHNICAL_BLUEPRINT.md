# Blueprint Técnico Exhaustivo
## New RadioChi: Laravel 12 + React + Inertia + Tailwind + Framer Motion

Este documento define, paso por paso, la creación de New RadioChi desde cero en un repositorio independiente, sin afectar el proyecto Astro actual.

> Actualizacion operativa 2026-09-22:
> este blueprint queda como documento historico de diseño.
> El estado real actual del repo ya no coincide con varios estados parciales aqui descritos:
> - la importacion masiva legacy/i18n ya esta implementada mediante `legacy:import-content`,
> - la base de automatizacion `n8n/Ollama` ya esta implementada y validada por tests,
> - el hardening base y QA de cierre del backoffice ya quedaron cerrados en `docs/documentation/FASE11_BACKOFFICE_QA_SEGURIDAD_DOD.md`,
> - los pendientes reales previos a produccion quedan centralizados en `docs/plans/Recordar_antes_Depoly _Produccion.md`.

---

## 1. Objetivo de esta guía

Objetivo principal:
- Migrar RadioChi desde Astro hacia una plataforma Laravel moderna con backend CMS profesional, preparada para SEO, automatización y escalabilidad.

Resultado esperado:
- Frontend visualmente equivalente o superior.
- CMS para contenido dinámico.
- Infraestructura Docker lista para local y VPS.
- Seguridad y rendimiento de nivel producción.

---

## 2. Requisitos funcionales obligatorios

1. Home y secciones actuales migradas a React Inertia.
2. Sistema multidioma con gestión desde CMS.
3. Gestión de eventos y calendario desde panel admin.
4. Gestión de media y metadatos SEO por página.
5. Newsletter masiva por colas.
6. Integración de Instagram mediante jobs y caché.
7. Preparado para integración n8n/Ollama.

Estado auditado actual:
- [x] Home y secciones base migradas a React Inertia (paridad visual final pendiente).
- [ ] Sistema multidioma con gestión completa desde CMS.
- [x] Gestión de eventos y calendario desde backend API admin.
- [x] Gestión de media y metadatos SEO por API admin.
- [x] Newsletter masiva base por colas (MVP backend).
- [x] Integración Instagram base mediante job y caché.
- [ ] Preparación n8n/Ollama completada.

---

## 3. Requisitos no funcionales

1. Disponibilidad objetivo > 99.9%.
2. Arquitectura desacoplada por servicios.
3. Core Web Vitals objetivo móvil:
   - LCP < 2.5s
   - INP < 200ms
   - CLS < 0.1
4. Seguridad OWASP top risks mitigados.
5. Tiempos de despliegue reproducibles.

---

## 4. Estructura de repositorio objetivo

```text
radiochi-platform-laravel/
  app/
  bootstrap/
  config/
  database/
    migrations/
    seeders/
  resources/
    js/
      Pages/
      Components/
      Layouts/
      lib/
    css/
  routes/
  storage/
  docker/
    nginx/
    php/
  docker-compose.yml
  Dockerfile
```

---

## 5. Paso a paso: creación del proyecto

## Paso 1: Inicializar proyecto

Objetivo:
- Crear base Laravel 12 con dependencias frontend.

Comandos:

```bash
composer create-project laravel/laravel radiochi-platform "^12.0"
cd radiochi-platform
composer require inertiajs/inertia-laravel
npm install
npm install react react-dom @inertiajs/react @vitejs/plugin-react framer-motion
npm install -D tailwindcss @tailwindcss/vite
php artisan inertia:middleware
```

Validación:
- `php artisan --version` responde.
- `npm run build` compila.
- Estado: [x] completado

## Paso 2: Configurar Inertia + React

Objetivo:
- Activar renderizado Inertia con React.

Archivo `resources/views/app.blade.php`:

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @viteReactRefresh
    @vite('resources/js/app.jsx')
    @inertiaHead
</head>
<body>
    @inertia
</body>
</html>
```

Archivo `resources/js/app.jsx`:

```jsx
import { createRoot } from 'react-dom/client'
import { createInertiaApp } from '@inertiajs/react'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'

createInertiaApp({
  resolve: (name) => resolvePageComponent(`./Pages/${name}.jsx`, import.meta.glob('./Pages/**/*.jsx')),
  setup({ el, App, props }) {
    const root = createRoot(el)
    root.render(<App {...props} />)
  }
})
```

Validación:
- Ruta inicial responde con una página Inertia simple.
- Estado: [x] completado

## Paso 3: Configurar Tailwind y base UI

Objetivo:
- Disponer de sistema de estilos consistente.

Requisitos:
- Tokens de color y tipografía de marca RadioChi.
- Utilidades para espaciado y breakpoints.

Entregable:
- Design system mínimo con componentes:
  - Button
  - SectionTitle
  - Card
  - LanguageSwitcher
- Estado: [~] parcial (LanguageSwitcher y layout público listos; design system formal pendiente)

## Paso 4: Modelo de datos inicial

Objetivo:
- Sustituir JSON estático por contenido gestionable.

Migración ejemplo `events`:

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->date('event_date')->nullable();
            $table->string('external_url')->nullable();
            $table->json('content')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
```

Tablas mínimas:
- users
- roles
- permissions
- pages
- page_translations
- events
- calendar_events
- media_assets
- seo_meta
- newsletter_subscribers
- newsletter_campaigns
- newsletter_logs
- Estado: [~] parcial (newsletter_* y tablas core listas; roles/permissions/calendar_events pendientes)

## Paso 5: Migración de contenido Astro -> DB

Objetivo:
- Cargar contenido inicial sin pérdida.

Fuente actual:
- [events.json](file:///c:/Users/fernandocardona/Documents/ContentWorkPC26/RadioChi-Website-2025/src/data/events.json)
- [i18n](file:///c:/Users/fernandocardona/Documents/ContentWorkPC26/RadioChi-Website-2025/src/i18n)

Proceso:
1. Crear seeders para eventos.
2. Crear importador para traducciones.
3. Normalizar fechas a UTC.
4. Validar claves de traducción faltantes.
- Estado: [~] parcial (importador de `calendarevents.json` implementado; falta `events.json` + i18n masivo)

## Paso 6: Construcción del CMS

Objetivo:
- Gestionar sitio sin editar código.

Módulos del CMS:
1. Dashboard de estado.
2. Gestor de páginas y bloques.
3. Gestor de eventos.
4. Gestor de traducciones.
5. Gestor de media.
6. Gestor SEO por página.
7. Newsletter.
8. Auditoría de acciones.

Roles:
- super_admin
- editor
- marketing
- readonly

Policies:
- Separación estricta de permisos por módulo.
- Estado: [~] parcial (CRUD API admin funcional; faltan panel visual, roles/permisos y auditoría)

## Paso 7: Newsletter robusta con colas

Objetivo:
- Envío masivo sin bloquear web ni worker principal.

Diseño:
- Cola dedicada `newsletter`.
- Worker dedicado.
- Batch por bloques.
- Reintentos con backoff.
- Logs por campaña.

Comando ejemplo:

```bash
php artisan queue:work redis --queue=newsletter --tries=3 --backoff=5
```
- Estado: [~] parcial (modelo + campañas + logs + job listos; faltan batches, backoff avanzado y operación dedicada)

## Paso 8: SEO técnico end-to-end

Objetivo:
- Entregar base SEO premium.

Implementación:
1. Titles y descriptions dinámicos por locale.
2. Canonicals y hreflang.
3. JSON-LD en home, eventos y páginas clave.
4. Sitemap index + sitemaps por idioma.
5. Robots por entorno.
6. Gestión de redirecciones 301 desde CMS.
- Estado: [~] parcial-alto (home con canonical/hreflang/OG/Twitter/JSON-LD + sitemap/robots; faltan redirecciones CMS y cobertura total por tipo de contenido)

## Paso 9: Seguridad de aplicación

Objetivo:
- Resistir vectores de ataque comunes.

Controles:
1. 2FA para admins.
2. Rate limiting login y endpoints sensibles.
3. CSP y cabeceras seguras.
4. Protección CSRF.
5. Sanitización de entradas CMS.
6. Logs de auditoría.
7. Rotación de credenciales.
8. Revisión de dependencias.
- Estado: [~] parcial (security headers + rate limit admin activos; faltan 2FA, CSP estricta, auditoría y controles avanzados)

## Paso 10: Docker local para RadioChi

Objetivo:
- Entorno local reproducible.

`docker-compose.yml` mínimo:

```yaml
services:
  radiochi_web:
    image: nginx:1.27-alpine
    ports:
      - "8080:80"
    depends_on:
      - radiochi_app
    volumes:
      - ./:/var/www/html
  radiochi_app:
    build:
      context: .
    depends_on:
      - radiochi_db
      - radiochi_redis
    volumes:
      - ./:/var/www/html
  radiochi_db:
    image: postgres:16-alpine
    environment:
      POSTGRES_DB: radiochi
      POSTGRES_USER: radiochi
      POSTGRES_PASSWORD: radiochi_secure_pass
    volumes:
      - radiochi_db_data:/var/lib/postgresql/data
  radiochi_redis:
    image: redis:7-alpine

volumes:
  radiochi_db_data:
```

Flujo de arranque local:

```bash
docker compose up -d --build
docker compose exec radiochi_app composer install
docker compose exec radiochi_app php artisan key:generate
docker compose exec radiochi_app php artisan migrate --seed
docker compose exec radiochi_app npm install
docker compose exec radiochi_app npm run build
```
- Estado: [x] completado en stack local base

---

## 6. Paso a paso de despliegue de RadioChi en VPS

1. Preparar variables de entorno de producción.
2. Generar build frontend.
3. Ejecutar migraciones.
4. Cachear config, routes y views.
5. Reiniciar workers.
6. Verificar health endpoints.
7. Verificar SEO y respuestas HTTP.

Comandos de optimización:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan optimize
```

---

## 7. Plan de validación de calidad

QA funcional:
- Navegación completa por idioma.
- Formularios y newsletter.
- CMS y permisos por rol.

QA técnico:
- Tests de integración.
- Build sin errores.
- Validación de migraciones.

QA seguridad:
- Hardening checklist.
- Pruebas de rate limit.
- Validación de headers.

QA SEO:
- Validación metadata.
- Validación sitemap.
- Validación schema JSON-LD.
- Estado de validación ejecutada en este ciclo:
  - [x] `php artisan test`
  - [x] `npm run build`
  - [x] lint PHP en archivos migrados (`pint --test` segmentado)

---

## 8. Definición de terminado (Definition of Done)

Un release de RadioChi se considera listo cuando:
1. Cumple paridad funcional con Astro.
2. Añade capacidades CMS y newsletter por colas.
3. Cumple seguridad mínima definida.
4. Cumple objetivos de rendimiento.
5. Incluye plan de rollback probado.

---

## 9. Riesgos críticos y mitigaciones

1. Riesgo de regresión visual:
   - Mitigación: revisión por secciones y snapshots.
2. Riesgo de pérdida de contenido:
   - Mitigación: scripts de importación idempotentes.
3. Riesgo de envío masivo fallido:
   - Mitigación: colas dedicadas y logs por campaña.
4. Riesgo de caída en despliegue:
   - Mitigación: blue/green o ventana controlada con rollback.

---

## 10. Referencias técnicas recomendadas

- Laravel Installation 12.x: https://laravel.com/docs/12.x/installation
- Laravel Deployment 12.x: https://laravel.com/docs/12.x/deployment
- Inertia Server-Side Setup: https://inertiajs.com/docs/v2/installation/server-side-setup
- Inertia SSR: https://inertiajs.com/server-side-rendering

---

## 11. Proyecto base de origen y método de clonación funcional

Proyecto base obligatorio para la adaptación:

- `C:\Users\fernandocardona\Documents\ContentWorkPC26\RadioChi-Website-2025`

Método correcto de migración hacia `NewRadiochiWebsite26`:
1. Abrir y levantar `NewRadiochiWebsite26` en Docker.
2. Auditar sección por sección del Astro original (`src/pages`, `src/components`, `src/i18n`, `src/data`).
3. Modelar en Laravel las entidades necesarias y mover el contenido JSON a PostgreSQL.
4. Recrear cada bloque de UI en React Inertia manteniendo animaciones clave con Framer/GSAP según blueprint.
5. Validar equivalencia funcional con checklist por módulo antes de cerrar cada sprint.
6. Mantener el proyecto Astro como fuente de verdad durante toda la migración hasta cutover final.

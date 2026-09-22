# Auditoría Exhaustiva y Plan de Cierre de Migración
## RadioChi Astro Legacy -> Laravel 12 + React + Inertia + Tailwind + Framer

> Actualizacion operativa 2026-09-22:
> este documento queda como auditoria historica de baseline y ya no refleja por si solo el estado real del repo.
> El estado correcto hoy es:
> - stack base en `Laravel 13`,
> - frontend publico servido desde payload CMS/BD,
> - backoffice oficial completado en `React + Inertia + Tailwind` bajo `/backoffice/*`,
> - automatizacion/produccion base de Fase 8 ya implementada en repo,
> - QA/cierre del backoffice documentado en `docs/documentation/FASE11_BACKOFFICE_QA_SEGURIDAD_DOD.md`.
>
> Regla de uso segura:
> - usar este archivo solo como baseline historico y mapa de brechas antiguas,
> - no usar sus estados `PENDIENTE` / `PARCIAL` como backlog operativo actual sin contrastarlos antes con `PLAN-REMAKE.md`, `docs/plans/PLAN_BACKEND_React_inertia_Tailwind.md` y la documentacion `FASE*.md`.
>
> Siguiente punto seguro de implementacion tras esta alineacion:
> - `GA4 + eventos de navegacion y conversion del frontend publico`, de forma aditiva, condicionada por `env/settings` y sin tocar la maquetacion.

## 1) Objetivo del documento

Definir, con criterio técnico y operativo, el estado real actual de `NewRadiochiWebsite26` frente al baseline `RadioChi-Website-2025` y detallar los pasos faltantes para lograr clonación funcional/visual completa y continuar con la implementación premium definida en el plan maestro.

Alcance de auditoría:
- Revisión de estado del proyecto Laravel nuevo.
- Revisión de baseline Astro legacy.
- Análisis de brecha por módulos.
- Plan metodológico de cierre con checklist verificable.

---

## 2) Estado actual auditado

## 2.1 Proyecto destino (Laravel)

Ruta:
- `C:\Users\fernandocardona\Documents\ContentWorkPC26\FCT_MASTER_PLATAFORM\NewRadiochiWebsite26`

Estado confirmado:
- Base Laravel 12 operativa.
- Inertia Laravel instalado.
- React + Vite + Tailwind + Framer integrados.
- Docker operativo con aislamiento por contenedores:
  - `newradiochiwebsite26_app`
  - `newradiochiwebsite26_nginx`
  - `newradiochiwebsite26_postgres`
  - `newradiochiwebsite26_redis`
- DB PostgreSQL y Redis activos.
- Migraciones ejecutadas para `pages`, `page_translations`, `events`, `media_assets`, `seo_meta` y `newsletter_*`.
- Home de Inertia implementada por secciones con navegación por anclas y progress bar.
- Rutas multidioma activas: `/`, `/en`, `/ca`, `/fr`, `/it`, `/de`.
- SEO técnico base activo en frontend público:
  - canonical
  - hreflang + `x-default`
  - OG/Twitter
  - JSON-LD
  - `sitemap.xml`
  - `robots.txt`
- API admin protegida con `auth.basic` + `throttle:admin` para:
  - events
  - pages + translations
  - media
  - seo-meta
  - newsletter subscribers/campaigns/logs
- Jobs implementados:
  - envío de newsletter por cola (`SendNewsletterCampaignJob`)
  - sync Instagram con caché (`SyncInstagramFeedJob`)
- Hardening base activo:
  - `SecurityHeaders` middleware
  - rate limiting de admin

Cobertura funcional real hoy:
- Infraestructura local: `completada` (nivel base).
- Frontend paridad Astro: `parcial` (estructura por secciones activa, aún sin clonación visual exacta).
- CMS real: `parcial` (CRUD API operativo, falta panel visual completo y RBAC).
- i18n multicapa: `parcial` (6 locales activos + traducción frontend; falta ingestión completa desde 114 JSON).
- SEO técnico productivo: `parcial-alto` (metadatos base, sitemap y robots ya operativos).
- Importación de contenido legacy: `parcial` (importador de `calendarevents.json` activo; faltan `events.json` + i18n masivo).
- Jobs/newsletter/instagram: `parcial` (base funcional creada, falta operación robusta de producción).

## 2.2 Proyecto fuente (Astro legacy)

Ruta:
- `C:\Users\fernandocardona\Documents\ContentWorkPC26\RadioChi-Website-2025`

Inventario funcional clave:
- Secciones principales detectadas: `7`
- Subcomponentes detectados: `14`
- Páginas Astro detectadas: `12` (incluye variantes por locale y 404)
- Archivos i18n JSON detectados: `114`
- JSON de datos detectados: `3`
- Locales en uso: `es`, `en`, `ca`, `fr`, `it`, `de`
- Patrón visual: experiencia one-page con navegación por anclas + lógica de scroll fullpage + animaciones custom.
- Integraciones observadas:
  - GA4 (`gtag`)
  - YouTube (thumbnails/reproductor en lightbox)
  - Audio player custom de tracks en sección de música
  - react-icons
  - GSAP en subcomponente de carrusel textual
  - Framer Motion como dependencia del proyecto

Observaciones de baseline (importantes para decisiones):
- `videos.json` está vacío.
- En `CalendarEvents.astro` hay mezcla de ids y referencias (`calendar` / `events`) que debe decidirse si se replica tal cual o se corrige en migración.
- En `ProgressBar.astro` existe `myjournal` aunque la sección está desactivada en navegación.

---

## 3) Matriz de brecha (Gap Analysis)

Escala:
- `OK`: ya implementado.
- `PARCIAL`: base creada pero sin paridad.
- `PENDIENTE`: sin implementación funcional.

### 3.1 Core plataforma
- Laravel 12 base: `OK`
- Docker local aislado: `OK`
- PostgreSQL + Redis: `OK`
- Inertia middleware + app shell: `OK`
- Convenciones de proyecto y README operativo propio: `PENDIENTE`

### 3.2 Paridad frontend Astro -> Inertia React
- HomeSection: `PARCIAL` (implementada, sin paridad visual final Astro)
- AboutSection / AboutSection2: `PARCIAL`
- MusicSection2 (player avanzado): `PARCIAL` (sección existe, player avanzado pendiente)
- CalendarEvents: `PARCIAL` (listado dinámico básico desde DB)
- MediaSection + lightboxes: `PARCIAL` (sin lightbox avanzada)
- ContactSection + marquesinas/logos: `PARCIAL`
- ScrollIndicator + ProgressBar + navegación por anclas/fullpage: `PARCIAL`
- LanguageSwitcher equivalente: `OK` (switch por locale en rutas públicas)

### 3.3 Multidioma
- Rutas por locale (`/`, `/en`, `/ca`, `/fr`, `/it`, `/de`): `OK`
- Sistema de traducciones en backend + frontend: `PARCIAL`
- Importación de 114 JSON i18n a modelo persistente: `PENDIENTE`
- Fallback de idioma y persistencia de preferencia (cookie/session): `OK`

### 3.4 Datos y CMS
- Modelo `events` mínimo: `OK`
- Modelo `pages`/`page_translations`: `OK`
- Modelo `media_assets`: `OK`
- Modelo `seo_meta`: `OK`
- CRUD CMS (panel admin): `PARCIAL` (API CRUD lista; falta panel visual administrativo)
- Roles y permisos (`super_admin`, `editor`, `marketing`, `readonly`): `PENDIENTE`
- Auditoría de acciones CMS: `PENDIENTE`

### 3.5 SEO y analítica
- Canonical por locale: `OK`
- Hreflang completo + x-default: `OK`
- OG/Twitter dinámico por página: `PARCIAL` (home pública cubierta)
- JSON-LD por tipo de contenido: `PARCIAL` (home pública cubierta)
- Sitemap por locale y robots por entorno: `OK`
- GA4 y eventos de navegación/conversión: `PENDIENTE`

### 3.6 Automatización y servicios
- Cola redis en Laravel: `PARCIAL` (configurada; falta cierre de extensión Redis en runtime Docker)
- Newsletter por colas dedicadas: `PARCIAL` (modelo + job + endpoint de queue)
- Integración Instagram por jobs + caché: `PARCIAL`
- Scheduler + workers dedicados: `PENDIENTE`
- Preparación n8n/Ollama: `PENDIENTE`

### 3.7 QA y hardening
- Tests unitarios/feature de negocio: `PARCIAL` (suite feature crítica añadida)
- Lint/format de frontend y php: `PARCIAL`
- Headers de seguridad/CSP: `PARCIAL` (headers base activos; CSP estricta pendiente)
- Rate limits y protección admin: `OK`
- Checklist de rendimiento CWV: `PENDIENTE`

---

## 4) Plan de ejecución faltante (metódico y secuencial)

## Fase A — Congelar baseline y criterios de clonación exacta

Objetivo:
- Evitar ambigüedad de “igual al Astro” y cerrar definición de paridad.

Entregables:
1. Matriz `Astro component -> React Inertia component`.
2. Matriz `JSON/i18n -> tablas`.
3. Matriz `URL legacy -> URL Laravel`.
4. Lista de “quirks legacy” a replicar temporalmente o corregir explícitamente.

Criterio de salida:
- Documento de paridad aprobado y trazable.

## Fase B — Arquitectura frontend React para one-page full experience

Objetivo:
- Replicar estructura visual/navegación principal de Astro.

Entregables:
1. `resources/js/Layouts/PublicLayout.jsx` con shell SEO base.
2. `resources/js/Pages/Home.jsx` estructurada por secciones.
3. `resources/js/Components/sections/*` equivalentes a Astro.
4. `resources/js/Components/subcomponents/*` críticos.
5. Motor de navegación por anclas y progreso equivalente.

Criterio de salida:
- Paridad visual y de interacción de Home en locale `es`.

## Fase C — i18n completo (6 idiomas)

Objetivo:
- Mismo comportamiento de idioma del legacy, pero normalizado para CMS.

Entregables:
1. Estructura de traducciones persistente.
2. Seeder/importador de i18n desde JSON legacy.
3. Middleware de locale por ruta + fallback.
4. LanguageSwitcher con rutas limpias y persistencia de preferencia.

Criterio de salida:
- Home funcional equivalente en `es/en/ca/fr/it/de`.

## Fase D — CMS mínimo viable de producción

Objetivo:
- Operar contenido sin tocar código.

Entregables:
1. Auth admin.
2. Roles/permisos.
3. CRUD:
   - Pages + translations
   - Events
   - Media assets
   - SEO meta
4. Validaciones y políticas.

Criterio de salida:
- Edición de contenido en CMS impacta frontend en caliente.

## Fase E — Datos legacy y paridad dinámica

Objetivo:
- Migrar contenido existente sin pérdida.

Entregables:
1. Importador idempotente de:
   - `src/data/calendarevents.json`
   - `src/data/events.json`
   - i18n JSON
2. Normalización de fechas y slugs.
3. Reporte de llaves faltantes/inconsistentes.

Criterio de salida:
- Contenido mostrado en Laravel coincide con baseline.

## Fase F — SEO técnico y analítica

Objetivo:
- Mantener/elevar posicionamiento y medición desde día 1.

Entregables:
1. Canonical/hreflang.
2. OG/Twitter dinámico.
3. JSON-LD.
4. Sitemap locale-aware.
5. Robots por entorno.
6. GA4 con eventos de navegación/CTA.

Criterio de salida:
- Checklist SEO técnico en verde.

## Fase G — Jobs, newsletter, Instagram y operación

Objetivo:
- Completar capacidades backend del blueprint.

Entregables:
1. Cola `newsletter`.
2. Campañas + logs.
3. Job de sincronización Instagram con caché.
4. Scheduler y workers dedicados.

Criterio de salida:
- Procesos asíncronos estables y trazables.

## Fase H — QA final y hardening de release

Objetivo:
- Llegar a definición de terminado con bajo riesgo de regresión.

Entregables:
1. Suite de tests (feature e integración clave).
2. Validación de seguridad (headers, rate limits, hardening admin).
3. Validación de rendimiento (LCP/INP/CLS objetivos).
4. Plan de rollback + backup/restore test.

Criterio de salida:
- Cumplimiento de DoD del blueprint.

---

## 5) Backlog priorizado inmediato (siguiente bloque de ejecución)

Prioridad crítica (P0):
1. Completar paridad visual exacta de secciones Astro (tipografías, spacing, animaciones, overlays).
2. Migrar subcomponentes críticos faltantes (lightboxes, carruseles y player avanzado).
3. Implementar importador de `events.json` + normalización completa.
4. Implementar importador masivo de i18n (`114` JSON) hacia persistencia en DB.

Prioridad alta (P1):
1. Construir panel admin visual (no solo API) para contenido y SEO.
2. Añadir roles/permisos (`spatie/laravel-permission`) y policies por módulo.
3. Cerrar SEO de páginas internas (events/pages) con metadata dinámica real.
4. Activar scheduler + workers dedicados para newsletter/instagram.

Prioridad media (P2):
1. Integrar GA4 y eventos de conversión.
2. Endurecer CSP y seguridad avanzada de admin.
3. Ejecutar benchmark CWV y optimizaciones finales.

---

## 6) Checklist de “clonación exacta” (control de calidad)

Frontend:
- [x] Las secciones visibles y su orden coinciden a nivel estructural.
- [x] Navegación por anclas existe.
- [x] Comportamiento de scroll/progreso base existe.
- [ ] Animaciones críticas equivalentes.
- [ ] Media y player mantienen UX funcional.

Contenido:
- [x] Textos por locale operativos (6 idiomas con fallback).
- [x] Datos de eventos básicos cargados en DB.
- [ ] Assets visuales equivalentes.

SEO:
- [x] Canonical/hreflang correctos.
- [x] Metadata OG/Twitter base implementados.
- [x] Sitemap y robots correctos.

Operación:
- [ ] Docker estable.
- [x] Migraciones y seeds limpias.
- [x] Tests críticos en verde.
- [ ] Logs sin errores críticos.

---

## 7) Riesgos principales y mitigación

Riesgo 1: divergencia visual respecto a Astro.
- Mitigación: QA por sección con comparativa lado a lado y checklist de paridad.

Riesgo 2: pérdida o inconsistencia de traducciones.
- Mitigación: importador idempotente + reporte de llaves huérfanas.

Riesgo 3: deuda técnica por scripts legacy complejos.
- Mitigación: encapsular comportamientos en componentes React y pruebas de interacción.

Riesgo 4: caída SEO en cutover.
- Mitigación: fase SEO dedicada + validación previa de metadatos/sitemaps/redirecciones.

---

## 8) Definición de listo para continuar implementaciones avanzadas

Podemos considerar “clonación base completada” cuando:
1. Home y secciones clave en React Inertia tienen paridad funcional/visual.
2. 6 idiomas operan con contenido real.
3. CMS mínimo permite editar contenido sin tocar código.
4. SEO técnico base está activo y validado.
5. El entorno Docker es reproducible y estable.

Estado de avance estimado actual frente al objetivo final:
- Plataforma base: alta.
- Paridad Astro frontend: media-baja.
- CMS/backend de negocio: media-baja.
- SEO/i18n productivo: media.
- Automatización/operación avanzada: media-baja.

Conclusión operativa:
- El proyecto está correctamente “encendido” como base técnica.
- La migración ya no está en estado “placeholder”; existe una base funcional ejecutable.
- Falta cerrar la paridad exacta Astro y la capa operativa premium completa.
- El siguiente frente de trabajo debe centrarse en paridad visual exacta + importación total de contenido + panel admin visual + operación de colas productiva.

## 8.1) Plan de actuación inmediato (ejecución sugerida)

Semana/Sprint 1 (paridad visual y UX):
1. Migrar subcomponentes visuales críticos de Astro:
   - lightbox de media
   - player de música avanzado
   - carruseles/marquesinas
2. Ajustar spacing, tipografías, overlays y timing de animaciones para paridad lado a lado.
3. Cerrar sección Contact con assets/logos legacy.

Semana/Sprint 2 (datos e i18n full):
1. Importar `events.json` y enlazarlo a frontend.
2. Importar los `114` JSON de i18n a persistencia (`pages`/`page_translations` o tabla dedicada).
3. Implementar fallback completo por clave faltante y validación automática de cobertura por locale.

Semana/Sprint 3 (CMS visual y seguridad):
1. Construir panel admin visual sobre APIs ya disponibles.
2. Implementar roles/permisos por módulo.
3. Añadir auditoría de acciones administrativas.
4. Endurecer seguridad de admin (CSP estricta, políticas y flujos de acceso).

Semana/Sprint 4 (operación y release hardening):
1. Activar scheduler + workers dedicados (`default` y `newsletter`).
2. Añadir batch/retry/backoff robusto para campañas.
3. Integrar GA4 + eventos de conversión.
4. Ejecutar checklist CWV y cerrar optimizaciones de performance.

---

## 9) Referencias técnicas oficiales a seguir durante la ejecución

Laravel:
- https://laravel.com/docs/12.x/installation
- https://laravel.com/docs/12.x/vite
- https://laravel.com/docs/12.x/localization
- https://laravel.com/docs/12.x/queues
- https://laravel.com/docs/12.x/scheduling
- https://laravel.com/docs/12.x/deployment

Inertia:
- https://inertiajs.com/docs/v2/installation/server-side-setup
- https://inertiajs.com/docs/v2/installation/client-side-setup

Tailwind:
- https://tailwindcss.com/docs/guides/vite
- https://tailwindcss.com/docs/installation/framework-guides

Animación (Framer/Motion):
- https://motion.dev/docs/react
- https://motion.dev/docs/react-installation

Notas de decisión técnica:
- En este proyecto actual se mantiene `framer-motion` para continuidad del baseline y menor fricción de migración.
- Si se decide migrar a `motion/react`, debe tratarse como refactor controlado posterior a la paridad funcional.

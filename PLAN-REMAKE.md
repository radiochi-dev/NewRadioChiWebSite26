# PLAN REMAKE

## Objetivo

Llevar el proyecto actual a un estado que cumpla de forma verificable estos requisitos:

1. Desarrollo local 100% Docker Desktop, sin XAMPP ni dependencias de host.
2. Stack objetivo exacto: Laravel 13 + Inertia + Tailwind + Framer Motion + Spatie + Filament + PostgreSQL.
3. CMS visual real para administrar el 100% del contenido: textos multilingues, imagenes, videos, PDFs, enlaces, SEO y automatizaciones.
4. Despliegue en VPS Hostinger con arquitectura de produccion que incluya:
   - aplicacion principal,
   - PostgreSQL principal del proyecto,
   - n8n con base de datos PostgreSQL independiente,
   - capacidad de n8n para consultar la base principal con credenciales limitadas,
   - backend Laravel capaz de disparar y consultar automatizaciones de n8n,
   - Ollama en el VPS con modelo ligero para automatizaciones internas.

## Estado actual resumido

- Docker local base: parcial.
- Laravel 13: no cumple.
- Inertia, Tailwind, Framer: cumple base.
- PostgreSQL principal: cumple base.
- Filament: no cumple.
- Spatie: no cumple.
- CMS visual completo: no cumple.
- Produccion Hostinger con n8n/Ollama: no cumple.
- Contenido 100% dinamico desde backend: no cumple.

## Criterios de exito finales

- `docker compose up -d --build` levanta desarrollo completo sin depender de PHP, Composer o npm del host.
- `composer.json` queda en Laravel 13 con dependencias compatibles.
- Existe panel Filament funcional con login, roles, permisos y recursos de contenido.
- El CMS permite administrar por locale:
  - home,
  - about,
  - music,
  - calendar/events,
  - media gallery,
  - contact,
  - footer/legal,
  - SEO por pagina y por entidad.
- Existe arquitectura de produccion reproducible en Hostinger con:
  - `radiochi_app`,
  - `radiochi_nginx`,
  - `radiochi_postgres`,
  - `radiochi_redis`,
  - `radiochi_worker`,
  - `radiochi_scheduler`,
  - `n8n`,
  - `n8n_postgres`,
  - `ollama`.
- El backend Laravel puede:
  - lanzar workflows de n8n por webhook/API,
  - consultar estado/ejecuciones,
  - exponer endpoints seguros consumibles por n8n,
  - delegar tareas puntuales a Ollama.

## Auditoria base de documentos guia

### Hallazgos trasladados desde `MASTER_ARCHITECTURE_PLAN.md`

1. El documento sigue orientado a una estrategia dual RadioChi + Neway en el mismo VPS. Para este repo, el plan operativo debe centrarse primero en RadioChi y dejar cualquier coexistencia multiapp como capacidad futura, no como eje del remake.
2. El documento sigue mencionando Laravel 12 como stack objetivo de RadioChi. Debe alinearse a Laravel 13.
3. El documento ya anticipa `n8n` y `ollama`, pero no especifica:
   - base de datos independiente de n8n,
   - permisos de lectura controlada sobre la base principal,
   - contrato backend <-> n8n,
   - politicas de seguridad para esas integraciones.
4. El documento habla de CMS minimo viable, pero el requisito actual es CMS completo administrable al 100%.

### Hallazgos trasladados desde `RADIOCHI_TECHNICAL_BLUEPRINT.md`

1. El blueprint sigue anclado a Laravel 12 y debe corregirse a Laravel 13.
2. El blueprint reconoce preparacion n8n/Ollama como pendiente, pero no la desarrolla en un plan de infraestructura y aplicacion.
3. El blueprint describe un CMS parcial y APIs admin base, pero no cubre la dinamizacion completa por seccion ni la migracion total de JSON legacy a contenido administrable.
4. Falta una matriz editorial exhaustiva que identifique todos los campos administrables por seccion.

## Auditoria actual del frontend y del grado de dinamizacion

### Situacion real actual

El frontend publico sigue dependiendo de contenido legacy cargado desde JSON y arrays embebidos en frontend. La pagina principal monta 6 secciones fijas:

- `home`
- `about`
- `music`
- `calendar`
- `media`
- `contact`

Ademas arrastra contenido legal, footer, logos de sponsors, redes sociales y SEO parcial.

### Resultado de la auditoria

1. Hay base de datos y APIs admin parciales para `pages`, `page_translations`, `events`, `media_assets`, `seo_meta` y newsletter.
2. Esa base no gobierna todavia el frontend publico principal.
3. El frontend sigue leyendo:
   - traducciones JSON por locale,
   - calendarios desde JSON legacy,
   - fotos/videos desde arrays hardcodeados,
   - logos/sponsors/social links hardcodeados,
   - textos legales desde JSON legacy,
   - SEO home parcialmente generado en controlador.
4. Conclusion: el proyecto aun no es 100% administrable desde backend.

## Matriz editorial obligatoria para hacer 100% dinamico el sitio

### 1. Home

Debe administrarse desde CMS:
- slides del hero,
- logo de slide,
- titulo,
- subtitulo,
- descripcion,
- imagen principal,
- CTA label,
- CTA URL,
- orden de slides,
- estado publicado,
- programacion por fecha si se necesita.

Modelo recomendado:
- `pages`
- `page_blocks`
- `page_block_translations`
- media relacionada por bloque

### 2. About

Debe administrarse desde CMS:
- pasos del scrollytelling,
- titulo,
- subtitulo,
- contenido rich text,
- imagen de fondo por paso,
- orden,
- estado publicado.

Modelo recomendado:
- `page_blocks` tipo `about_step`
- traducciones por locale
- relacion media por bloque

### 3. Music

Debe administrarse desde CMS:
- hero title,
- subtitle,
- descripcion,
- tracks,
- portada/label image,
- URL de SoundCloud,
- CTA SoundCloud,
- CTA Follow,
- orden y destacado.

Modelo recomendado:
- `music_tracks`
- `music_track_translations`
- media asociada
- enlaces externos estructurados

### 4. Calendar / Events

Debe administrarse desde CMS:
- eventos,
- fecha inicio,
- fecha fin,
- localizacion,
- pais,
- logo/cartel,
- CTA compra,
- URL externa,
- texto fallback tipo "dates coming soon",
- etiquetas por locale.

Modelo recomendado:
- ampliar `events`
- agregar traducciones por locale
- agregar media principal
- agregar campos editoriales y SEO por evento

### 5. Media

Debe administrarse desde CMS:
- galeria de fotos,
- alt text por locale,
- caption por locale,
- fecha,
- categoria,
- orden,
- videos,
- thumbnail,
- titulo,
- duracion,
- URL YouTube,
- posibilidad de adjuntar PDF/press kit si aplica.

Modelo recomendado:
- reemplazar `media_assets` puramente descriptivo por Media Library real
- `media_collections`
- `video_items`
- `downloadable_files`

### 6. Contact

Debe administrarse desde CMS:
- titulo,
- enlaces de redes sociales,
- sponsor logos,
- sponsor links,
- textos de las lineas marquee,
- emails y canales de contacto,
- CTAs externos.

Modelo recomendado:
- `social_links`
- `partners`
- `page_blocks` tipo `contact_marquee`

### 7. Footer y legal

Debe administrarse desde CMS:
- copyright,
- creditos,
- terminos,
- privacidad,
- cookies,
- enlaces asociados,
- contenido HTML o rich text por locale.

Modelo recomendado:
- `legal_documents`
- `legal_document_translations`
- `settings`

### 8. SEO transversal

Debe administrarse desde CMS:
- meta title,
- meta description,
- canonical,
- Open Graph,
- Twitter Card,
- JSON-LD,
- indice de sitemap,
- redirecciones 301,
- hreflang por locale.

Modelo recomendado:
- ampliar `seo_meta`
- `redirect_rules`
- generacion automatica de sitemap por tipo de contenido

### 9. Configuracion global

Debe administrarse desde CMS:
- locales activos,
- idioma por defecto,
- enlaces globales,
- configuracion de analytics,
- identificadores de integraciones,
- textos globales reutilizables.

Modelo recomendado:
- `settings`
- `settings_translations`
- `integrations`

## Fase 0. Baseline y guardarrailes

**Estado:** COMPLETADA

### Paso 0.1
~~Crear rama de trabajo limpia desde el estado restaurado y no tocar frontend publico salvo alcance autorizado.~~

### Paso 0.2
~~Congelar como reglas:~~
- Docker Desktop only,
- nada de XAMPP,
- nada de `php artisan serve` ni `npm run dev` en host,
- nada de cambios esteticos no pedidos.

### Paso 0.3
~~Definir checklist fijo por cambio:~~
- `docker compose config`
- `docker compose ps`
- `php artisan test`
- acceso HTTP publico
- acceso HTTP admin
- auditoria pre/post de archivos tocados

## Fase 1. Desarrollo 100% Docker

**Estado:** COMPLETADA

### Objetivo
~~Eliminar cualquier dependencia del host para arrancar y desarrollar.~~

### Paso 1.1
~~Rehacer `docker-compose.yml` para desarrollo estable con:~~
- `app`
- `nginx`
- `postgres`
- `redis`
- `node` o servicio `vite`

### Paso 1.2
~~Separar Dockerfiles o stages:~~
- desarrollo
- produccion multi-stage

### Paso 1.3
~~Garantizar dentro de contenedores:~~
- Composer
- extensiones PHP requeridas
- Node/npm o estrategia equivalente

### Paso 1.4
~~Crear flujo de arranque Windows PowerShell Docker-only:~~
- `up`
- `build`
- `rebuild`
- comandos internos solo via `docker compose exec`

### Paso 1.5
~~Verificacion de salida:~~
- levantado limpio,
- sin contenedores huerfanos,
- sin puertos abiertos globalmente sin control,
- frontend y backend respondiendo.

## Fase 2. Alineacion de stack a Laravel 13

**Estado:** SIGUIENTE FASE SEGURA

### Objetivo
Cumplir exactamente el stack pedido.

### Paso 2.1
Actualizar el stack PHP/Laravel y validar compatibilidad real de:
- Laravel 13
- Inertia
- React
- Tailwind
- Filament
- Spatie
- paquetes existentes

### Paso 2.2
Regenerar lock y corregir incompatibilidades de:
- middleware,
- auth,
- bootstrap,
- tests,
- providers.

### Paso 2.3
Verificacion de salida:
- `php artisan about`
- `php artisan test`
- rutas publicas y privadas sanas

## Fase 3. Filament base + Spatie

### Objetivo
Sustituir el admin custom parcial por CMS visual mantenible y seguro.

### Paso 3.1
Instalar Filament v5 compatible con Laravel 13.

### Paso 3.2
Instalar y configurar:
- `spatie/laravel-permission`
- `spatie/laravel-medialibrary`
- `spatie/laravel-translatable`
- `spatie/laravel-activitylog`

### Paso 3.3
Crear panel admin base:
- login,
- dashboard,
- navegacion,
- grupos editoriales,
- control por roles.

### Paso 3.4
Definir roles base:
- `super_admin`
- `editor`
- `marketing`
- `readonly`

### Paso 3.5
Migrar gradualmente la operativa del dashboard actual a recursos Filament.

## Fase 4. Remodelado editorial y de datos

### Objetivo
Crear el modelo de datos real que permita administrar todo el contenido.

### Paso 4.1
Conservar y revisar tablas utiles existentes:
- `pages`
- `page_translations`
- `events`
- `seo_meta`
- `newsletter_*`

### Paso 4.2
Introducir tablas faltantes para contenido total:
- `page_blocks`
- `page_block_translations`
- `music_tracks`
- `music_track_translations`
- `partners`
- `social_links`
- `legal_documents`
- `legal_document_translations`
- `downloadable_files`
- `redirect_rules`
- `settings`
- `settings_translations`
- `automation_logs` si se decide persistir ejecuciones n8n internas

### Paso 4.3
Definir FKs, indices y politicas de borrado.

### Paso 4.4
Definir que entidades seran translatables con Spatie y cuales requeriran tabla satelite.

## Fase 5. Migracion de contenido legacy a contenido administrable

### Objetivo
Sacar el sitio del modo JSON legacy y pasarlo a PostgreSQL + CMS.

### Paso 5.1
Inventariar y migrar todos los archivos legacy:
- `home.json`
- `about.json`
- `music.json`
- `calendarEvents.json`
- `contact.json`
- `media.json`
- `introwebsite.json`
- `footer.json`
- `terms-policy-cookies.json`
- `videos.json`
- `events.json`

### Paso 5.2
Crear importadores idempotentes por dominio:
- paginas y bloques
- music tracks
- eventos
- media
- legales
- SEO base

### Paso 5.3
Validar paridad por locale:
- es
- en
- ca
- fr
- it
- de

## Fase 6. CMS por seccion

### Objetivo
Entregar administracion total seccion por seccion.

### Paso 6.1 Home
Recursos Filament para hero slides, intro y CTA.

### Paso 6.2 About
Recursos Filament para pasos de scrollytelling y fondos.

### Paso 6.3 Music
Recursos Filament para tracks, embeds y CTAs.

### Paso 6.4 Calendar
Recursos Filament para eventos, carteles, fechas y ticket URLs.

### Paso 6.5 Media
Recursos Filament para fotos, videos, colecciones y archivos descargables.

### Paso 6.6 Contact
Recursos Filament para redes, sponsors, links y lineas marquee.

### Paso 6.7 Legal y footer
Recursos Filament para terminos, privacidad, cookies y pie global.

### Paso 6.8 SEO
Recursos Filament para metadata, redirects y sitemap rules.

## Fase 7. Integracion del frontend publico con el CMS

### Objetivo
Hacer que el frontend lea datos administrables en lugar de contenido embebido.

### Paso 7.1
Sustituir `getLegacyContent`, `getLegacyCalendarData` y `getLegacyMediaData` por queries/controladores/DTOs.

### Paso 7.2
Mantener la fidelidad visual del frontend original sin redisenos no autorizados.

### Paso 7.3
Renderizar por locale:
- textos,
- media,
- CTAs,
- legales,
- SEO.

### Paso 7.4
Verificacion de salida:
- frontend con misma estructura visual,
- datos desde DB/CMS,
- sin dependencia del JSON legacy para produccion.

## Fase 8. Produccion Hostinger VPS con n8n y Ollama

### Objetivo
Definir una arquitectura de produccion realista, segura y operable.

### Paso 8.1 Infraestructura de servicios
Definir stack de produccion con:
- `radiochi_nginx`
- `radiochi_app`
- `radiochi_worker`
- `radiochi_scheduler`
- `radiochi_postgres`
- `radiochi_redis`
- `n8n`
- `n8n_postgres`
- `ollama`
- reverse proxy/SSL segun estrategia final

### Paso 8.2 Base de datos independiente para n8n
Implementar `n8n_postgres` separado de `radiochi_postgres`.

Requisitos:
- base propia de n8n para ejecuciones, credenciales y metadatos,
- usuario y secretos propios,
- backups independientes,
- no mezclar datos internos de n8n con la base del proyecto.

### Paso 8.3 Acceso controlado de n8n a la base principal
Crear un usuario tecnico de solo lectura o permisos minimizados sobre `radiochi_postgres` para consultas de automatizacion.

Requisitos:
- acceso solo a tablas/vistas aprobadas,
- sin privilegios de schema ni DDL,
- preferencia por vistas/materialized views si aplica,
- auditoria de consultas si el alcance lo exige.

### Paso 8.4 Integracion backend Laravel <-> n8n
Definir contrato bidireccional:

Laravel hacia n8n:
- webhooks firmados,
- API keys/headers HMAC,
- disparo de workflows por eventos de negocio,
- endpoints para consultar estado de ejecucion.

n8n hacia Laravel:
- webhooks autenticados,
- endpoints internos protegidos,
- posibilidad de leer datos del proyecto,
- posibilidad de registrar resultados en tablas de automatizacion.

Casos objetivo:
- automatizaciones de marketing,
- generacion de contenido asistido,
- sincronizaciones programadas,
- enriquecimiento SEO,
- tareas internas CMS.

### Paso 8.5 Integracion con Ollama en VPS
Instalar `ollama` como servicio interno no expuesto publicamente.

Requisitos:
- modelo ligero configurable,
- consumo solo desde backend o n8n por red privada,
- limites de recursos del VPS,
- colas o timeouts para evitar bloquear peticiones web.

Usos previstos:
- resumen/clasificacion de contenido,
- sugerencias SEO,
- asistencia editorial,
- etiquetado de media,
- borradores internos.

### Paso 8.6 Seguridad y operaciones
- no exponer Postgres ni Redis al exterior,
- n8n protegido tras autenticacion fuerte y, si procede, acceso solo por VPN/IP allowlist,
- backups cifrados,
- healthchecks,
- rotacion de secretos,
- logs y monitoreo.

## Fase 9. QA, seguridad y Definition of Done

### Paso 9.1 Tests backend
- auth
- roles/permisos
- CRUD Filament
- traducciones
- media real
- integraciones n8n

### Paso 9.2 Tests frontend
- rutas publicas
- locale switching
- render de contenido dinamico
- SEO visible

### Paso 9.3 Smoke tests de infraestructura
- desarrollo Docker limpio
- produccion compose valida
- workers y scheduler vivos
- n8n operativo
- Ollama operativo

### Paso 9.4 Checklist final
- Docker Desktop only: si
- XAMPP: no
- Laravel 13: si
- Inertia: si
- Tailwind: si
- Framer: si
- Spatie: si
- Filament: si
- PostgreSQL principal: si
- PostgreSQL independiente para n8n: si
- backend integrado con n8n: si
- Ollama operativo en VPS: si
- CMS visual completo: si
- contenido 100% administrable: si
- Hostinger preparado para produccion: si

## Orden recomendado de ejecucion

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

## Riesgos que este plan evita

- volver a introducir dependencias host tipo XAMPP,
- quedarse en Laravel 12 cuando el requisito es Laravel 13,
- seguir con un admin parcial en vez de un CMS real,
- dejar contenido clave anclado a JSON hardcodeado,
- mezclar la base interna de n8n con la base principal del proyecto,
- exponer integraciones automatizadas sin contrato ni seguridad,
- llegar a Hostinger sin una ruta de despliegue reproducible.

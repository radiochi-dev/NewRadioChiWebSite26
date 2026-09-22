# Plan Maestro de Arquitectura y Migración
## Ejecución por etapas: primero RadioChi, después Neway

Este documento es la guía operativa principal para llevar el ecosistema a nivel premium sin romper el proyecto actual.

Alcance real de ejecución:
- Fase 1: preparar infraestructura común en VPS.
- Fase 2: construir y desplegar únicamente New RadioChi.
- Fase 3: mantener Neway definido y listo, pero no desplegado hasta segunda ventana.
- Fase 4: activar Neway cuando RadioChi esté estabilizado.

---

## 1. Respuesta clara a la duda de coexistencia en el mismo VPS

Sí, ambos proyectos coexistirán en el mismo VPS, pero no tienen por qué ejecutarse al mismo tiempo durante la etapa inicial.

Modelo operativo recomendado:
- En VPS habrá una infraestructura base única: Traefik, observabilidad, red y políticas.
- RadioChi se despliega primero y queda activo.
- Neway se mantiene en estado preparado en:
  - repositorio propio,
  - contenedores definidos,
  - variables definidas,
  - perfil de compose desactivado.

Conclusión práctica:
- Misma máquina, aislamiento total, activación por etapas.
- Sin colisiones entre bases de datos, puertos ni secretos.

---

## 2. Estado actual auditado (entrada de migración RadioChi)

Frontend y componentes actuales:
- Secciones: [sections](file:///c:/Users/fernandocardona/Documents/ContentWorkPC26/RadioChi-Website-2025/src/components/sections)
- Subcomponentes: [subcomponentes](file:///c:/Users/fernandocardona/Documents/ContentWorkPC26/RadioChi-Website-2025/src/components/subcomponentes)
- Rutas por idioma: [pages](file:///c:/Users/fernandocardona/Documents/ContentWorkPC26/RadioChi-Website-2025/src/pages)

Datos actuales:
- Eventos base: [events.json](file:///c:/Users/fernandocardona/Documents/ContentWorkPC26/RadioChi-Website-2025/src/data/events.json)
- Datos complementarios: [data](file:///c:/Users/fernandocardona/Documents/ContentWorkPC26/RadioChi-Website-2025/src/data)
- Traducciones actuales: [i18n](file:///c:/Users/fernandocardona/Documents/ContentWorkPC26/RadioChi-Website-2025/src/i18n)

Configuración actual:
- Scripts y dependencias: [package.json](file:///c:/Users/fernandocardona/Documents/ContentWorkPC26/RadioChi-Website-2025/package.json)
- Config Astro: [astro.config.mjs](file:///c:/Users/fernandocardona/Documents/ContentWorkPC26/RadioChi-Website-2025/astro.config.mjs)
- Variables Instagram: [.env.example](file:///c:/Users/fernandocardona/Documents/ContentWorkPC26/RadioChi-Website-2025/.env.example)

---

## 3. Arquitectura objetivo en producción

### 3.1 Redes Docker
- `proxy_net`
- `radiochi_net`
- `neway_net`
- `ai_services_net`
- `ops_net`

### 3.2 Servicios comunes
- `traefik`
- `watchtower`
- `uptime-kuma`
- `backup-runner`
- `ollama`
- `n8n`

### 3.3 Servicios RadioChi
- `radiochi_web`
- `radiochi_app`
- `radiochi_worker`
- `radiochi_scheduler`
- `radiochi_db`
- `radiochi_redis`

### 3.4 Servicios Neway
- `neway_web`
- `neway_app`
- `neway_worker`
- `neway_scheduler`
- `neway_db`
- `neway_redis`

---

## 4. Estrategia de repositorios y protección total del proyecto actual

Regla obligatoria:
- El repo actual de Astro no se toca como base de despliegue nuevo.

Repos recomendados:
- `radiochi-website-astro-legacy` (solo mantenimiento correctivo).
- `radiochi-platform-laravel` (nuevo principal).
- `neway-platform-laravel` (nuevo secundario).
- `vps-infra-hostinger` (infraestructura y operaciones).

Beneficio:
- Historial limpio por producto.
- Pipelines aislados.
- Riesgo mínimo de romper producción actual.

---

## 5. Fases metodológicas con objetivos, requisitos y entregables

## Fase 0: Preparación y baseline

Objetivo:
- Fijar baseline técnico antes de migrar.

Requisitos:
- Inventario de rutas y contenido.
- Inventario SEO actual.
- Inventario de traducciones y assets.

Entregables:
- Matriz URL actual -> URL nueva.
- Matriz componentes Astro -> componentes React.
- Matriz JSON actual -> tablas SQL.

Criterio de salida:
- Todo el alcance funcional y SEO inventariado.

## Fase 1: Infraestructura VPS base

Objetivo:
- Dejar el VPS listo para operar múltiples proyectos con seguridad.

Requisitos:
- Ubuntu LTS actualizado.
- Docker y Compose instalados.
- SWAP operativo.
- Traefik funcional.

Entregables:
- Redes docker creadas.
- Traefik emitiendo certificados.
- Uptime Kuma y backups operativos.

Criterio de salida:
- Infra operativa y validada antes de desplegar apps.

## Fase 2: Construcción New RadioChi

Objetivo:
- Migrar RadioChi a Laravel 12 + React + Inertia + Tailwind + Framer Motion.

Requisitos:
- Repo nuevo.
- Estructura Laravel lista.
- CMS mínimo viable.

Entregables:
- Front home y secciones replicadas.
- Panel CMS para eventos, media y traducciones.
- Newsletter por colas.

Criterio de salida:
- Paridad funcional con Astro y mejoras de backend activas.

## Fase 3: Despliegue RadioChi en producción

Objetivo:
- Publicar RadioChi nuevo sin downtime significativo.

Requisitos:
- Staging validado.
- Checklist seguridad y SEO en verde.
- Plan rollback.

Entregables:
- DNS actualizado.
- SSL activo.
- Monitoreo y alertas activos.

Criterio de salida:
- Producción estable durante ventana de observación.

## Fase 4: Preparación Neway sin activación

Objetivo:
- Dejar Neway técnicamente terminado para despliegue posterior.

Requisitos:
- Infra y app definidas con profile de compose.
- Base de datos y secretos listos.

Entregables:
- Perfil `neway` desactivado por defecto.
- Pipeline de despliegue listo.

Criterio de salida:
- Neway listo para lanzar en segunda ventana.

---

## 6. Desarrollo local y desarrollo en VPS con Docker

## 6.1 Local

Regla:
- Cada proyecto con su propio `docker-compose.yml` o perfiles.

Modo recomendado:
- Levantar solo RadioChi durante primera fase.
- Mantener Neway apagado localmente para no mezclar contextos.

Ejemplo operativo conceptual:

```bash
docker compose --profile radiochi up -d
docker compose --profile neway up -d
docker compose --profile ai up -d
```

Uso en fase actual:
- Ejecutar `radiochi`.
- No ejecutar `neway` hasta terminar RadioChi.

## 6.2 VPS

Regla:
- Misma infraestructura, activación por dominio y perfil.

Flujo:
1. Se levantan servicios base.
2. Se levanta RadioChi.
3. Neway queda definido pero apagado.
4. Cuando toque segunda etapa, se levanta Neway.

---

## 7. Seguridad minuciosa por capas

## 7.1 Capa servidor
- Usuario no root para operaciones.
- SSH por llave.
- `PermitRootLogin no`.
- `PasswordAuthentication no`.
- UFW sólo puertos 22, 80, 443.
- Fail2ban activo en sshd y paneles.
- Actualizaciones de seguridad automáticas.

## 7.2 Capa contenedores
- Imágenes oficiales o auditadas.
- Versiones fijadas por tag estable.
- No exponer puertos internos de DB/Redis al exterior.
- Redes privadas por dominio.
- Volúmenes persistentes con permisos restrictivos.
- Healthchecks obligatorios.

## 7.3 Capa aplicación
- CSP estricta.
- CSRF activo en Laravel.
- Validación de request con FormRequest.
- Sanitización de entrada para CMS.
- Límite de intentos en login.
- 2FA para usuarios admin.
- Auditoría de acciones de CMS.

## 7.4 Capa base de datos
- Usuarios separados por proyecto.
- Privilegios mínimos.
- Backups cifrados.
- Restore probado semanalmente.
- Rotación de credenciales.

## 7.5 Capa secretos
- Secretos fuera de git.
- `.env` por entorno.
- Variables sensibles rotadas trimestralmente.
- Inventario de secretos con dueño y fecha de expiración.

---

## 8. SEO técnico y SEM en el diseño desde día 1

SEO técnico obligatorio:
- Canonical por idioma.
- Hreflang completo.
- JSON-LD por tipo de página.
- Sitemaps por locale.
- Tabla de redirecciones 301 desde URLs antiguas.
- Open Graph y Twitter Card por página.
- Metadata editable en CMS.

Rendimiento:
- Objetivo LCP < 2.5s móvil.
- Objetivo INP < 200ms.
- Objetivo CLS < 0.1.
- Carga diferida de media pesada.

SEM:
- UTMs normalizadas.
- Eventos de conversión definidos antes del lanzamiento.
- Integración con GA4 y píxeles publicitarios.
- Landing pages dedicadas por campaña.

---

## 9. Checklist de salida por etapa

Checklist RadioChi antes de salir a producción:
1. Migraciones y seeds ejecutan limpio.
2. Colas y scheduler funcionando.
3. Staging validado con QA funcional.
4. Seguridad validada.
5. SEO técnico validado.
6. Prueba de backup y restore validada.
7. Plan rollback probado.

Checklist Neway antes de activación:
1. Infra profile `neway` validado.
2. Dominio y SSL preparados.
3. Backend funcional mínimo listo.
4. Integraciones externas probadas.
5. Monitoreo y alertas conectados.

---

## 10. Documentos técnicos complementarios

Para detalle exhaustivo por proyecto:
- [RADIOCHI_TECHNICAL_BLUEPRINT.md](file:///c:/Users/fernandocardona/Documents/ContentWorkPC26/RadioChi-Website-2025/RADIOCHI_TECHNICAL_BLUEPRINT.md)
- [NEWAY_TECHNICAL_BLUEPRINT.md](file:///c:/Users/fernandocardona/Documents/ContentWorkPC26/RadioChi-Website-2025/NEWAY_TECHNICAL_BLUEPRINT.md)

---

## 11. Conclusión operativa

La estrategia definitiva es:
- infraestructura común,
- despliegue secuencial,
- aislamiento total,
- seguridad por capas,
- migración de calidad premium,
- lanzamiento de RadioChi primero,
- Neway preparado para segunda etapa sin riesgo para producción activa.

---

## 12. Fuente base obligatoria de migración (RadioChi Astro Legacy)

Para ejecutar la adaptación correctamente, la fuente oficial del frontend legado será siempre el proyecto Astro original ubicado fuera del nuevo workspace, en:

- `C:\Users\fernandocardona\Documents\ContentWorkPC26\RadioChi-Website-2025`

Regla operativa:
1. Se levanta y valida primero `NewRadiochiWebsite26` en su entorno Docker independiente.
2. Una vez operativo, el equipo toma como referencia funcional y visual el proyecto Astro base.
3. La migración no se hace por copia ciega: se replica arquitectura por módulos, se normalizan datos y se adapta al stack Laravel 12 + React + Inertia + Tailwind + Framer.
4. Cada sección migrada debe compararse contra Astro para paridad visual, contenido y comportamiento.
5. El proyecto Astro permanece intacto como baseline de control y rollback de contenidos.

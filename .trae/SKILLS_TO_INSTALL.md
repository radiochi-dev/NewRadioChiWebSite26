# SKILLS TO INSTALL de la pagina https://www.skills.sh/ donde puedes encontrar los comandos para instalar esta lista de skills exactos sin invenciones !!

## Objetivo

Lista de skills recomendados para trabajar este repositorio sin desviarse del stack objetivo ni del alcance del proyecto.

## Estado actual

- Skills instalados a nivel proyecto en [`.trae/skills`](file:///C:/Users/fernandocardona/Documents/ContentWorkPC26/FCT_MASTER_PLATAFORM/NewRadiochiWebsite26/.trae/skills): `laravel-best-practices`, `laravel-plugin-discovery`, `laravel-security`, `laravel-database-optimization`, `laravel-specialist`, `laravel-filament`, `spatie-security`, `postgresql-optimization`, `multi-stage-dockerfile`, `tailwindcss`, `tailwindcss-advanced-layouts`, `framer-motion-animator`.
- Skills builtin ya disponibles en Trae y que no requieren instalación manual: `TRAE-code-review`, `TRAE-debugger`.

## Skills recomendados e instalacion exacta

1. **laravel-best-practices**
`npx skills add https://github.com/asyrafhussin/agent-skills --skill laravel-best-practices -y --copy`
`npx skills add https://github.com/affaan-m/ecc --skill laravel-plugin-discovery -y --copy`
`npx skills add https://github.com/affaan-m/ecc --skill laravel-security -y --copy`
   - Para arquitectura Laravel, controladores, servicios, validacion, policies y estructura general.

2. **laravel-database-optimization**
`npx skills add https://github.com/asyrafhussin/agent-skills --skill laravel-database-optimization -y --copy`
`npx skills add https://github.com/jeffallan/claude-skills --skill laravel-specialist -y --copy`
   - Para migraciones, indices, relaciones, queries Eloquent y rendimiento de base de datos.

3. **laravel-filament**
`npx skills add https://github.com/ulpi-io/skills --skill laravel-filament -y --copy`
   - Para construir el CMS visual y el panel admin sobre Filament.

4. **spatie-security**
`npx skills add https://github.com/spatie/guidelines-skills --skill spatie-security -y --copy`
   - Para permisos, seguridad, hardening y buenas practicas de acceso.

5. **postgresql-optimization**
`npx skills add https://github.com/github/awesome-copilot --skill postgresql-optimization -y --copy`
   - Para decisiones especificas de PostgreSQL, indices, tipos y rendimiento.

6. **multi-stage-dockerfile**
`npx skills add https://github.com/github/awesome-copilot --skill multi-stage-dockerfile -y --copy`
   - Para el Dockerfile de produccion y separacion desarrollo/produccion.

7. **tailwindcss**
`npx skills add https://github.com/hairyf/skills --skill tailwindcss -y --copy`
`npx skills add https://github.com/josiahsiegel/claude-plugin-marketplace --skill tailwindcss-advanced-layouts -y --copy`
   - Para cambios controlados de estilos cuando el usuario los pida de forma explicita.

8. **framer-motion-animator**
`npx skills add https://github.com/patricio0312rev/skills --skill framer-motion-animator -y --copy`
   - Solo cuando el usuario pida tocar o mejorar animaciones del frontend.

9. **TRAE-code-review**
   - Para auditorias, revisiones tecnicas y deteccion de brechas contra requisitos.

10. **TRAE-debugger**
   - Para depuracion con evidencia cuando algo no funcione y no baste con inspeccion estatica.

## Reglas de uso de skills

- No usar skills de frontend si el usuario no ha pedido tocar frontend.
- Priorizar skills de backend, Docker, DB y CMS para este proyecto.
- Antes de usar cualquier skill, leer sus instrucciones.
- Si un skill contradice [PROJECT_RULES.md](file:///C:/Users/fernandocardona/Documents/ContentWorkPC26/FCT_MASTER_PLATAFORM/NewRadiochiWebsite26/.trae/PROJECT_RULES.md), prevalecen las reglas del proyecto.

## Skills prioritarios para el remake

Orden recomendado:

1. `TRAE-code-review`
2. `laravel-best-practices`
3. `laravel-database-optimization`
4. `postgresql-optimization`
5. `multi-stage-dockerfile`
6. `laravel-filament`
7. `spatie-security`

## Skills de uso condicionado

- `tailwindcss`: solo con permiso explicito del usuario.
- `tailwindcss-advanced-layouts`: solo con permiso explicito del usuario.
- `framer-motion-animator`: solo con permiso explicito del usuario.
- `TRAE-debugger`: cuando haya un fallo reproducible real.

## Observaciones de auditoria

- Las instalaciones externas se han fijado en [`.trae/skills`](file:///C:/Users/fernandocardona/Documents/ContentWorkPC26/FCT_MASTER_PLATAFORM/NewRadiochiWebsite26/.trae/skills) para este proyecto.
- El instalador generó carpetas auxiliares fuera de [`.trae`](file:///C:/Users/fernandocardona/Documents/ContentWorkPC26/FCT_MASTER_PLATAFORM/NewRadiochiWebsite26/.trae); ya fueron eliminadas para no ensuciar la raíz.
- El archivo [skills-lock.json](file:///C:/Users/fernandocardona/Documents/ContentWorkPC26/FCT_MASTER_PLATAFORM/NewRadiochiWebsite26/skills-lock.json) queda como registro reproducible de instalación.
- El instalador reportó evaluación upstream `High Risk` para `laravel-security` y `Critical Risk` para `laravel-specialist`; se han instalado porque estaban en la lista exacta pedida, pero conviene usarlas con revisión humana y no dar por válidas sus recomendaciones sin comprobarlas.

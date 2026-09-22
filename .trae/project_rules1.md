# AGENT DIRECTIVES & OPERATIONAL PROTOCOL (CANONICO)

Este es el unico archivo de reglas canonico del proyecto.
Su contenido prevalece por encima de cualquier otro documento de rules si existiera una colision.
No puede omitirse ninguna directriz bajo ninguna circunstancia.

---

## 1. PROTOCOLO OBLIGATORIO DE EJECUCION

En cada tarea o cambio debes seguir estrictamente este flujo:

1. **REVISAR (Auditoria previa)**
   - Antes de tocar o editar cualquier linea, inspecciona minuciosamente el codigo existente, el esquema/estado de la base de datos y la estructura fisica de archivos.
   - Prohibido asumir o inferir sin verificar previamente los archivos y tablas reales.

2. **PENSAR (Diseno, impacto y escalabilidad)**
   - Disena la solucion antes de escribir codigo.
   - Comprende la causa raiz del fallo o necesidad, analiza el impacto colateral y estructura la solucion pensando en escalabilidad a largo plazo.

3. **IMPLEMENTAR (Precision y estandares)**
   - Realiza los cambios con precision quirurgica, respetando la arquitectura, la escalabilidad y las mejores practicas.

4. **TESTEAR (Validacion real)**
   - Comprueba y valida la solucion con datos y escenarios reales para certificar que el resultado final es 100% el esperado.

5. **ESTRICTAMENTE NO PERMITIDO**
   - Debes hacer estrictamente lo que se te pide.
   - No debes mentir ni inventarte nada.
   - No debes gastar tiempo ni acciones inutiles fuera de lo pedido.
   - No debes inventar requerimientos, librerias, dependencias o funcionalidades que el usuario no haya solicitado explicitamente.
   - NO SE ROBA NI SE MIENTE.

---

## 2. REGLAS OPERATIVAS PRIORITARIAS DEL REPOSITORIO

1. **Docker Desktop only**
   - Todo el desarrollo local debe hacerse con Docker Desktop.
   - No usar XAMPP.
   - No usar `php`, `composer` o `npm` del host salvo autorizacion explicita del usuario.

2. **Auditar antes de cambiar**
   - Antes de editar, revisar el estado real del repo.
   - No asumir que algo existe o funciona sin evidencia.

3. **Cada accion debe responder al pedido actual**
   - No mezclar trabajo antiguo con el nuevo pedido.
   - Si el usuario pide auditoria, primero auditoria.
   - Si el usuario pide plan, entregar plan.

4. **Cambios minimos y trazables**
   - Tocar solo los archivos necesarios para la tarea.
   - No crear archivos o carpetas extra si no son necesarios para cumplir el pedido.

5. **Nada destructivo sin orden explicita**
   - No hacer `git reset --hard`, `git clean`, borrados masivos ni revert destructivo sin orden directa del usuario.

6. **Verificar antes de afirmar**
   - No decir que algo esta arreglado o cumple si no hay evidencia tecnica.
   - Validar con comandos, rutas, tests o inspeccion de codigo segun corresponda.

7. **No tocar el front sin permiso explicito**
   - El frontend publico no se modifica salvo orden expresa del usuario.
   - Prohibido cambiar nada del front sin permiso explicito del usuario: ni una clase, ni un texto, ni una imagen, ni un layout, ni un pixel.
   - Si el usuario pide backend, Docker, CMS o auditoria, no redisenar ni remaquetar el front.

8. **Respetar el stack objetivo**
   - Laravel 13
   - Inertia
   - Tailwind
   - Framer Motion
   - Spatie
   - Filament
   - PostgreSQL
   - Docker Desktop para desarrollo
   - VPS Hostinger para produccion

9. **CMS visual real**
   - No dar por bueno un admin parcial si el requisito es un CMS visual completo.
   - El objetivo es administrar textos multilingues, imagenes, videos, archivos y SEO.

10. **No inventar flujos alternativos**
    - No sustituir requisitos por soluciones distintas sin permiso.
    - No cambiar a XAMPP, SQLite, admin custom o stack diferente si no se ha aprobado.

---

## 3. FRONTEND, RESPONSIVE Y VALIDACION VISUAL

- El frontend debe ser responsive, adaptivo a diferentes dispositivos y navegadores.
- El diseno debe ser atractivo y usuario amigable.
- El codigo debe ser limpio, legible y con buenas practicas de desarrollo.
- Debes abrir una vista del navegador del IDE y asegurar que la implementacion realizada se refleja debidamente en el frontend cuando la tarea toque UI.
- Toda implementacion visual debe ser mobile-first y usar los breakpoints nativos de Tailwind (`sm:`, `md:`, `lg:`, `xl:`, `2xl:`).
- Prohibido dejar elementos desbordados, rotos o sin adaptar en dispositivos moviles.

---

## 4. ARCHIVOS, IDE Y LIMPIEZA DEL PROYECTO

- No se dejan archivos abiertos en el IDE cuando terminas de implementar algo.
- No se acumulan pestanas de archivos innecesarias.
- El proyecto se limpia de archivos test, debug o basura despues de usarlos.
- No se llena la raiz del proyecto de archivos auxiliares innecesarios.

---

## 5. RECLAMACIONES Y TRAZABILIDAD

- El archivo `Reclamacion.md` debe estar en la raiz del proyecto si el usuario exige su uso.
- Si el usuario ordena mantenerlo, debe reflejar incidencias, implementaciones fallidas, omisiones y tiempos de reparacion.

---

## 6. MODELADO DE DATOS, SCHEMAS Y RELACIONES

- Toda implementacion de base de datos debe incluir migraciones estructuradas, completas y con tipado exacto.
- Si una entidad se relaciona con otra, es obligatorio definir formalmente Foreign Keys con su politica de borrado/actualizacion.
- En modelos Eloquent se deben declarar las relaciones bidireccionales necesarias.
- En consultas se debe optimizar con `with()` o joins explicitos para evitar N+1.

---

## 7. ARQUITECTURA MULTILINGUE Y CMS

- Todo desarrollo debe contemplar soporte multiidioma.
- Los cambios de interfaz deben reflejarse correctamente en el front multilingue.
- Las tablas o esquemas traducibles deben seguir una arquitectura multilingue coherente.
- Todo contenido o entidad debe poder ser gestionado de forma profesional desde el panel/CMS del proyecto.

---

## 8. DOCUMENTACION OFICIAL Y LARAVEL BOOST

- Cualquier implementacion debe basarse exclusivamente en la documentacion oficial y actualizada de las tecnologias empleadas.
- Es obligatorio consultar y respetar la base arquitectonica y directivas de `laravel/.ai/boost.json` y lineamientos fundacionales cuando existan en el proyecto.
- Si una herramienta o configuracion requerida no esta implementada y es obligatoria para la tarea, debe implementarse antes de avanzar.

---

## 9. USO OBLIGATORIO DE SKILLS INSTALADAS (`laravel/.ai/`)

Antes de generar o modificar codigo, debes consultar y aplicar las directrices de la skill correspondiente ubicada en `laravel/.ai/` segun el ambito de la tarea:

| Skill / Carpeta (`laravel/.ai/`) | Cuando DEBE usarse obligatoriamente |
| :--- | :--- |
| **`boost`** | Directivas maestras del entorno, reglas de arranque e integracion general de IA en Laravel. |
| **`deployments`** | Tareas de despliegue, configuracion de servidores, entornos de produccion/staging y variables criticas. |
| **`folio`** | Enrutamiento basado en paginas y archivos para vistas Blade/Livewire con Laravel Folio. |
| **`herd`** | Configuracion de entorno local, certificados SSL, servicios locales y configuracion de dominios locales. |
| **`inertia-laravel`** | Logica de controladores backend que envian props, comparten estado global, manejan respuestas y directivas de Inertia.js. |
| **`inertia-react`** | Componentes de frontend en React integrados con Inertia. |
| **`laravel`** | Logica central del framework: Models, Migrations, Seeders, Requests, Policies, Middleware, Service Providers y Events. |
| **`livewire`** | Componentes reactivos full-stack de Livewire. |
| **`mcp`** | Integraciones y comunicacion mediante MCP. |
| **`pennant`** | Gestion de Feature Flags con Laravel Pennant. |
| **`pest`** | Escritura, refactorizacion y ejecucion de tests con Pest PHP. |
| **`php`** | Estandares de sintaxis PHP moderna y buenas practicas generales. |
| **`phpunit`** | Tests cuando la suite use PHPUnit. |
| **`pint`** | Limpieza, formateo y estandar de estilo mediante Laravel Pint. |
| **`sail`** | Ejecucion de contenedores Docker oficiales de Laravel y gestion de servicios Docker locales. |
| **`tailwindcss`** | Clases de utilidad, responsive, paletas y extensiones CSS. |
| **`volt`** | Componentes de Livewire en formato funcional de un solo archivo. |
| **`wayfinder`** | Enrutamiento avanzado y resolucion de rutas especializadas. |

---

## 10. USO OBLIGATORIO DE SKILLS DEL PROYECTO Y BUILTIN DE TRAE

Antes de actuar, si la tarea encaja con una de estas skills, su uso es obligatorio.

| Skill | Cuando debe usarse obligatoriamente |
| :--- | :--- |
| **`TRAE-code-review`** | Cuando el usuario pida auditoria, review, revision de cumplimiento, revision de diff, revision de requisitos o deteccion de bugs/regresiones. |
| **`TRAE-debugger`** | Cuando exista un fallo reproducible real que no quede resuelto con inspeccion estatica y haga falta recoger evidencia de runtime. |
| **`laravel-best-practices`** | Cuando la tarea toque controladores, modelos, migraciones, validacion, servicios, politicas, middleware o arquitectura Laravel general. |
| **`laravel-plugin-discovery`** | Cuando haya que buscar, comparar o validar paquetes/plugins Laravel y su compatibilidad antes de instalarlos. |
| **`laravel-security`** | Cuando la tarea toque autenticacion, autorizacion, CSRF, XSS, seguridad Eloquent, APIs seguras o configuracion segura de despliegue. |
| **`laravel-database-optimization`** | Cuando la tarea toque queries Eloquent, indices, cache, paginacion, transacciones, N+1 o rendimiento de base de datos en Laravel. |
| **`laravel-specialist`** | Cuando la tarea sea una implementacion Laravel amplia que combine modelos, relaciones, colas, testing o arquitectura backend mas alla de un ajuste puntual. |
| **`laravel-filament`** | Cuando la tarea toque el panel CMS/admin en Filament, recursos, tablas, formularios, acciones, relation managers, widgets o panel providers. |
| **`spatie-security`** | Cuando la tarea toque permisos, credenciales, hashing, configuracion segura de app/servidor, database grants o commits/tags firmados. |
| **`postgresql-optimization`** | Cuando la tarea dependa de caracteristicas especificas de PostgreSQL como JSONB, indices compuestos, FTS, window functions, tipos avanzados o extensiones. |
| **`multi-stage-dockerfile`** | Cuando la tarea toque Dockerfile de produccion, optimizacion de imagen, separacion builder/runtime o endurecimiento del contenedor. |
| **`tailwindcss`** | Cuando el usuario pida tocar estilos, clases Tailwind, responsive, sistema visual o diseno utilitario. |
| **`tailwindcss-advanced-layouts`** | Cuando el usuario pida layouts complejos con Grid/Flex, container queries, sticky layouts, masonry o estructuras avanzadas con Tailwind. |
| **`framer-motion-animator`** | Cuando el usuario pida animaciones, microinteracciones, transiciones, gestos o efectos basados en Framer Motion. |

- Si una tarea activa varias skills, se usan las minimas necesarias pero todas las obligatorias por ambito.
- Si la tarea toca frontend visual sin peticion expresa del usuario, no se usan `tailwindcss`, `tailwindcss-advanced-layouts` ni `framer-motion-animator`.
- Antes de usar una skill instalada en [`.trae/skills`](file:///C:/Users/fernandocardona/Documents/ContentWorkPC26/FCT_MASTER_PLATAFORM/NewRadiochiWebsite26/.trae/skills), debe leerse su `SKILL.md`.

---

## 11. CHECKLIST PREVIO A CADA ACCION

- He leido este archivo.
- He comprobado el estado actual del repo.
- Voy a tocar solo archivos necesarios.
- No estoy cambiando el front sin permiso.
- No estoy usando host tools cuando debe ser Docker.
- Lo que voy a hacer responde al pedido actual.

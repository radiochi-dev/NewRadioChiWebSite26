# Cambios Multiidioma CMS

## Estado de esta auditoria

- [x] ~~Detectar la causa raiz del front multidioma vacio.~~
- [x] ~~Verificar que el payload publico llegaba vacio desde backend y no por un problema visual de React/CSS.~~
- [x] ~~Restaurar el contenido publico desde el importador legacy oficial.~~
- [x] ~~Detectar el bug de navegacion que impedia volver a `ES` desde el selector de idioma.~~
- [x] ~~Definir el plan tecnico para rehacer el CMS multidioma con una UX de edicion por pagina y selector de idioma en cabecera.~~
- [x] ~~Restaurar y persistir el contenido editorial real dentro de las tablas CMS para que un entorno limpio no arranque vacio.~~
- [x] ~~Corregir el bootstrap persistente de super admins del backoffice (`fernandocardonatoro@gmail.com`, `radiochi.dev@gmail.com`).~~
- [x] ~~Implementar el primer avance UX del CMS multidioma: botones por locale en cabecera para abrir o crear la traduccion de pagina correspondiente.~~
- [x] ~~Eliminar el bloque visual de checklist tecnico del backoffice porque no aportaba nada operativo al editor.~~
- [x] ~~Reconducir `Paginas` a una superficie editorial real del onepage (`Home` + `Login`) en lugar de exponer CRUD tecnico de `pages/page_blocks`.~~
- [x] ~~Implementar la base del nuevo editor multidioma por pagina en backoffice con retorno al flujo editorial (`Home/Login` -> seccion -> formulario tipado).~~
- [ ] Completar la cobertura tipada de todos los formularios secundarios restantes del CMS onepage.
- [ ] Validar integralmente frontend publico + backoffice editorial con pruebas y navegador.

## Resumen ejecutivo

La incidencia actual del frontend no estaba en la maquetacion: el sitio publico recibia un payload practicamente vacio porque las tablas editoriales publicas estaban a cero (`pages`, `page_translations`, `page_blocks`, `settings`, `legal_documents`, `music_tracks`, `events`, `media_assets`, `partners`, `social_links`, `seo_meta`).

Ademas existia un bug funcional en el selector de idioma: `ES` resolvia a `/`, y la ruta raiz respetaba la cookie previa (`radiochi_locale`), por lo que si el usuario venia de `en` no podia volver de forma explicita a `es`.

## Lo que ya queda corregido en esta intervencion

### 1. Restauracion del contenido publico

Se ha ejecutado el importador oficial:

- `php artisan legacy:import-content`

Resultado restaurado:

- `pages`: 6
- `page_translations`: 36
- `page_blocks`: 14
- `page_block_translations`: 84
- `music_tracks`: 6
- `music_track_translations`: 36
- `events`: 3
- `media_assets`: 19
- `partners`: 5
- `social_links`: 8
- `legal_documents`: 3
- `legal_document_translations`: 18
- `settings`: 11
- `settings_translations`: 36
- `seo_meta`: 6

### 2. Correccion del bug de vuelta a `ES`

Se ha corregido el generador de URLs del `LanguageSwitcher` para que el locale explicito `es` apunte a `/es` y no a `/`.

Con esto desaparece el bucle funcional:

- usuario en `en`
- cookie `radiochi_locale=en`
- click en `ES`
- antes: enviaba a `/` y la cookie lo devolvia a `en`
- ahora: envia a `/es` y la seleccion explicita gana

### 3. Persistencia real del CMS y super admins

Se ha reforzado el bootstrap del proyecto para que un entorno limpio recupere el estado operativo base sin pasos manuales frágiles:

- `DatabaseSeeder` ahora ejecuta `CmsContentSeeder`
- `CmsContentSeeder` importa el contenido editorial en las tablas CMS reales
- `config/backoffice.php` define por defecto los dos super admins esperados
- `AuthenticateBackofficeUserAction` ahora hace `updateOrCreate` para alinear credenciales configuradas aunque el usuario ya exista

### 4. Primer avance real del editor multidioma

La edición multidioma ya tiene un primer punto de entrada usable:

- al abrir una pagina del backoffice aparecen botones por locale en cabecera
- si la traduccion existe, abre su formulario de edicion
- si no existe, abre el formulario de alta con el locale prefijado

Esto no es aun el editor page-centric final, pero ya evita navegar a ciegas por relation managers sin contexto de idioma.

## Auditoria del estado actual del CMS multidioma

### Lo que existe hoy

El backoffice actual ya tiene soporte tecnico de traducciones, pero esta distribuido en varios puntos y no resuelve la UX editorial que necesitamos:

1. Modulos con traducciones separadas:
   - `pages`
   - `page-blocks`
   - `settings`
   - `music-tracks`
   - `legal-documents`

2. Persistencia real por locale mediante `updateOrCreate`:
   - `page_id + locale`
   - `page_block_id + locale`
   - `setting_id + locale`
   - `music_track_id + locale`
   - `legal_document_id + locale`

3. Formularios de traduccion separados del formulario principal:
   - el editor abre una pagina
   - despues entra al relation manager de traducciones
   - despues abre o crea una traduccion concreta

### La brecha real

Eso funciona a nivel tecnico, pero no a nivel editorial:

- no existe una vista centrada en la pagina como unidad editorial principal;
- no existe un selector global de locale en la cabecera del editor de pagina;
- el editor no cambia de idioma dentro del mismo flujo;
- el contenido traducible sigue cayendo en formularios JSON separados y poco ergonomicos;
- no hay una separacion clara entre:
  - campos compartidos entre todos los idiomas,
  - campos localizados por idioma.

## Objetivo del nuevo sistema

Debemos pasar a un modelo editorial de este tipo:

1. El editor entra en una pagina concreta.
2. En la cabecera hay un selector de idioma.
3. Al cambiar el idioma:
   - cambian solo los campos traducibles,
   - se mantiene la misma pagina,
   - no cambian las imagenes ni assets globales.
4. Las imagenes y assets se editan una vez y son compartidos por todos los idiomas.
5. El contenido textual, labels, CTA, metadata y bloques traducibles se editan por locale.

## Contrato editorial objetivo

### Campos globales compartidos

Estos no deben duplicarse por idioma:

- imagenes
- logos
- videos
- fondos
- posters
- rutas de archivos
- enlaces tecnicos globales cuando no dependan del idioma
- orden/posicion de bloques
- flags de publicacion del asset

### Campos localizados

Estos deben cambiar por locale:

- titulos
- subtitulos
- descripciones
- CTA labels
- textos legales
- copy de hero
- copy de about
- copy de music
- labels de menu
- metadata SEO por locale
- contenido de bloques por locale

## Arquitectura funcional objetivo

### Vista principal: Paginas

Cada pagina tendra:

- cabecera con:
  - nombre de pagina
  - slug
  - estado
  - selector de idioma
  - acciones guardar/publicar
- secciones editables por bloques
- panel lateral de assets compartidos cuando aplique

### Modelo de persistencia

- `pages`: entidad raiz editorial
- `page_translations`: contenido localizado de pagina
- `page_blocks`: estructura y assets compartidos
- `page_block_translations`: copy localizado de cada bloque
- `settings_translations`: solo para settings realmente globales del sitio
- `seo_meta`: por locale

### Regla clave

La edicion debe ser **page-centric**, no **translation-centric**.

Es decir:

- el editor no debe navegar por relation managers separados para hacer una tarea normal;
- debe editar la pagina y alternar idioma desde la misma superficie;
- los formularios de traduccion separados quedaran como contrato interno o fallback tecnico, no como UX principal.

## Plan de implementacion minucioso

### Fase 0. Estabilizacion y contrato de datos

- congelar el contrato actual de `BuildPublicHomePayloadAction`
- inventariar para cada seccion:
  - que campos son globales
  - que campos son localizados
- documentar la matriz por entidad:
  - `Page`
  - `PageBlock`
  - `Setting`
  - `SeoMeta`
  - `LegalDocument`
  - `MusicTrack`

**DoD**

- matriz completa aprobada
- sin ambiguedades sobre que se traduce y que no

### Fase 1. Modelo UX de editor por pagina

- crear una nueva superficie de edicion por pagina
- introducir selector de locale en cabecera del editor
- mantener el record raiz constante y cargar:
  - traduccion de pagina activa
  - bloques de pagina
  - traduccion activa de cada bloque
- mostrar el locale actual siempre visible

**DoD**

- un editor puede abrir `home`
- puede alternar `es/en/ca/fr/it/de`
- ve cambiar el contenido textual sin salir de la pagina

### Fase 2. Separacion estricta entre campos globales y localizados

- mover las imagenes de hero a campos compartidos del bloque
- mantener textos del hero dentro de `page_block_translations`
- repetir el patron para:
  - `about`
  - `music`
  - `calendar`
  - `media`
  - `contact`

**DoD**

- cambiar una imagen no duplica assets por idioma
- cambiar un texto solo afecta al locale activo

### Fase 3. Editor de bloques por pagina

- listar bloques dentro de la pagina actual
- soportar:
  - ordenar bloques
  - activar/desactivar bloques
  - editar settings globales del bloque
  - editar contenido traducible del bloque segun locale activo

**DoD**

- `home` permite editar `hero_slide`
- `about` permite editar `about_step`
- `contact` permite editar `contact_marquee`

### Fase 4. SEO y legales por locale

- integrar SEO por locale en el mismo flujo de pagina
- integrar legales con selector de locale claro
- impedir que el editor mezcle locale de pagina con locale de SEO por error

**DoD**

- meta title, meta description, canonical logico y JSON-LD por locale accesibles desde la misma pagina

### Fase 5. Media compartida estilo biblioteca

- reforzar la libreria de media como repositorio unico
- seleccionar media existente en vez de duplicarla por locale
- guardar en la ficha del asset:
  - alt/caption si decidimos localizarlos mas adelante
  - pero mantener el binario y la ruta como compartidos

**DoD**

- una misma imagen se reutiliza en varios locales sin copias duplicadas

### Fase 6. Validacion y reglas anti-regresion

- pruebas feature de:
  - cambio de locale en editor
  - guardado de traduccion de pagina
  - guardado de traduccion de bloque
  - no contaminacion entre locales
  - no mutacion de assets compartidos
- pruebas del frontend publico:
  - `/es`
  - `/en`
  - resto de locales
  - fallback a `es`
  - selector de idioma

**DoD**

- cada locale renderiza contenido valido
- `ES` siempre vuelve a funcionar de forma explicita
- no se vacia el payload publico por errores editoriales

## Cambios de implementacion propuestos

### Backend

- crear un payload page-centric para backoffice editorial
- añadir resolucion de locale activo por query param o segmento controlado
- exponer en un mismo payload:
  - datos base de pagina
  - traduccion activa
  - bloques
  - traducciones activas de bloques
  - SEO activo
  - lista de locales disponibles

### Frontend backoffice

- nueva pantalla de edicion de pagina con:
  - selector de locale en cabecera
  - bloques editables
  - panel de media compartida
  - secciones localizadas visualmente marcadas
- mantener los relation managers actuales como fallback tecnico durante migracion

### Frontend publico

- conservar el contrato del payload publico
- no tocar maquetacion visual
- solo reforzar:
  - estabilidad del locale switching
  - fallback de locale
  - validacion de contenido no vacio

## Riesgos reales

1. Mezclar campos globales y localizados y sobrescribir assets compartidos.
2. Romper el contrato actual de `BuildPublicHomePayloadAction`.
3. Duplicar contenido entre `settings` y `pages`.
4. Mantener demasiado tiempo dos UX editoriales en paralelo.
5. Permitir guardar locales incompletos sin visibilidad clara.

## Criterios de cierre

- el frontend publico vuelve a renderizar contenido real en todos los locales soportados
- el selector de idioma funciona ida y vuelta
- el backoffice permite editar una pagina desde una unica vista con selector de idioma
- las imagenes siguen siendo compartidas entre locales
- los textos cambian segun locale sin contaminar otros idiomas
- el payload publico se mantiene estable
- todo queda cubierto por tests de regresion

## Siguiente paso correcto

El siguiente paso correcto ya no es seguir improvisando relation managers sueltos.

Hay que ejecutar en este orden:

1. cerrar la estabilizacion del frontend multidioma con tests;
2. diseñar el payload page-centric del backoffice;
3. implementar el selector de idioma en cabecera del editor de pagina;
4. migrar `home` como primera pagina piloto;
5. despues extender el patron al resto de paginas.

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
- [x] ~~Reconducir `Paginas` a una superficie editorial real page-centric en lugar de exponer CRUD tecnico de `pages/page_blocks`.~~
- [x] ~~Implementar la base del nuevo editor multidioma por pagina en backoffice con retorno al flujo editorial (`Paginas` -> `Pagina` -> `Componente` -> formulario tipado).~~
- [x] ~~Consolidar `Redes sociales` como fuente comun unica para contacto, footer y login, sin duplicados editoriales por localizacion.~~
- [x] ~~Cerrar el patron de editor multilingue integrado en `settings` y `music-tracks`, guardando el locale activo desde el formulario principal y manteniendo imagenes compartidas entre idiomas.~~
- [x] ~~Reconstruir la superficie editorial page-centric para que `home` deje de listar slides como filas y pase a editarlos en tabs dentro del mismo contenedor, con alta y baja de slides desde la propia pagina.~~
- [x] ~~Eliminar globalmente del backoffice el bloque y la tarjeta resumen `Modo EDIT`, porque no aportaban valor operativo en los formularios.~~
- [x] ~~Corregir globalmente el color de iconos y controles nativos de campos compartidos del backoffice para que se lean bien sobre la UI oscura.~~
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
- `social_links`: 4
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

### 5. Ajuste visual puntual del shell del backoffice

Se ha corregido el shell visual del dashboard/admin en los puntos pedidos durante la auditoria de maquetacion:

- sidebar y topbar con cabeceras a la misma altura para evitar descuadres de la linea horizontal;
- eliminacion del logo diminuto del topbar;
- uso del logo real en la cabecera del sidebar con presencia visible;
- reordenacion del menu lateral en el orden operativo solicitado;
- descripciones de items ocultas por defecto y visibles solo en item activo o hover.
- ocultacion de la barra de scroll del sidebar y estilizado del scroll del contenido con track transparente acorde al layout oscuro del backoffice.

### 6. Correccion del flujo editorial page-centric

Se ha corregido la mezcla incorrecta entre paginas y bloques tecnicos:

- `Bloques de pagina` deja de exponerse como punto de entrada principal en sidebar y accesos rapidos;
- el acceso directo a `/backoffice/page-blocks` redirige a `Paginas`;
- `Paginas` lista ahora las paginas reales del CMS;
- al abrir una pagina solo se muestran sus componentes propios;
- al abrir un componente se entra en su editor tipado independiente;
- al guardar una traduccion de pagina o bloque se retorna a la pagina propietaria, no al CRUD tecnico de bloques.
- las URLs editoriales de pagina dejan de exponer IDs numericos y pasan a usar `slug` (`/backoffice/pages/home/edit?locale=es`);
- la apertura de componentes usa una ruta descriptiva por pagina y clave de componente (`/backoffice/pages/home/components/hero-slide-01/edit?locale=es`);
- se elimina el bloque visual `Resumen de validacion servidor` de los formularios de traduccion porque no aporta valor operativo al editor.
- los campos reales de imagen del backoffice dejan de mostrarse como inputs de ruta y pasan a usar preview con miniatura, hover con blur y accion de limpieza;
- esos mismos campos exponen botones para subir imagen nueva o seleccionar una existente desde la galeria de assets del backoffice;
- el backoffice incorpora un endpoint de upload de imagen que registra automaticamente el asset en `media_assets` y lo deja disponible en la galeria reutilizable.
- el modulo `Assets` deja de renderizarse como tabla tecnica y pasa a una galeria visual con grid de tarjetas;
- el filtrado de `Assets` se limita a nombre del archivo, pagina donde esta asignado y formato (`Imagenes`, `Videos`, `PDF`);
- cada tarjeta de asset muestra preview, nombre, tamano, formato, dimensiones, fecha de actualizacion y paginas donde esta siendo usada dentro del CMS.
- `Redes sociales` pasa a una fuente comun unica: se elimina la separacion editorial `contact/footer` y cada red se crea una sola vez para reutilizarse en todas las superficies compartidas;
- la lectura publica y el footer de login consumen ahora la misma coleccion `global` de redes sociales;
- una migracion de consolidacion deduplica los registros legacy y deja un unico registro por plataforma en `social_links`.
- la shell del backoffice deja de repetir el contexto actual en topbar, breadcrumb y cabecera principal: el titulo activo ya no se duplica y `Settings` queda anclado al bloque inferior del sidebar;
- el sidebar se reordena para eliminar `Agenda`, mover `Eventos` a `Editorial / contenido` y recolocar `Sponsors` y `Redes sociales` dentro de `Marketing`.
- los formularios compartidos del backoffice dejan de renderizar el bloque tecnico `Resumen de validacion servidor` en todas las pantallas;
- los modulos traducibles (`settings`, `tracks`, `documentos legales`, `bloques`) pasan a gobernar los idiomas desde acciones de locale en la cabecera del contenedor en lugar de exponer la tarjeta heredada de `Relaciones y traducciones`.
- el backoffice incorpora un editor WYSIWYG real basado en Tiptap para los campos de contenido HTML/largo que lo necesitan (`legal-documents.content`, `events.body` y `newsletter-campaigns.html_body`);
- el contenido legal importado desde `resources/js/legacy/i18n/*/terms-policy-cookies.json` queda ahora alineado con un editor rico funcional en backoffice y con el render HTML que ya usa el frontend publico.
- `legal-documents` deja de separar artificialmente metadata y traduccion: el editor base ya carga y guarda el contenido del locale activo dentro del mismo contenedor;
- el menu de idiomas para traducciones se renderiza dentro del contenedor del formulario (`form.localeActions`) y se usa tambien en documentos legales para alternar locale sin salir de la superficie de edicion.
- `seo-metas` expone ahora el mismo patron de conmutacion por idioma dentro del contenedor del formulario, enlazando cada locale del mismo recurso SEO y pre-rellenando altas nuevas cuando aun no existe el registro;
- la gestion SEO queda alineada con el payload publico por locale: un registro por `entity_type + entity_id + locale`, con metadatos y canonical especificos por idioma.
- cuando `seo-metas` ya esta contextualizado a una entidad concreta, la edicion oculta la identidad tecnica y deja una superficie de dos niveles: tabs de idioma en el primer contenedor y un unico contenedor de `Metadata SEO` para el locale activo;
- al cambiar de tab en `seo-metas`, el formulario ya no muestra bloques tecnicos duplicados y carga directamente los campos SEO del idioma correspondiente para el mismo recurso.
- el indice `/backoffice/seo-metas` deja de renderizar una tabla CRUD por locale y pasa a comportarse como editor por recurso: primer contenedor con tabs de idioma, segundo contenedor con el formulario del locale activo;
- en ese flujo de indice se elimina el bloque de filtros para `seo-metas`, porque deja de aportar valor cuando la pantalla ya no navega por filas sino por el recurso SEO seleccionado.
- `settings` translatables dejan de depender del flujo separado de traducciones para el uso principal: el formulario base ya cambia el segundo contenedor segun el idioma activo, valida `locale` desde query y persiste `settings_translations.value` desde el mismo editor.
- `music-tracks` deja de separar la traduccion en una pantalla secundaria para el flujo principal: el formulario base ya guarda el locale activo junto al contenido editorial del track y conserva `label_image_path` / `cover_image_path` como assets compartidos entre idiomas.
- `pages/home/edit` deja de mostrar el bloque sobrante `Modo EDIT` y sustituye la lista lineal de hero por un editor inline con tabs dentro del mismo contenedor: `Contenido base`, `Slide 01`, `Slide 02`, etc.
- desde esa misma superficie page-centric ya existe `Nuevo slide` y cada tab de slide expone su propia accion `Eliminar`, manteniendo el idioma activo sobre el mismo contenedor sin abrir una pantalla separada por item.
- el redirect de guardado de traducciones de pagina/bloque ya conserva `focus` para devolver el editor inline a la pestaña concreta que se estaba editando.
- los formularios del backoffice dejan de mostrar tanto el panel `Modo EDIT` como la tarjeta-resumen `Modo`, reduciendo ruido visual en todas las superficies CRUD.
- los componentes compartidos de campos (`Input`, `Textarea`, `Select`, `RichTextField`, `ImageField`, toggles y checkboxes de tabla) quedan ajustados para tema oscuro: iconos de fecha/hora aclarados, checks con `accent-color` consistente y SVG de controles con contraste suficiente.
- validacion reciente cerrada en runtime Docker del proyecto:
  - `BackofficePhase6CrudPersistenceTest`: 13 tests OK, 282 assertions.
  - `BackofficePhase7RichContentPersistenceTest`: 10 tests OK, 176 assertions.
  - `npm run build`: OK, con warnings ya conocidos de assets runtime/chunk size sin error bloqueante.

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

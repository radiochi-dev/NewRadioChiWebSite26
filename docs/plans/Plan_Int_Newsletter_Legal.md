# Plan Integracion Newsletter Legal

## Estado

- [x] Auditoria inicial del estado real del proyecto completada.
- [x] Fase 1. Rediseño de datos y migraciones legales.
- [x] Fase 2. Backend publico de suscripcion, confirmacion y baja.
- [x] Fase 3. Mailables, cabeceras y plantillas base.
- [x] Fase 4. Integracion frontend en `Contact` / footer.
- [x] Fase 5. Integracion operativa con backoffice de suscriptores.
- [x] Fase 6. Cobertura legal, textos y trazabilidad RGPD/LSSI.
- [x] Fase 7. Tests, endurecimiento, QA y despliegue.

## Objetivo

Implementar un flujo integral, legal y auditable de newsletter sobre la base existente del proyecto:

- captacion publica con consentimiento expreso;
- double opt-in;
- baja directa y one-click unsubscribe;
- persistencia probatoria RGPD/LSSI;
- integracion real con el modulo existente `newsletter-subscribers`;
- compatibilidad con envios de campanas ya existentes;
- mantenimiento de la UX y shell React/Inertia actuales.

## Base documental oficial usada para el plan

- Laravel 13 Mailables y headers: https://laravel.com/framework/docs/13.x/mail
- Laravel signed URLs: https://laravel.com/docs/12.x/urls#signed-urls
- RFC 8058 one-click unsubscribe: https://www.rfc-editor.org/info/rfc8058/
- Codigo de Conducta AEPD/AUTOCONTROL para tratamiento publicitario de datos: https://www.aepd.es/documento/codigo-conducta-autocontrol.pdf

## Auditoria del estado actual

### 1. Datos y modelo actuales

Estado actual detectado:

- La tabla `newsletter_subscribers` ya existe, pero es insuficiente para cumplimiento legal y trazabilidad.
- Hoy solo contiene:
  - `email`
  - `name`
  - `is_active`
  - `subscribed_at`
  - `unsubscribed_at`
  - timestamps
- `is_active` nace con `default(true)`, lo cual contradice el double opt-in solicitado.

Referencias:

- [migration actual](file:///c:/Users/fernandocardona/Documents/ContentWorkPC26/FCT_MASTER_PLATAFORM/NewRadiochiWebsite26/database/migrations/2026_03_29_200000_create_newsletter_tables.php)
- [modelo actual](file:///c:/Users/fernandocardona/Documents/ContentWorkPC26/FCT_MASTER_PLATAFORM/NewRadiochiWebsite26/app/Models/NewsletterSubscriber.php)

Huecos exactos:

- falta `confirmation_token`;
- falta `unsubscribe_token`;
- falta `ip_address`;
- falta `user_agent`;
- falta `consent_text_version`;
- falta normalizacion fuerte a lowercase;
- falta indice explicito para tokens y email;
- falta diseño de reactivacion segura de bajas previas.

### 2. Backend publico

Estado actual detectado:

- No existen rutas publicas de alta, confirmacion ni baja de newsletter en `routes/web.php` o `routes/api.php`.
- No existe `NewsletterSubscriberController`.
- No existe throttling especifico para captacion newsletter.
- No existe validacion publica con `privacy_accepted`.
- No existe honeypot.

Referencias:

- [routes/web.php](file:///c:/Users/fernandocardona/Documents/ContentWorkPC26/FCT_MASTER_PLATAFORM/NewRadiochiWebsite26/routes/web.php)
- [routes/api.php](file:///c:/Users/fernandocardona/Documents/ContentWorkPC26/FCT_MASTER_PLATAFORM/NewRadiochiWebsite26/routes/api.php)

### 3. Mailables y cumplimiento de correo

Estado actual detectado:

- El proyecto ya envia campanas, pero las envia con `Mail::html(...)` directo desde el job.
- No existen Mailables dedicados para newsletter.
- No existen cabeceras `List-Unsubscribe` ni `List-Unsubscribe-Post`.
- No existe plantilla base comun para correos newsletter.
- No existe link de baja inyectado de forma estructural en todos los envios.

Referencias:

- [SendNewsletterCampaignJob.php](file:///c:/Users/fernandocardona/Documents/ContentWorkPC26/FCT_MASTER_PLATAFORM/NewRadiochiWebsite26/app/Jobs/SendNewsletterCampaignJob.php)
- [config/mail.php](file:///c:/Users/fernandocardona/Documents/ContentWorkPC26/FCT_MASTER_PLATAFORM/NewRadiochiWebsite26/config/mail.php)
- [config/queue.php](file:///c:/Users/fernandocardona/Documents/ContentWorkPC26/FCT_MASTER_PLATAFORM/NewRadiochiWebsite26/config/queue.php)

### 4. Frontend publico

Estado actual detectado:

- La pantalla publica en uso real es `resources/js/Pages/Home.jsx`, no `ContactSection.jsx`.
- La seccion `contact` actual solo muestra iconos sociales, sponsors y marquee.
- No existe formulario de newsletter en la superficie publica actual.
- `ContactSection.jsx` existe como componente alternativo, pero no gobierna el flujo principal del home legacy.

Referencias:

- [Home.jsx](file:///c:/Users/fernandocardona/Documents/ContentWorkPC26/FCT_MASTER_PLATAFORM/NewRadiochiWebsite26/resources/js/Pages/Home.jsx)
- [ContactSection.jsx](file:///c:/Users/fernandocardona/Documents/ContentWorkPC26/FCT_MASTER_PLATAFORM/NewRadiochiWebsite26/resources/js/Components/sections/ContactSection.jsx)

Conclusion tecnica:

- la integracion debe hacerse sobre `Home.jsx` dentro de la seccion `contact` o en su footer real, no sobre el componente alternativo aislado.

### 5. Backoffice existente

Estado actual detectado:

- Ya existe modulo real `newsletter-subscribers` en el shell Inertia.
- Ya existe listado conectado a DB, filtros por `is_active`, formulario y guardado.
- Ya existe helper de fechas de negocio `PrepareNewsletterSubscriberData`.
- Hoy el backoffice permite altas/bajas manuales simples, pero no:
  - guardar trazas RGPD;
  - reactivar con tokens;
  - borrar definitivamente por derecho al olvido mediante accion dedicada;
  - mostrar campos legales nuevos;
  - acciones operativas especificas de baja/reactivacion.

Referencias:

- [Phase6ModuleCatalog.php](file:///c:/Users/fernandocardona/Documents/ContentWorkPC26/FCT_MASTER_PLATAFORM/NewRadiochiWebsite26/app/Support/Backoffice/Phase6ModuleCatalog.php)
- [BuildBackofficePhase6CrudPayloadAction.php](file:///c:/Users/fernandocardona/Documents/ContentWorkPC26/FCT_MASTER_PLATAFORM/NewRadiochiWebsite26/app/Actions/Backoffice/BuildBackofficePhase6CrudPayloadAction.php)
- [SaveBackofficePhase6ModuleAction.php](file:///c:/Users/fernandocardona/Documents/ContentWorkPC26/FCT_MASTER_PLATAFORM/NewRadiochiWebsite26/app/Actions/Backoffice/SaveBackofficePhase6ModuleAction.php)
- [PreviewController.php](file:///c:/Users/fernandocardona/Documents/ContentWorkPC26/FCT_MASTER_PLATAFORM/NewRadiochiWebsite26/app/Http/Controllers/Backoffice/PreviewController.php)

Limitacion actual relevante:

- el shell `PreviewController` no tiene todavia un flujo generico de borrado definitivo; solo soporta `draft` y algunas `actions` especificas.

### 6. Automatizacion y vistas SQL

Estado actual detectado:

- Existe la vista `automation_newsletter_subscribers`.
- Hoy solo expone `id`, `email`, `name`, `subscribed_at` para activos.

Referencia:

- [create_automation_access_views.php](file:///c:/Users/fernandocardona/Documents/ContentWorkPC26/FCT_MASTER_PLATAFORM/NewRadiochiWebsite26/database/migrations/2026_09_22_000000_create_automation_access_views.php)

Implicacion:

- si automatizaciones futuras necesitan estado de consentimiento o fecha de baja, la vista debera revisarse en una fase controlada.

### 7. Riesgo legal detectado antes de implementar

Hallazgo critico:

- La politica de privacidad actual del front dice expresamente que el sitio no tiene formularios de suscripcion, newsletters ni listas de correo.

Referencias:

- [terms-policy-cookies ES](file:///c:/Users/fernandocardona/Documents/ContentWorkPC26/FCT_MASTER_PLATAFORM/NewRadiochiWebsite26/resources/js/legacy/i18n/es/terms-policy-cookies.json)
- equivalentes en `en`, `ca`, `fr`, `it`, `de`.

Consecuencia:

- no se puede desplegar esta integracion sin actualizar textos legales, transparencia informativa y pie de los correos.

## Gap analysis contra el alcance solicitado

### Ya existe

- CRUD backoffice de suscriptores.
- Campanas y logs de newsletter.
- Cola `newsletter`.
- Query real contra PostgreSQL.
- Politicas de acceso backoffice.

### Falta

- modelo de consentimiento legalmente trazable;
- captacion publica;
- double opt-in;
- confirmacion segura;
- baja one-click RFC 8058;
- Mailable de confirmacion;
- plantilla base de newsletter;
- cabeceras `List-Unsubscribe`;
- acciones operativas de baja/reactivacion/borrado RGPD en backoffice;
- actualizacion legal de politicas;
- test suite de extremo a extremo del flujo.

## Cuestiones criticas a resolver y su tratamiento en el plan

### Cuestion critica 1. La base `newsletter_subscribers` no cumple aun el nivel legal requerido

Problema confirmado:

- faltan `confirmation_token`, `unsubscribe_token`, `ip_address`, `user_agent`, `consent_text_version`;
- `is_active` nace con `default(true)`, lo que invalida el double opt-in como comportamiento por defecto;
- falta estrategia de backfill y saneo de legacy.

Decision de producto ya tomada:

- a fecha de este plan, la tabla de suscriptores esta vacia, por lo que no existe bloqueo real por legacy ni por colisiones historicas;
- el email introducido en el front se normalizara automaticamente a minusculas;
- el backend volvera a normalizar a minusculas antes de validar y persistir;
- si se detecta un email duplicado, no se insertara un nuevo suscriptor y se respondera con mensaje UI localizado al idioma activo indicando que el suscriptor ya existe.

Solucion acordada en el plan:

- resolverlo en `Fase 1` con migracion de alter table, indices, normalizacion a lowercase, tokens, traza legal y cambio de default a `false`;
- ajustar `NewsletterSubscriber`, validaciones y tests para que el nuevo contrato de datos sea la unica verdad del sistema.

Dependencias:

- comprobacion rapida previa de que la tabla sigue vacia antes de ejecutar migracion en entornos compartidos;
- aplicar la misma regla de lowercase en frontend y backend para evitar desalineacion funcional.

Criterio de cierre:

- la tabla queda lista para consentimiento trazable, reactivacion segura y baja one-click sin apoyarse en campos provisionales o logica ad hoc.

### Cuestion critica 2. No existe flujo publico real de alta, confirmacion y baja

Problema confirmado:

- no hay rutas publicas;
- no hay controlador publico;
- no hay store con consentimiento expreso;
- no hay confirmacion double opt-in;
- no hay baja publica one-click.

Solucion acordada en el plan:

- resolverlo en `Fase 2` con `NewsletterSubscriberController`, rutas publicas, throttling, honeypot, validacion estricta, confirmacion por token + signed URL y baja idempotente por `GET` humano + `POST` RFC 8058.

Dependencias:

- `Fase 1` cerrada para disponer del esquema correcto;
- decision sobre expiracion y firma de URLs.

Criterio de cierre:

- cualquier alta publica queda inactiva hasta confirmacion;
- cualquier baja puede ejecutarse de forma segura, directa e idempotente desde correo o pagina publica.

### Cuestion critica 3. Las campanas salen hoy por `Mail::html(...)` sin estructura legal reutilizable

Problema confirmado:

- no hay `Mailable` para newsletter;
- no hay headers `List-Unsubscribe`;
- no hay `List-Unsubscribe-Post`;
- no hay footer legal estructurado y comun;
- el job de envio actual no garantiza cumplimiento uniforme.

Solucion acordada en el plan:

- resolverlo en `Fase 3` creando `NewsletterDoubleOptIn`, una plantilla base de newsletter y una capa comun reutilizable por correos de confirmacion y por campanas;
- refactorizar `SendNewsletterCampaignJob` para dejar de depender de `Mail::html(...)` plano.

Dependencias:

- `Fase 1` y `Fase 2` cerradas para disponer de `unsubscribe_token`, rutas y trazabilidad.

Criterio de cierre:

- todos los correos newsletter salen con footer legal, enlace visible de baja y cabeceras compatibles con `List-Unsubscribe` y `List-Unsubscribe-Post`.

### Cuestion critica 4. El backoffice de suscriptores existe, pero aun no tiene operaciones robustas RGPD

Problema confirmado:

- hoy el modulo lista y edita, pero no cubre bien baja manual, reactivacion manual y borrado definitivo por derecho al olvido;
- tampoco expone toda la trazabilidad legal necesaria.

Solucion acordada en el plan:

- resolverlo en `Fase 5` ampliando columnas, badges, acciones operativas y lectura de datos legales;
- introducir una accion protegida especifica para borrado RGPD, ya que el shell actual no trae delete generico listo para este caso.

Dependencias:

- `Fase 1` cerrada para disponer de campos legales;
- coordinacion con `PreviewController`, `Phase6ModuleCatalog` y payloads del shell Inertia.

Criterio de cierre:

- el modulo `newsletter-subscribers` permite consultar estado real, dar de baja, reactivar y borrar definitivamente con permisos y trazabilidad adecuados.

### Cuestion critica 5. La politica de privacidad contradice la futura existencia de la newsletter

Problema confirmado:

- el sitio afirma hoy que no existen newsletters ni listas de correo;
- desplegar la funcionalidad sin cambiar ese texto generaria incoherencia legal y operativa.

Solucion acordada en el plan:

- resolverlo en `Fase 6` actualizando politica de privacidad, copy del formulario y textos relacionados en todos los idiomas soportados;
- bloquear despliegue productivo de la captacion hasta que esa cobertura legal quede alineada con el tratamiento real.

Dependencias:

- definicion final del texto de consentimiento y del `consent_text_version`;
- validacion de enlaces y pie legal de correos.

Criterio de cierre:

- el formulario, la politica, el footer de correos y el backoffice describen el mismo tratamiento de datos sin contradicciones.

### Regla de gobierno del plan

Estas cinco cuestiones se consideran bloqueantes de despliegue.

La integracion no podra marcarse como finalizada mientras cualquiera de ellas siga abierta, aunque existan piezas parciales ya funcionando en desarrollo.

## Decision tecnica propuesta

### Decision 1. Mantener una sola fuente de verdad

La fuente unica de suscriptores seguira siendo `newsletter_subscribers`.

No se creara una tabla paralela ni un flujo separado para captacion publica.

### Decision 2. Confirmacion con doble capa de seguridad

Para confirmacion se propone:

- persistir `confirmation_token` en DB;
- generar enlace mediante `URL::temporarySignedRoute(...)`;
- validar tanto firma de Laravel como token persistido;
- invalidar `confirmation_token` tras activacion.

Motivo:

- el token persistido permite revocacion y reemision controlada;
- la signed route evita manipulacion de parametros;
- el enlace puede caducar sin depender solo de la tabla.

### Decision 3. Baja con token unico persistido y endpoint POST para RFC 8058

Para unsubscribe se propone:

- `unsubscribe_token` persistido y unico;
- `GET /newsletter/unsubscribe/{token}` para experiencia humana y pagina Inertia;
- `POST /newsletter/unsubscribe/{token}` para one-click RFC 8058;
- el token no se invalida al primer uso; se mantiene estable para futuras campanas y para idempotencia.

Motivo:

- RFC 8058 requiere endpoint HTTPS apto para POST one-click;
- mantener token estable simplifica footer de correos futuros y evita reenviar nuevos enlaces tras cada campana;
- la accion de baja debe ser idempotente y segura.

### Decision 4. IP anonimizada

No guardar IP plena si no es estrictamente necesaria.

Se propone:

- anonimizar IPv4 y IPv6 antes de persistir;
- documentar el metodo;
- mantener solo el valor necesario para auditoria probatoria.

### Decision 5. Integracion frontend en `Home.jsx`

El formulario se integrara en la seccion `contact` real del home legacy.

No se duplicara en un componente alternativo no usado.

## Fases de implementacion

### Fase 1. Rediseño de datos y migracion

- [x] Crear nueva migracion de alter table para `newsletter_subscribers`.
- [x] Añadir campos legales:
  - [x] `email` normalizado e indexado
  - [x] `name`
  - [x] `is_active default false`
  - [x] `subscribed_at`
  - [x] `unsubscribed_at`
  - [x] `confirmation_token`
  - [x] `unsubscribe_token`
  - [x] `ip_address`
  - [x] `user_agent`
  - [x] `consent_text_version`
- [x] Confirmar antes de migrar que `newsletter_subscribers` sigue vacia en el entorno objetivo.
- [x] Definir normalizacion obligatoria de email a lowercase en backend antes de validar y persistir.
- [x] Mantener comprobacion defensiva de duplicados por `lower(email)` aunque hoy la tabla este vacia.
- [x] Ajustar modelo `NewsletterSubscriber`.
- [x] Ajustar factories/tests que hoy asumen esquema corto.

Ejecucion real completada en esta fase:

- se verifico que `newsletter_subscribers` tenia `0` registros en el entorno actual antes de cerrar la migracion;
- se creo la migracion `2026_09_27_130000_harden_newsletter_subscribers_for_legal_compliance.php`;
- se endurecio `NewsletterSubscriber` para lowercase, `unsubscribe_token`, `consent_text_version` y `is_active = false` por defecto a nivel de dominio;
- se ajusto `PrepareNewsletterSubscriberData` para que `subscribed_at` sea coherente con el nuevo contrato de activacion;
- se ajusto `BackofficePreviewDraftRequest` para normalizar `email` a lowercase antes de validar;
- se actualizo cobertura de tests de esquema y de operativa backoffice para el nuevo contrato de datos.

Riesgos:

- colision de emails si en algun entorno aparecieran duplicados con distinto case;
- que el front fuerce lowercase pero el backend no lo haga igual;
- introducir una regla de unicidad sin respuesta UX clara para el usuario.

Solucion propuesta:

- dar por no bloqueante el frente legacy porque hoy no hay suscriptores;
- mantener una comprobacion previa de seguridad por si otro entorno no estuviera vacio;
- blindar la normalizacion a lowercase en backend aunque el front ya la aplique;
- definir desde esta fase la respuesta funcional para duplicados: no crear registro nuevo y devolver estado manejable por UI localizada.

### Fase 2. Backend publico de suscripcion y baja

- [x] Crear `NewsletterSubscriberController`.
- [x] Añadir rutas publicas:
  - [x] `POST /newsletter/subscribe`
  - [x] `GET /newsletter/confirm/{token}`
  - [x] `GET /newsletter/unsubscribe/{token}`
  - [x] `POST /newsletter/unsubscribe/{token}`
- [x] Proteger `subscribe` con throttling `5,1`.
- [x] Validar `email:rfc,dns|required|max:255`.
- [x] Validar `privacy_accepted:accepted`.
- [x] Validar honeypot.
- [x] Normalizar el email recibido a lowercase antes de cualquier consulta o validacion de unicidad.
- [x] Reactivar suscriptor dado de baja cuando proceda con nuevo `confirmation_token`.
- [x] Guardar fecha, IP anonimizada, user-agent y version legal.
- [x] Confirmar suscripcion con activacion real solo tras doble opt-in.
- [x] Implementar baja idempotente.
- [x] Renderizar respuestas limpias via Inertia.
- [x] Resolver el caso de email ya existente sin insertar duplicados y devolviendo respuesta interpretable por el front para modal localizado.

Ejecucion real completada en esta fase:

- se creo `NewsletterSubscriberController` con `store`, `confirm` y `unsubscribe`;
- se registraron las rutas publicas de alta, confirmacion y baja, con `throttle:5,1` en el alta;
- se implemento validacion backend para `email`, `privacy_accepted` y honeypot;
- el alta pública ya normaliza el email a lowercase, reactiva registros dados de baja en modo pendiente y renueva `confirmation_token`;
- la confirmacion exige firma valida de URL y activa realmente al suscriptor solo cuando el token sigue vigente;
- la baja funciona por `GET` y `POST` sobre el mismo endpoint para cubrir flujo humano y one-click de forma idempotente;
- se creo la superficie Inertia `Newsletter/Status` para mensajes publicos de confirmacion y baja sin tocar el `Home` actual;
- se añadieron mensajes localizados `es/en/ca/fr/it/de` y cobertura de tests para alta, confirmacion valida, confirmacion invalida y baja one-click.

Riesgos:

- firmar mal las URLs en entornos tras proxy/https;
- permitir enumeracion o abuso por respuestas demasiado reveladoras;
- romper UX con redirects inconsistentes entre `GET` y `POST`.

Solucion propuesta:

- respuestas neutrales para altas repetidas;
- signed routes solo donde aportan valor;
- token estable para baja y signed route temporal para confirmacion;
- middleware `signed` o validacion explicita segun necesidad de UX.

### Fase 3. Mailables y correo

- [x] Crear `NewsletterDoubleOptIn`.
- [x] Crear layout Blade base para newsletter.
- [x] Incluir footer obligatorio:
  - [x] responsable del tratamiento
  - [x] finalidad
  - [x] politica de privacidad
  - [x] enlace de baja directo
- [x] Configurar headers:
  - [x] `List-Unsubscribe`
  - [x] `List-Unsubscribe-Post: List-Unsubscribe=One-Click`
- [x] Encolar envio de confirmacion.
- [x] Refactorizar `SendNewsletterCampaignJob` para usar Mailable o builder comun compatible con footer legal y baja.

Ejecucion real completada en esta fase:

- se creo `ResolveNewsletterMailContextAction` para resolver responsable, email de contacto, politica de privacidad y baja sin hardcodes ajenos a la configuracion del proyecto;
- se creo `BaseNewsletterMailable` para centralizar `List-Unsubscribe`, `List-Unsubscribe-Post` y el footer legal comun;
- se implemento `NewsletterDoubleOptIn` con `Envelope`, `Content`, metadata, cola y URL de confirmacion firmada;
- se implemento `NewsletterCampaignMail` y se sustituyo el envio legacy con `Mail::html(...)` dentro de `SendNewsletterCampaignJob`;
- se añadieron las vistas Blade `layout`, `double-opt-in` y `campaign` en `resources/views/emails/newsletter`;
- se anadieron textos de correo y footer legal en `es/en/ca/fr/it/de`;
- se incorporo una ruta publica minima para `privacy/terms/cookies` y una pagina Inertia simple para que el footer del email enlace a una politica real accesible;
- el alta publica ya encola el correo de confirmacion real usando el nuevo `Mailable`;
- se cubrio con tests el render del footer legal, los headers RFC 8058, la disponibilidad de la politica de privacidad y el envio de campanas por el nuevo `Mailable`.

Riesgos:

- hoy las campanas salen por `Mail::html(...)` y podrian seguir sin footer legal si no se refactorizan;
- el one-click RFC 8058 no queda cubierto solo con un enlace visible en el body.

Solucion propuesta:

- encapsular construccion de correo newsletter en una capa unica reutilizable por confirmacion y campanas.

### Fase 4. Frontend publico

- [x] Integrar formulario en `Home.jsx` dentro de `contact`.
- [x] Usar `useForm` de `@inertiajs/react`.
- [x] Campos:
  - [x] `email`
  - [x] checkbox `privacy_accepted`
  - [x] honeypot oculto
- [x] Pasar el valor del email a lowercase en tiempo real dentro del campo del formulario.
- [x] Validacion en tiempo real del email.
- [x] Spinner/estado de envio.
- [x] mensaje inline de exito y errores.
- [x] enlace visible a politica de privacidad.
- [x] Mostrar modal bien maquetado y traducido cuando el email ya exista en la lista.
- [x] mantener estilo actual legacy, sin inventar una seccion nueva.

Ejecucion real completada en esta fase:

- se termino la integracion visible del formulario newsletter dentro de `resources/js/Pages/Home.jsx` usando `useForm` oficial de Inertia;
- el campo `email` ya fuerza lowercase en tiempo real y valida formato antes del submit sin esperar al backend;
- el formulario publica `email`, `privacy_accepted`, `website` y `locale` contra `/newsletter/subscribe`, manteniendo `preserveScroll` y feedback inline;
- la politica de privacidad se abre desde el mismo flujo visual de `Contact`, reutilizando el modal legal existente o cayendo a la ruta publica legal si hiciera falta;
- el caso de duplicado activo abre un modal localizado dedicado en vez de insertar un registro nuevo o dejar un error crudo en pantalla;
- se anadieron estilos especificos en `resources/css/app.css` para la tarjeta newsletter, checkbox legal custom, spinner, feedback y modal, sin crear una seccion visual nueva fuera del layout legacy de `Contact`;
- se amplio `Phase7PublicCmsPayloadTest` para cubrir el payload localizado `contactData.newsletterForm`;
- validacion final ejecutada en Docker:
  - `php artisan test tests/Feature/NewsletterPublicFlowTest.php tests/Feature/Phase7PublicCmsPayloadTest.php`
  - `npm run build`

Nota de diseño:

- el formulario debe encajar en el layout `contact` actual con Tailwind y framer-motion, sin desplazar sponsors o marquees de manera arbitraria.

### Fase 5. Integracion con backoffice

- [x] Ampliar `newsletter-subscribers` con columnas y badges reales:
  - [x] Email
  - [x] Nombre
  - [x] Activo
  - [x] Suscrito en
  - [x] Baja en
  - [x] Acciones
- [x] Definir acciones especificas:
  - [x] baja manual
  - [x] reactivacion manual
  - [x] borrado definitivo RGPD
- [x] Mostrar indicadores superiores con lectura real de DB.
- [x] Mostrar datos legales utiles en formulario o drawer:
  - [x] version de consentimiento
  - [x] IP anonimizada
  - [x] user-agent
  - [x] origen del alta si se modela (no aplica en esta fase porque el modelo actual no lo expone)
- [x] Blindar permisos con policies existentes.

Ejecucion real completada en esta fase:

- se anadio `ManageNewsletterSubscriberBackofficeAction` para centralizar `unsubscribe`, `reactivate` y `forget` dentro de una transaccion y reutilizar `PrepareNewsletterSubscriberData` donde aplica;
- `PreviewController` ya resuelve las acciones operativas del modulo `newsletter-subscribers` sobre la ruta generica de acciones del shell;
- el indice reutilizable del backoffice ahora muestra acciones reales por fila:
  - `Editar`
  - `Dar de baja` o `Reactivar` segun el estado real del suscriptor
  - `Borrado RGPD` para `super_admin`, con confirmacion explicita antes del POST destructivo;
- la ficha del suscriptor expone la zona de negocio con accion contextual (`baja` o `reactivacion`) y la zona peligrosa de borrado definitivo RGPD solo para `super_admin`;
- el formulario del suscriptor muestra en solo lectura la traza legal operativa ya disponible en DB:
  - `consent_text_version`
  - `ip_address`
  - `user_agent`
  - `confirmation_token`
  - `unsubscribe_token`;
- los permisos quedan blindados asi:
  - `readonly` puede leer pero no mutar;
  - `editor` y `marketing` pueden gestionar alta/baja/reactivacion;
  - `super_admin` mantiene ademas el borrado definitivo con cascada de logs;
- validacion final ejecutada:
  - `php artisan test tests/Feature/BackofficePhase8OperationalModulesTest.php tests/Feature/BackofficePhase11QualityGateTest.php`
  - `php artisan test tests/Feature/BackofficePhase8OperationalModulesTest.php`
  - `npm run build`

### Fase 6. Cobertura legal y transparencia

- [x] Actualizar politica de privacidad en todos los idiomas para reflejar newsletter real.
- [x] Revisar textos de `footer` y `contact` si mencionan ausencia de tratamiento.
- [x] Definir `consent_text_version` inicial.
- [x] Conservar copia versionada del texto legal aceptado.
- [x] Añadir copy preciso en el formulario:
  - [x] finalidad comercial
  - [x] identificacion del responsable
  - [x] enlace a politica
  - [x] posibilidad de baja en cualquier momento

Ejecucion real completada en esta fase:

- se creo `NewsletterLegalConsent` como fuente unica de verdad para `consent_text_version = v1.1` y la version documental `2026.09`;
- se creo `NewsletterLegalContent` como fuente legal versionada y multidioma para `terms`, `privacy` y `cookies`, evitando depender de textos legacy contradictorios como fuente activa de publicacion;
- `ImportLegacyContentAction` ya no publica los documentos legales de newsletter desde el JSON legacy contradictorio, sino desde la fuente canonica versionada;
- se implemento `SyncNewsletterTransparencyLegalDocsAction` y el comando `php artisan legal:sync-newsletter-transparency` para sincronizar el entorno actual sin tener que reinicializar toda la base de datos;
- se ejecuto la sincronizacion real del entorno y quedaron publicados `3` documentos legales con `18` traducciones y version `2026.09`;
- `NewsletterSubscriber` y `NewsletterSubscriberController` quedaron alineados con `consent_text_version = v1.1`;
- el payload publico de `BuildPublicHomePayloadAction` expone ahora:
  - `consentVersion`
  - resumen legal visible del formulario con responsable, finalidad, baja y doble confirmacion;
- `Home.jsx` y `app.css` muestran ese resumen legal multidioma en la tarjeta newsletter de `Contact` sin crear una UI nueva ajena al layout existente;
- se conservo la copia versionada aceptada en `docs/legal/newsletter-consent/v1.1.md`;
- validacion final ejecutada:
  - `php artisan legal:sync-newsletter-transparency`
  - `php artisan test tests/Feature/Phase5LegacyImportTest.php tests/Feature/NewsletterPublicFlowTest.php tests/Feature/NewsletterMailablesTest.php tests/Feature/Phase7PublicCmsPayloadTest.php tests/Feature/BackofficePhase8OperationalModulesTest.php`
  - `npm run build`

### Fase 7. Tests y QA

- [x] Tests de migracion y esquema.
- [x] Tests de store publico:
  - [x] alta valida
  - [x] honeypot
  - [x] throttle
  - [x] email repetido
  - [x] reactivacion de baja previa
- [x] Tests de confirmacion:
  - [x] token valido
  - [x] token invalido
  - [x] signed URL expirada
- [x] Tests de unsubscribe:
  - [x] GET humano
  - [x] POST RFC 8058
  - [x] idempotencia
- [x] Tests de Mailable:
  - [x] footer legal
  - [x] headers `List-Unsubscribe`
  - [x] link de baja correcto
- [x] Tests de backoffice:
  - [x] listado real
  - [x] baja manual
  - [x] reactivacion
  - [x] borrado definitivo
- [x] Smoke visual del front en `contact`.

Ejecucion real completada en esta fase:

- se endurecio `NewsletterPublicFlowTest` con los casos que faltaban del plan:
  - honeypot con respuesta neutra sin persistencia;
  - throttling real tras cinco intentos por minuto;
  - signed URL de confirmacion expirada;
- se revalido la cobertura ya existente de:
  - migracion/esquema editorial y newsletter;
  - mailables con footer legal y headers RFC 8058;
  - payload publico localizado;
  - backoffice de suscriptores y shell oficial;
  - sync legal multidioma;
  - vistas y endpoints de automatizacion/produccion relacionados con el payload publico;
- se ejecuto la sincronizacion legal previa al QA final:
  - `php artisan legal:sync-newsletter-transparency`
  - resultado: `3` documentos legales, `18` traducciones, version `2026.09`;
- se ejecuto la bateria final en Docker:
  - `php artisan test tests/Feature/NewsletterPublicFlowTest.php tests/Feature/NewsletterMailablesTest.php tests/Feature/Phase5LegacyImportTest.php tests/Feature/Phase7PublicCmsPayloadTest.php tests/Feature/BackofficePhase8OperationalModulesTest.php tests/Feature/BackofficeShellPreviewTest.php tests/Feature/Phase8ProductionAutomationTest.php tests/Feature/Phase4EditorialBaselineTest.php`
  - resultado: `43 passed`, `612 assertions`;
- se ejecuto `npm run build` y el frontend compilo correctamente;
- se realizo smoke visual del front publico en `http://localhost:8080/en` confirmando en la tarjeta newsletter de `Contact`:
  - presencia del formulario;
  - `Email address`;
  - resumen legal visible con `Controller`, `Purpose`, `Unsubscribe` y `Confirmation`;
- el unico residuo detectado en QA final son warnings conocidos de Vite:
  - assets legacy resueltos en runtime;
  - chunk principal por encima de `500 kB`;
  - ambos sin bloqueo funcional inmediato para esta fase.

## Ajuste posterior al cierre

- se retiro del contenedor visible de newsletter en `Contact` el resumen legal corto (`Responsable`, `Finalidad`, `Baja`, `Confirmacion`) porque esa informacion ya queda contemplada en los documentos legales publicados y no debe duplicarse en la tarjeta;
- se mantuvo el checkbox legal con enlace a politica de privacidad y consentimiento expreso para comunicaciones comerciales;
- se recoloco la tarjeta newsletter por encima del bloque de redes sociales y se redujo el tamano de los iconos sociales para mejorar la composicion visual de `Contact` sin alterar el resto del layout.
- ajuste posterior adicional:
  - tarjeta newsletter centrada horizontalmente mediante contenedor dedicado para evitar que `framer-motion` pisara el `transform` de centrado;
  - redes sociales recolocadas en la franja entre el marquee y el carrusel de sponsors, centradas y convertidas en botones con hover visible.

## Orden de ejecucion recomendado

1. Cerrar decision sobre tratamiento de suscriptores legacy.
2. Migracion y modelo.
3. Rutas/controlador/servicios publicos.
4. Mailable de confirmacion.
5. Integracion frontend.
6. Refactor de campanas para footer y unsubscribe legal.
7. Acciones extra del backoffice.
8. Actualizacion legal multidioma.
9. Tests de extremo a extremo.

## Hallazgos y soluciones

### Hallazgo 001

El proyecto ya tiene newsletter operativa a nivel backoffice, pero no tiene captacion publica legal.

Solucion:

- construir sobre `newsletter_subscribers` actual, no en paralelo.
- dado que hoy la tabla esta vacia, no hace falta estrategia compleja de migracion legacy para arrancar el nuevo flujo.

### Hallazgo 002

La migracion actual activa por defecto al suscriptor (`is_active = true`), incompatible con double opt-in.

Solucion:

- nueva migracion de alter table con `default false` y logica de activacion solo tras confirmacion o alta administrativa controlada.

### Hallazgo 002-B

La regla de normalizacion del email debe ser coherente entre UX y persistencia.

Solucion:

- el campo del front convertira automaticamente el email a lowercase;
- el backend volvera a convertir a lowercase antes de validar y guardar;
- si el email ya existe, no se insertara duplicado y el front mostrara un modal localizado en el idioma activo.

### Hallazgo 003

Los correos de campana actuales no incluyen footer legal ni cabeceras `List-Unsubscribe`.

Solucion:

- extraer una capa base de correo newsletter reutilizable.

### Hallazgo 004

La politica de privacidad publica actual niega la existencia de newsletters y listas de correo.

Solucion:

- bloquear despliegue de la funcionalidad hasta actualizar textos legales y copy multidioma.

### Hallazgo 005

El shell actual del backoffice no tiene borrado definitivo RGPD listo para `newsletter-subscribers`.

Solucion:

- introducir accion especifica y protegida para derecho al olvido, con confirmacion fuerte.

## Criterios de salida

La integracion se considerara cerrada solo si:

- el alta publica requiere consentimiento expreso;
- no se activa ningun suscriptor sin double opt-in o alta administrativa deliberada;
- todos los correos newsletter incluyen baja visible y headers correctos;
- el backoffice refleja el estado real de DB y permite operar bajas/reactivaciones/borrado;
- la politica de privacidad y el texto del formulario quedan alineados con el tratamiento real;
- existe cobertura de tests suficiente para store, confirm, unsubscribe, campaign send y backoffice.

## Ajuste posterior de incidencia

- el entorno local tenia pendiente la migracion `2026_09_27_130000_harden_newsletter_subscribers_for_legal_compliance`, lo que provocaba el error real `column "confirmation_token" does not exist` al intentar suscribirse desde `Contact`;
- se corrige la propia migracion pendiente para PostgreSQL soltando y recreando la vista `automation_newsletter_subscribers` antes y despues del cambio de esquema, evitando el bloqueo por dependencia sobre `is_active`;
- tras ejecutar `php artisan migrate --force`, el alta publica deja de romper por esquema y vuelve a quedar alineada con el flujo legal implementado;
- la maquetacion de `Contact` se endurece con bandas verticales reservadas para `newsletter`, `marquee`, `redes sociales` y `sponsors`, de forma que los botones sociales queden entre carruseles sin invadirlos.

# Plan Descargables + Suscriptores

## Estado

- Estado actual: `ANALISIS / AUDITORIA / PLANIFICACION`
- Implementacion: `NO EJECUTAR TODAVIA`
- Objetivo de negocio: convertir el modulo `Archivos descargables` en una palanca real de captacion de suscriptores y, de forma secundaria, de seguidores en redes.
- Objetivo tecnico: hacerlo sin romper el frontend actual, reutilizando la base legal/newsletter/n8n ya existente y dejando trazabilidad robusta.

## Conclusiones ejecutivas

1. El proyecto ya tiene una base muy aprovechable:
   - newsletter publica con alta trazable, honeypot, throttling, baja y consentimiento versionado;
   - modulo backoffice real de `downloadable-files`;
   - enlaces sociales globales;
   - canal Laravel -> n8n firmado y logueado;
   - infraestructura de modal y formulario ya viva en `Home.jsx`.
2. La idea es tecnicamente viable.
3. La parte delicada no es tecnica, sino legal:
   - obligar a aceptar newsletter comercial para recibir un archivo descargable tiene riesgo de invalidez del consentimiento si se plantea como consentimiento bundling no necesario para la prestacion pedida;
   - si se quiere mantener el caracter "obligatorio", la formulacion debe cambiar de "te doy un archivo y de paso te suscribo" a "esta biblioteca/recurso es exclusivo para suscriptores", con transparencia total antes de enviar el formulario;
   - aun asi, por prudencia RGPD/LSSI, conviene validacion legal final antes de ponerlo en produccion.
4. La captacion de seguidores en redes sociales puede incentivarse, pero no puede imponerse de forma fiable ni verificarse honestamente desde la web para todas las plataformas:
   - `YouTube`: si tiene boton oficial de suscripcion embebible;
   - `Facebook`: si tiene `Page Plugin` oficial;
   - `Instagram` y `Threads`: desde web la accion real sigue siendo manual del usuario;
   - `SoundCloud`: el follow via API requiere OAuth del usuario.
5. Recomendacion de arquitectura:
   - modal de seleccion de uno o varios descargables;
   - email + consentimiento expreso;
   - alta/reactivacion de newsletter en Laravel;
   - registro auditable de la solicitud;
   - disparo de workflow n8n;
   - envio por email con enlaces firmados temporales como modo recomendado;
   - redes sociales como CTA secundaria post-envio, no como condicion bloqueante.

## Auditoria del estado real del proyecto

### 1. Newsletter ya existente

Base auditada:

- `routes/web.php`
- `app/Http/Controllers/NewsletterSubscriberController.php`
- `app/Models/NewsletterSubscriber.php`
- `app/Support/NewsletterLegalConsent.php`
- `tests/Feature/NewsletterPublicFlowTest.php`

Estado detectado:

- El flujo vigente es `single opt-in trazable`, no `double opt-in` activo.
- Ya se guardan:
  - email normalizado a minusculas;
  - `unsubscribe_token`;
  - IP anonimizada;
  - `user_agent`;
  - `consent_text_version`;
  - `subscribed_at` / `unsubscribed_at`;
  - honeypot (`website`);
  - throttling.
- El contrato actual ya esta alineado con `consent_text_version = v1.2`.
- El alta publica actual rechaza duplicados activos, pero reactiva bajas previas.

Implicacion:

- no conviene rehacer newsletter desde cero;
- conviene crear un flujo especifico para "solicitud de descargables" que reutilice la logica legal de suscripcion, pero no la UX de error por duplicado del endpoint generico.

### 2. Downloadable files ya existentes

Base auditada:

- `app/Models/DownloadableFile.php`
- `app/Actions/Backoffice/SaveBackofficePhase6ModuleAction.php`
- `app/Actions/Backoffice/BuildBackofficePhase6CrudPayloadAction.php`
- `tests/Feature/BackofficePhase7RichContentPersistenceTest.php`

Estado detectado:

- Ya existe modelo `DownloadableFile` con:
  - `slug`
  - `display_name`
  - `description`
  - `disk`
  - `file_path`
  - `file_name`
  - `mime_type`
  - `size`
  - `external_url`
  - `collection`
  - adjunto polimorfico
  - `position`
  - `is_active`
  - `settings`
- Ya existe CRUD real de backoffice.
- Ya existe validacion de adjunto polimorfico.
- No existe hoy un flujo publico de entrega controlada por email.

Implicacion:

- el backend editorial de archivos ya esta;
- falta la capa publica de seleccion, solicitud, entrega y auditoria.

### 3. Automatizacion n8n ya existente

Base auditada:

- `app/Actions/Automation/DispatchN8nWorkflowAction.php`
- `app/Http/Middleware/EnsureValidAutomationSignature.php`
- `routes/api.php`
- `app/Http/Controllers/Automation/N8nResultWebhookController.php`
- `database/migrations/2026_09_27_130000_harden_newsletter_subscribers_for_legal_compliance.php`

Estado detectado:

- Laravel ya puede despachar workflows a n8n.
- La salida ya va firmada con cabeceras HMAC.
- Ya existe canal inbound para que n8n reporte resultados a Laravel.
- Ya existe `automation_logs` como bitacora tecnica.
- Ya existe vista `automation_newsletter_subscribers` para consumo automatizado de suscriptores activos.

Implicacion:

- no hay que inventar otra pasarela de automatizacion;
- la nueva funcionalidad debe colgar de esta infraestructura y no crear un bypass paralelo.

### 4. Frontend publico ya existente

Base auditada:

- `resources/js/Pages/Home.jsx`
- `app/Actions/PublicSite/BuildPublicHomePayloadAction.php`

Estado detectado:

- `Home.jsx` ya usa modal y ya tiene formulario newsletter con feedback localizado.
- El payload publico ya expone `contactData.newsletterForm` y enlaces sociales globales.
- No aparece hoy una lista publica de descargables.

Implicacion:

- el sitio ya tiene patron de UI reutilizable;
- el trabajo de frontend debe limitarse a anadir un modal de descargables y su wiring de datos, sin reescribir el front publico.

## Marco legal y restricciones oficiales a respetar

## 1. Newsletter y correos comerciales

Fuentes oficiales revisadas:

- BOE Ley 34/2002 (LSSI), arts. 20 y 21
- AEPD, Informe 0219/2009
- EDPB, Directrices 5/2020 sobre consentimiento

Principios que condicionan el diseno:

1. Para enviar comunicaciones comerciales por email hace falta consentimiento previo o encajar en la excepcion legal de cliente previo.
2. La comunicacion comercial debe identificarse claramente como tal y el emisor debe ser identificable.
3. Si existe incentivo promocional, sus condiciones deben estar claras y accesibles.
4. El consentimiento debe ser libre, especifico, informado e inequivoco.
5. Si la entrega del archivo se condiciona a una finalidad no estrictamente necesaria para esa entrega, aparece riesgo de "consentimiento no libre".

Traduccion practica al proyecto:

- No vale una casilla premarcada.
- No vale ocultar que la persona entra en newsletter.
- No vale reutilizar el email de una "descarga" como si fuera consentimiento comercial implicito.
- Si se fuerza la suscripcion, el modal debe decirlo de forma frontal antes de enviar nada.

## 2. Redes sociales

Fuentes oficiales revisadas:

- YouTube Subscribe Button
- Facebook Page Plugin
- Instagram Help Center
- Threads Help Center
- SoundCloud API Guide

Conclusiones:

- `YouTube`: existe boton oficial de suscripcion, pero la confirmacion final la hace la persona usuaria en YouTube.
- `Facebook`: existe `Page Plugin`; sirve para promocionar la pagina y permite interaccion propia de Facebook.
- `Instagram`: la propia ayuda oficial orienta a buscar o seguir perfiles dentro de Instagram; no se ha identificado un boton web oficial de "follow" embebible equivalente al de YouTube.
- `Threads`: la accion de seguir sigue siendo una confirmacion manual del usuario en la plataforma.
- `SoundCloud`: el follow oficial por API requiere actuar en nombre del usuario autenticado mediante OAuth.

Conclusiones de producto:

- no se debe prometer "seguir automaticamente" desde la web;
- no se debe bloquear la descarga a la verificacion de follow social;
- las redes deben quedar como CTA secundaria, medible y opcional.

## Decision de negocio recomendada

### Opcion recomendada

Presentar la funcionalidad como:

`Recursos/archivos exclusivos para suscriptores`

Esto permite que el flujo sea coherente:

- la suscripcion no queda escondida;
- el archivo se entiende como beneficio asociado a la suscripcion;
- el usuario sabe antes de enviar el formulario que entra en newsletter;
- la entrega del recurso llega por email como parte del onboarding.

### Opcion no recomendada

Presentarlo como:

`Descarga este archivo` y despues convertir esa accion en alta obligatoria a newsletter.

Riesgos:

- peor defensa legal;
- mayor friccion en reclamaciones;
- menor transparencia;
- mas posibilidad de considerarse consentimiento no libre.

## Arquitectura funcional propuesta

### 1. Flujo publico

1. La persona abre un CTA tipo `Descargar recursos`.
2. Se abre un modal con:
   - listado de descargables activos disponibles;
   - seleccion multiple;
   - email;
   - checkbox legal desmarcado por defecto;
   - textos legales y links a privacidad/terminos/cookies;
   - honeypot oculto;
   - CTA secundaria de redes.
3. Al enviar:
   - si el email no existe: se crea suscriptor activo con traza;
   - si existe y esta activo: no se rechaza; se acepta la solicitud y se evita duplicado de suscriptor;
   - si existe y estaba de baja: se reactiva solo si vuelve a consentir expresamente.
4. Laravel registra la solicitud.
5. Laravel despacha el workflow n8n.
6. n8n envia el email de entrega del recurso.
7. Laravel recibe el resultado tecnico desde n8n y actualiza la trazabilidad.
8. El frontend muestra mensaje neutro de exito, nunca detalles internos.

### 2. Endpoint publico nuevo

Crear un endpoint especifico, separado del alta generica actual, por ejemplo:

- `POST /downloadables/request`

Razon:

- el endpoint actual de newsletter esta optimizado para alta simple y marca duplicado activo como error;
- el nuevo flujo necesita aceptar:
  - alta nueva;
  - reactivacion;
  - solicitud de recursos por suscriptor ya activo;
- separarlo reduce riesgo de regresion sobre el newsletter actual del footer/contact.

### 3. Modo de entrega recomendado

### Recomendacion principal: enlaces firmados temporales

No recomiendo que n8n adjunte siempre fisicamente todos los archivos al correo.

Motivos:

- peor entregabilidad;
- limites de peso;
- mas rebotes o spam score;
- mas complejidad operativa si los archivos cambian;
- menos control de expiracion.

Diseno recomendado:

- el email lleva uno o varios enlaces firmados temporales generados por Laravel;
- cada enlace apunta a un endpoint interno controlado;
- el endpoint valida expiracion, estado del archivo y token;
- solo entonces sirve el archivo.

### Modo secundario opcional

Permitir adjunto real solo si:

- el archivo es pequeno;
- el total de seleccion no supera un umbral definido;
- el proveedor SMTP lo soporta con margen.

Recomendacion:

- `v1`: solo enlaces firmados temporales;
- `v2`: adjuntos pequenos como mejora opcional y controlada.

## Modelo de datos recomendado

### 1. Mantener `newsletter_subscribers` como fuente unica de verdad

No duplicar suscriptores en otra tabla.

### 2. Anadir una tabla de negocio para la solicitud de recursos

Tabla sugerida:

- `downloadable_requests`

Campos sugeridos:

- `id`
- `subscriber_id`
- `email_snapshot`
- `locale`
- `source`
- `status` (`pending`, `queued`, `sent`, `failed`, `cancelled`)
- `ip_address`
- `user_agent`
- `consent_text_version`
- `requested_at`
- `sent_at`
- `failed_at`
- `automation_log_id`
- `payload_snapshot`

Tabla hija sugerida:

- `downloadable_request_items`

Campos sugeridos:

- `id`
- `downloadable_request_id`
- `downloadable_file_id`
- `display_name_snapshot`
- `file_name_snapshot`
- `delivery_mode_snapshot`
- `position`

Motivo:

- `automation_logs` es bitacora tecnica, no registro de negocio;
- el equipo necesitara saber que archivo se pidio, por quien y cuando;
- deja trazabilidad separada para auditoria, soporte y BI.

### 3. Extensiones minimas en `downloadable_files`

No meter mas logica de la necesaria si no hace falta.

Suficiente para esta fase:

- reutilizar `is_active`
- reutilizar `settings`
- si hace falta, anadir dentro de `settings`:
  - `delivery_mode`
  - `requires_newsletter`
  - `link_ttl_minutes`
  - `public_cta_label`

Recomendacion:

- empezar con `settings` porque el modelo ya lo soporta;
- solo normalizar a columnas nuevas si el dominio crece de verdad.

## Integracion frontend propuesta

### 1. Superficie

No inventar una pagina nueva si no es imprescindible.

Recomendacion:

- exponer los descargables en el area publica ya prevista por contenido;
- lanzar desde ahi un modal;
- reutilizar el patron visual del modal/newsletter ya existente en `Home.jsx`.

### 2. Contenido del modal

Bloques:

1. Titulo y descripcion.
2. Lista de archivos activos con checkbox multiple.
3. Campo email.
4. Consentimiento legal explicito.
5. Texto corto explicando que el archivo se enviara al correo y que la persona pasa a formar parte del newsletter.
6. CTA principal.
7. CTA secundaria de redes:
   - YouTube boton oficial;
   - Facebook plugin o link oficial;
   - Instagram, Threads y SoundCloud como links de seguimiento manual.

### 3. Reglas UX

- email siempre a minusculas;
- checkbox legal siempre desmarcado por defecto;
- feedback localizado por idioma;
- errores inline;
- mensaje de exito sin filtrar si el email ya existia o no;
- si no se selecciona ningun archivo, no se envia nada;
- el modal no debe tocar scroll ni animaciones globales fuera de su propio alcance.

## Integracion backend propuesta

### 1. Nuevo request dedicado

Request validado con:

- `email`
- `privacy_accepted`
- `downloadable_ids[]`
- `website` honeypot
- `locale`

Validaciones:

- email RFC/DNS;
- ids existentes y activos;
- maximo de elementos por solicitud;
- throttling por IP + email;
- deduplicacion temporal por combinacion email + archivos.

### 2. Servicio de aplicacion

Crear una accion dedicada, por ejemplo:

- `RequestDownloadableResourcesAction`

Responsabilidades:

1. normalizar email;
2. resolver o crear suscriptor;
3. aplicar alta/reactivacion segun consentimiento;
4. crear `downloadable_request`;
5. crear sus items;
6. generar payload canonico;
7. despachar n8n con `DispatchN8nWorkflowAction`;
8. devolver resultado neutral al controlador.

### 3. Reglas de negocio

- Suscriptor activo ya existente:
  - no error;
  - se registra la nueva solicitud;
  - se envia el email con recursos.
- Suscriptor inactivo:
  - se reactiva solo con consentimiento expreso del envio actual.
- Archivo inactivo o borrado:
  - no debe aparecer en modal;
  - si llega por manipulacion de request, se rechaza.
- `external_url`:
  - si existe, no exponerla cruda en el modal;
  - la entrega debe pasar igualmente por capa controlada/logueada cuando sea posible.

## Workflow n8n propuesto

### 1. Entrada

Workflow ejemplo:

- `downloadable-lead-delivery`

Entrada desde Laravel:

- `workflow`: nombre fijo
- `request_id`
- `subscriber_id`
- `locale`
- `email`
- `files[]`
- `download_links[]`
- `consent_version`

### 2. Estructura recomendada del workflow

1. `Webhook` de n8n.
2. Validacion temprana de firma/cabeceras.
3. Normalizacion del payload.
4. Construccion del email.
5. `Send Email`.
6. Callback firmado a Laravel en `/api/internal/automation/n8n/results`.

### 3. Seguridad del workflow

Aunque Laravel ya firma la llamada saliente, recomiendo en n8n:

- validar cabeceras firmadas al inicio;
- usar workflow publicado solo en URL de produccion;
- no dejar webhooks de prueba como endpoint operativo;
- no meter secretos ni rutas reales de almacenamiento en el payload visible.

### 4. Envio de email

Recomendacion:

- asunto localizado;
- lista de recursos solicitados;
- botones por archivo;
- footer legal;
- recordatorio de baja del newsletter;
- CTA social secundaria.

## Seguridad y ant abuso

1. Honeypot igual que en newsletter actual.
2. Throttle especifico para `downloadables/request`.
3. Dedupe temporal de solicitudes repetidas.
4. No revelar si un email existe o no existe.
5. No exponer `file_path` ni URLs internas sin firma.
6. TTL corto para enlaces firmados.
7. Si el recurso es sensible, estudiar token de un solo uso.
8. Log tecnico + log de negocio separados.
9. Tests de abuso:
   - flood;
   - IDs manipulados;
   - archivo inactivo;
   - email duplicado;
   - baja previa;
   - callback n8n invalido.

## Ajustes legales necesarios en el proyecto

### 1. Versionado de consentimiento

Recomendacion:

- subir `NewsletterLegalConsent::CONSENT_VERSION` a `v1.3` cuando se implemente.

Motivo:

- cambia la finalidad real del alta: ya no solo "recibir novedades/comunicaciones", sino tambien "recibir recursos descargables solicitados dentro del programa de suscripcion".

### 2. Actualizar textos legales publicados

Habra que alinear:

- `NewsletterLegalContent`
- documentos legales sincronizados
- copy del modal
- copy del email

Puntos que deben quedar explicitados:

- finalidad: alta newsletter + entrega de recursos solicitados;
- base juridica: consentimiento;
- procesadores implicados: hosting, SMTP y n8n si aplica;
- baja en un clic;
- version del texto aceptado;
- posibilidad de solicitar varios archivos en una misma operacion.

### 3. Transparencia del incentivo

Si el recurso es el incentivo, debe quedar claro:

- que la entrega va ligada al alta;
- que no es una simple descarga anonima;
- que la suscripcion puede cancelarse en cualquier momento;
- que la baja no invalida el acceso ya entregado, salvo politica especifica comunicada por adelantado.

## Redes sociales: plan realista

### V1 recomendada

- Mostrar CTA secundaria de redes tras el submit correcto o dentro del email.
- Medir clics con analytics.
- No bloquear entrega del recurso por no seguir perfiles.

### Integracion por plataforma

- `YouTube`: usar boton oficial de suscripcion.
- `Facebook`: estudiar `Page Plugin` si encaja visualmente; si no, link externo trackeado.
- `Instagram`: link externo trackeado al perfil.
- `Threads`: link externo trackeado al perfil.
- `SoundCloud`: link externo trackeado al perfil.

### Lo que no recomiendo en V1

- OAuth social para intentar verificar follows.
- exigir follow como condicion tecnica de entrega.
- prometer "seguir automaticamente".

## Plan de implementacion propuesto

### Fase 1. Contrato funcional y legal

- cerrar copy del modal;
- cerrar copy del email;
- decidir si el producto se presenta como `biblioteca exclusiva para suscriptores`;
- decidir politica de adjuntos vs enlaces firmados;
- aprobar version legal nueva.

### Fase 2. Backend de negocio

- nuevas tablas `downloadable_requests` y `downloadable_request_items`;
- request + controller publico dedicado;
- accion de aplicacion dedicada;
- generacion de enlaces firmados;
- tests unitarios/feature.

### Fase 3. Payload publico y frontend

- anadir descargables activos al payload publico;
- modal de seleccion multiple;
- wiring con endpoint nuevo;
- feedback localizado;
- CTA sociales secundarias.

### Fase 4. Workflow n8n

- webhook productivo;
- validacion de firma;
- construccion del email;
- envio SMTP;
- callback de resultado a Laravel.

### Fase 5. QA

- pruebas funcionales;
- pruebas legales/copy;
- pruebas de abuso;
- pruebas de expiracion de links;
- pruebas multidioma;
- pruebas responsive sin tocar animaciones globales.

## Cobertura de tests que deberia existir

### Backend

- alta nueva + solicitud multiple;
- suscriptor activo ya existente + solicitud multiple;
- reactivacion de baja previa;
- rechazo de archivo inactivo;
- rechazo de lista vacia;
- throttle;
- honeypot;
- firma de callback n8n;
- expiracion de link firmado.

### Frontend

- modal abre/cierra bien;
- seleccion multiple;
- validacion email;
- checkbox legal obligatorio;
- feedback localizado;
- CTA sociales visibles sin romper layout;
- ningun cambio colateral sobre scroll/animaciones.

## Riesgos principales

1. **Riesgo legal**: consentimiento no suficientemente libre si se vende como descarga puntual y no como recurso exclusivo para suscriptores.
2. **Riesgo de entregabilidad**: adjuntos pesados en email.
3. **Riesgo UX**: demasiada friccion si se mezclan descarga, alta y follows sociales en un solo paso.
4. **Riesgo tecnico**: exponer rutas de almacenamiento sin firma.
5. **Riesgo de regresion**: tocar `Home.jsx` sin acotar bien la nueva UI.

## Recomendacion final

La via mas solida para cumplir el objetivo de negocio sin hacer una chapuza es esta:

1. tratar los descargables como `beneficio exclusivo para suscriptores`;
2. crear un endpoint dedicado de solicitud de recursos;
3. reutilizar la newsletter legal y trazable ya implantada;
4. registrar cada solicitud en tablas propias de negocio;
5. enviar email desde n8n con enlaces firmados temporales;
6. dejar redes sociales como capa de captacion secundaria, no como bloqueo obligatorio.

Con este enfoque:

- se gana base de suscriptores;
- se reutiliza la arquitectura existente;
- se evita inventar automatismos falsos en redes;
- se mantiene una posicion legal mucho mas defendible.

## Fuentes oficiales revisadas

- Laravel Mail / cabeceras y mailables: https://laravel.com/docs/13.x/mail
- YouTube Subscribe Button: https://developers.google.com/youtube/subscribe
- Facebook Page Plugin: https://developers.facebook.com/docs/plugins/page-plugin/
- SoundCloud API Guide: https://developers.soundcloud.com/docs/api/guide
- n8n Webhook node: https://docs.n8n.io/integrations/builtin/core-nodes/n8n-nodes-base.webhook/
- n8n Send Email node: https://docs.n8n.io/integrations/builtin/core-nodes/n8n-nodes-base.sendemail/
- BOE Ley 34/2002 (LSSI): https://www.boe.es/eli/es/l/2002/07/11/34/con
- AEPD Informe 0219/2009: https://www.aepd.es/documento/2009-0219.pdf
- EDPB Directrices 5/2020 sobre consentimiento: https://www.edpb.europa.eu/our-work-tools/our-documents/guidelines/guidelines-052020-consent-under-regulation-2016679_es
- Instagram Help Center: https://help.instagram.com/1128997980474717
- Threads Help Center: https://help.instagram.com/150298994419902/Follow+or+unfollow+someone+on+Threads/?locale2=es-ES

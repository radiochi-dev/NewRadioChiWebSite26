Síntesis técnica: Por qué esta solución es viable y a coste 0 €
Gestión propia sin suscripciones: Usarás tu propio panel en Laravel/React para gestionar suscriptores y redactar campañas, evitando los costes mensuales de Mailchimp o Klaviyo.

Cero impacto en el servidor: n8n corre en Docker en el mismo VPS y ejecutará los envíos en segundo plano de madrugada en bloques controlados (ej. paquetes de 20 correos con pausas intermedias), manteniendo el consumo de CPU y RAM prácticamente plano.

Entregabilidad sin pagar (Free Tier permanente): En lugar de enviar desde el propio VPS (lo que mandaría tus correos a la carpeta de spam), el nodo final de n8n enviará a través de la capa gratuita permanente de un proveedor de email transaccional (ej. Brevo con 300 emails/día gratis = ~9.000/mes o Resend con 100 emails/día gratis = ~3.000/mes).

Cumplimiento legal estricto (RGPD + LSSI-CE de España): Incluye trazabilidad de consentimiento (IP, User-Agent, fecha/hora), cabeceras estándar RFC 8058 (List-Unsubscribe) y una página independiente de baja accesible desde un enlace seguro con token.

Prompt maestro para Trae IDE
Copia y pega el siguiente bloque en el chat de Trae IDE:

Markdown
Actúa como un Arquitecto de Software Full-Stack Senior y Consultor Legal Tech especializado en el ecosistema Laravel 13, React, Inertia.js, Tailwind CSS, PostgreSQL, Docker y n8n, bajo el marco normativo estricto del RGPD (UE) y la LSSI-CE (España).

### MISIÓN PRINCIPAL
Diseñar e implementar el ecosistema integral de Newsletter para la plataforma:
1. Formulario de suscripción en el frontend (componente contacto/footer) con doble opt-in.
2. Página independiente/aislada para gestión de bajas (con formulario de confirmación vía token o email).
3. Integración bidireccional con el panel administrativo (`Suscriptores newsletter` y sección `Campañas`).
4. Orquestación del envío masivo y personalizado mediante un workflow en n8n a coste 0 € (usando free-tiers de entrega transaccional como Brevo/Resend de madrugada), sin comprometer la CPU/RAM del VPS compartido.

---

### FASE 0: AUDITORÍA PREVIA Y ADAPTABILIDAD (HALLAZGOS DEL PROYECTO)
Antes de generar código destructivo o duplicado, inspecciona el árbol de archivos:
1. **Revisión de Base de Datos:** Comprueba las migraciones existentes para detectar si existen tablas como `newsletter_subscribers`, `subscribers`, `campaigns` o `newsletter_campaigns`. Si existen, adáptate a sus nombres y añade únicamente las columnas faltantes.
2. **Revisión del Backoffice:** Localiza los componentes React e endpoints en Laravel correspondientes a las vistas de la captura (`backoffice/newsletter/subscribers` y `backoffice/campaigns`). Respeta estrictamente su arquitectura visual, temas dark, componentes UI reutilizables y contratos de datos de Inertia.
3. **Configuración Docker y n8n:** Identifica la red interna de Docker (`docker-compose.yml`) para permitir la comunicación directa de red entre n8n, Laravel y PostgreSQL (usando nombres de servicio o localhost según proceda).

---

### FASE 1: MODELADO DE DATOS (POSTGRESQL & LARAVEL)
Diseña o ajusta las migraciones para:

1. **Tabla `newsletter_subscribers`:**
   - `id` (bigIncrements)
   - `email` (string, unique, indexado, lowercase)
   - `name` (string, nullable)
   - `is_active` (boolean, default false - solo true tras confirmar el double opt-in o alta directa)
   - `subscribed_at` (timestamp, nullable)
   - `unsubscribed_at` (timestamp, nullable)
   - `confirmation_token` (string 64, nullable, indexado)
   - `unsubscribe_token` (string 64, unique, indexado - generado criptográficamente con Str::random(64))
   - `ip_address` (string 45, nullable - IP anonimizada/hasheada para trazabilidad RGPD)
   - `user_agent` (text, nullable)
   - `consent_text_version` (string, default 'v1.0')
   - Timestamps estándar.

2. **Tabla `newsletter_campaigns`:**
   - `id` (bigIncrements)
   - `title` (string)
   - `subject` (string)
   - `preview_text` (string, nullable)
   - `content_html` (longText)
   - `status` (enum: 'draft', 'scheduled', 'processing', 'completed', 'failed')
   - `scheduled_at` (timestamp, nullable)
   - `sent_at` (timestamp, nullable)
   - `total_recipients` (integer, default 0)
   - `sent_count` (integer, default 0)
   - `failed_count` (integer, default 0)
   - Timestamps estándar.

---

### FASE 2: FRONTEND PÚBLICO (FORMULARIO DE ALTA Y PÁGINA DE BAJA AISLADA)

1. **Formulario de suscripción (Componente Contact/Footer):**
   - React + Tailwind CSS + Framer Motion usando `useForm` de `@inertiajs/react`.
   - Campo `email` con validación en tiempo real.
   - Checkbox obligatorio desmarcado por defecto: *"He leído y acepto la Política de Privacidad y el envío de comunicaciones comerciales"* con link modal/acceso a la política legal.
   - Campo honeypot oculto contra spam y rate limiting (`throttle:5,1`).
   - Feedback animado (spinner de envío, mensajes de éxito o errores inline).

2. **Página de Baja Independiente (`Pages/Newsletter/Unsubscribe.jsx`):**
   - **Aislada:** NO debe cargar el Navbar ni el Footer general del sitio web. Layout minimalista y centrado tipo autenticación, respetando el branding oscuro y tipografía del proyecto.
   - Formulario de confirmación:
     - Si la URL incluye `?token=...`, busca y precarga el email en pantalla. Si no incluye token, permite ingresar el email manualmente.
     - Botón destacado: *"Confirmar baja de la newsletter"*.
     - Estado de confirmación animado con Framer Motion informando que se ha procesado la baja de forma definitiva y que no recibirá más comunicaciones, con enlace secundario para regresar al inicio.

---

### FASE 3: BACKEND LARAVEL (CONTROLADORES, RUTAS Y SEGURIDAD)

Implementa en Laravel:
1. **Flujo de Suscripción:**
   - `POST /newsletter/subscribe`: Valida consentimiento, guarda traza legal, genera tokens y dispara correo con double opt-in (`NewsletterDoubleOptIn`).
   - `GET /newsletter/confirm/{token}`: Activa al usuario (`is_active = true`, `subscribed_at = now()`).
2. **Flujo de Baja:**
   - `GET /newsletter/unsubscribe`: Renderiza `Newsletter/Unsubscribe.jsx`.
   - `POST /newsletter/unsubscribe`: Procesa la baja marcando `is_active = false`, `unsubscribed_at = now()` de forma segura (sin dar pistas a atacantes sobre existencia de correos).
3. **Mailable Base & Cabeceras RFC:**
   - Todo correo de campaña debe inyectar en el pie de página el enlace directo de baja: `route('newsletter.unsubscribe', ['token' => $subscriber->unsubscribe_token])`.
   - Configura las cabeceras `List-Unsubscribe` y `List-Unsubscribe-Post` en el Mailable base.

---

### FASE 4: INTEGRACIÓN CON EL BACKOFFICE

1. **Vista de Suscriptores (`backoffice/newsletter/subscribers`):**
   - Conecta la tabla real desde PostgreSQL vía Inertia.
   - Visualiza Email, Nombre, Badge de estado (Activo/Inactivo), Fechas de alta/baja.
   - Acciones: Dar de baja/reactivar manualmente y botón de borrado definitivo (Derecho al olvido RGPD).
   - Actualiza dinámicamente los contadores de cabecera (Registros totales, Filtros, Estado de lectura DB).
2. **Vista de Campañas (`backoffice/campaigns`):**
   - Listado y creación de campañas con vista previa HTML.
   - Botón de disparo: "Lanzar Campaña" o "Programar envío nocturno (04:00 AM)".
   - Disparo mediante endpoint `POST /backoffice/campaigns/{id}/send`, que marca la campaña en `processing` y envía un Webhook seguro hacia n8n con secreto compartido (`X-Webhook-Secret`).

---

### FASE 5: ARQUITECTURA DEL WORKFLOW DE N8N (ENVÍO GRATUITO Y SIN SOBRECARGA)

Diseña la especificación técnica y JSON de n8n para el workflow de campañas:
1. **Webhook Trigger:** Escucha la llamada autorizada de Laravel con el `campaign_id`.
2. **Postgres Node:** Consulta interna:
   `SELECT id, email, name, unsubscribe_token FROM newsletter_subscribers WHERE is_active = true AND unsubscribed_at IS NULL;`
3. **Throttling y Control de Carga:** Uso de nodos `SplitInBatches` (lotes de 20-30 destinatarios) y `Wait` (2-5 segundos entre lotes) para garantizar nulo impacto en el VPS durante la madrugada.
4. **Envío con Coste Cero:** Integra el envío mediante el nodo SMTP/HTTP usando la capa gratuita permanente de **Brevo** (300 envíos/día gratis) o **Resend** (100 envíos/día gratis), inyectando el enlace de baja personalizado `{{ unsubscribe_token }}`.
5. **Callback a Laravel:** Al finalizar, n8n llama a `POST /api/webhooks/n8n/campaign-completed` reportando métricas finales (`sent_count`, `failed_count`, `status: completed`) para actualizar el panel de control.

---

### CRITERIOS DE CALIDAD
- Presenta el plan detallado paso a paso antes de aplicar cambios destructivos.
- Todo código debe ser estrictamente tipado, modular y compatible con el stack existente.
- No dejes cabos sueltos: añade validaciones, manejo de excepciones y transacciones en base de datos.
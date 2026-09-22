# Recordar antes Depoly Produccion

## Pendientes obligatorios antes de desplegar a produccion

- [ ] Definir el `GA4_MEASUREMENT_ID` real de produccion o de la propiedad de staging que vaya a usarse para la validacion final.
- [ ] Revisar y aprobar legalmente el texto de `cookies`, `privacidad` y `terminos` para que quede alineado con el uso real de Google Analytics 4.
- [ ] Configurar en entorno de produccion:
  - [ ] `GA4_ENABLED=true`
  - [ ] `GA4_MEASUREMENT_ID=<valor-real>`
  - [ ] `GA4_LEGAL_APPROVED=true`
- [ ] Validar en navegador real que se carga `gtag.js` solo en produccion y solo con aprobacion legal activa.
- [ ] Verificar emision correcta de eventos publicos:
  - [ ] `page_view`
  - [ ] `hero_cta_click`
  - [ ] `event_ticket_click`
  - [ ] `youtube_channel_click`
  - [ ] `social_click`
- [ ] Comprobar en la propiedad GA4 que los eventos llegan con los parametros esperados y sin duplicados.
- [ ] Crear el commit final de cierre de la activacion controlada GA4 cuando todos los puntos anteriores queden verificados.

## Pendientes operativos y de release no-GA4

- [ ] Validar en el entorno objetivo el scheduler de Laravel y los workers dedicados que sostienen newsletter e integraciones asincronas.
- [ ] Validar en el entorno objetivo la conectividad real `Laravel <-> n8n` con `N8N_SHARED_SECRET`, `N8N_WEBHOOK_BASE_URL`, `N8N_API_KEY` y el usuario readonly sobre las vistas aprobadas.
- [ ] Validar en el entorno objetivo la conectividad real de `Ollama` y el modelo configurado si la automatizacion editorial va a quedar activa en produccion.
- [ ] Ejecutar la checklist final de rendimiento real sobre la home publica y rutas criticas para cerrar `CWV` (`LCP`, `INP`, `CLS`) antes del despliegue.
- [ ] Repetir el smoke final de seguridad en produccion: `composer audit`, cabeceras `CSP`, `HSTS`, `X-Frame-Options`, `X-Content-Type-Options` y rutas protegidas del backoffice.

## Ya cerrado y no pendiente

- [x] ~~La importacion masiva legacy/i18n ya esta implementada mediante `legacy:import-content`, cubre `events.json` + locales legacy y esta validada por `Phase5LegacyImportTest`.~~
- [x] ~~La base de automatizacion `n8n/Ollama` ya esta implementada en repo con servicios Docker, acciones firmadas, endpoints internos y cobertura de `Phase8ProductionAutomationTest`.~~
- [x] ~~El hardening base del backoffice ya se cerro en Fase 11 con headers de seguridad, proteccion de acceso, activity log y verificacion de calidad; lo que queda es validacion final de release y rendimiento real.~~

## Estado actual

- La base tecnica de GA4 ya esta implementada.
- La compuerta legal ya esta implementada.
- La alineacion dinamica del contenido legal ya esta implementada y validada por tests.
- La activacion real en produccion queda bloqueada hasta disponer del `GA4_MEASUREMENT_ID` y de la aprobacion legal final.
- La importacion legacy/i18n no queda como pendiente tecnico abierto.
- `n8n/Ollama` no quedan como pendiente de implementacion base; solo queda validacion operativa final si se van a activar en produccion.
- Seguridad base cerrada; queda el cierre final de `CWV` y el smoke de release en entorno real.

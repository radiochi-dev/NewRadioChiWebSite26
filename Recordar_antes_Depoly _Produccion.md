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

## Estado actual

- La base tecnica de GA4 ya esta implementada.
- La compuerta legal ya esta implementada.
- La alineacion dinamica del contenido legal ya esta implementada y validada por tests.
- La activacion real en produccion queda bloqueada hasta disponer del `GA4_MEASUREMENT_ID` y de la aprobacion legal final.

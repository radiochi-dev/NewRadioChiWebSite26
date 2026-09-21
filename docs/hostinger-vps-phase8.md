## Fase 8 - Hostinger VPS, n8n y Ollama

### Acceso al backoffice

- URL privada: `/backoffice/login`
- En local con Docker: `http://localhost:8080/backoffice/login`
- En produccion: `https://tu-dominio/backoffice/login`

Los super admin se provisionan desde `BACKOFFICE_SUPER_ADMINS` y se aplican con:

```bash
docker compose exec app php artisan db:seed --class=SuperAdminSeeder
```

### Stack de produccion

`docker-compose.production.yml` define:

- `radiochi_nginx`
- `radiochi_app`
- `radiochi_worker`
- `radiochi_scheduler`
- `radiochi_postgres`
- `radiochi_redis`
- `n8n`
- `n8n_worker`
- `n8n_postgres`
- `ollama`

Decisiones clave:

- Postgres y Redis no se exponen al exterior.
- `n8n` y `ollama` quedan en red privada.
- TLS se termina en el reverse proxy frontal del VPS o en Hostinger; Nginx interno sirve la app en `:80`.
- `radiochi_worker` y `radiochi_scheduler` separan colas y cron del proceso web.

### Base de datos separada para n8n

- `n8n_postgres` usa credenciales propias.
- No comparte base con `radiochi_postgres`.
- Las variables viven en `.env.production`.

### Acceso minimo n8n -> base principal

Se crean vistas aprobadas:

- `automation_public_events`
- `automation_newsletter_subscribers`
- `automation_public_pages_seo`

Y se provisiona el rol tecnico con:

```bash
docker compose exec radiochi_app php artisan automation:provision-database-access
```

Ese rol solo recibe `SELECT` sobre las vistas anteriores.

### Contrato Laravel <-> n8n

Endpoints internos protegidos por HMAC:

- `GET /api/internal/automation/public-home/{locale}`
- `GET /api/internal/automation/logs/{automationLog}`
- `POST /api/internal/automation/n8n/results`

Cabeceras requeridas:

- `X-Radiochi-Timestamp`
- `X-Radiochi-Signature`

Laravel puede disparar workflows firmados con:

```bash
docker compose exec radiochi_app php artisan automation:ping-n8n seo-refresh
```

### Ollama interno

- Servicio interno: `ollama`
- URL privada por defecto: `http://ollama:11434`
- Modelo configurable por `OLLAMA_MODEL`
- Consumo pensado para cola `automation`, no para bloquear peticiones web

### Operacion recomendada

1. Copiar `.env.production.example` a `.env.production`
2. Rellenar secretos reales
3. Levantar stack:

```bash
docker compose -f docker-compose.production.yml up -d --build
```

4. Ejecutar migraciones y seeders:

```bash
docker compose -f docker-compose.production.yml exec radiochi_app php artisan migrate --force
docker compose -f docker-compose.production.yml exec radiochi_app php artisan db:seed --class=SuperAdminSeeder --force
docker compose -f docker-compose.production.yml exec radiochi_app php artisan automation:provision-database-access
```

5. Validar:

- `/up`
- `/backoffice/login`
- colas en `radiochi_worker`
- scheduler en `radiochi_scheduler`
- `n8n`
- `ollama`

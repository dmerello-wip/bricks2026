# Getting Started

**initial setup**
`cp .env.example .env.local`
`ln -s .env.local .env`
`make init`

**start development mode**
`make dev`

**SSR Testing**
`make ssr`

_you should see in view-source the server side generated content_

**Regenerate OpenAPI types** (after editing PHP schemas)
`make swagger`

**Sync PHP translations → frontend JSON**
`make translations`

**Regenerate all generated types** (translations + swagger)
`make types`

**log di mysql if needed**
`sail logs -f mysql`

# Directories

## General

- `/public` => resources served as is
- `/scripts` => command line scripts for general repository/project/environments maintainance.

## Front-End

- `resources/views` => Twill blocks configurations
- `resources/css/app.css` => Tailwind configurations
- `resources/js`
    - `/components` => React component root
        - `/editorial` => All editorial components and their `atoms`.
        - `/form` => All form components and preconfigured `fields`
        - `/layout` => Layout components and their dependencies, grouped by section/area/domain.
        - `/ui` => General ui components (buttons, links, inputs, ...)
    - `/pages` => Inertia-React controller components tree. Should reflect application routing and contain route specific React components.
    - `/lib` => Non react application code, possibly grouped by section/area/domain
        - `/types` => Global types
        - `/utils.ts` => Global utilities


---

# Deployment (Coolify)

## Variabili d'ambiente di produzione

Impostare nell'env panel di Coolify (o nel `.env` iniettato da Coolify):

```env
APP_NAME=bricks2026
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:...          # php artisan key:generate --show
APP_URL=https://yourdomain.com

# Database (MySQL 8.4 gestito da Coolify)
DB_CONNECTION=mysql
DB_HOST=<coolify-mysql-host>
DB_PORT=3306
DB_DATABASE=bricks2026
DB_USERNAME=bricks2026
DB_PASSWORD=<secret>

# Drivers (database-backed, no Redis necessario)
CACHE_STORE=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database

# SSR (punta al servizio `ssr` nel Docker network)
INERTIA_SSR_ENABLED=true
INERTIA_SSR_URL=http://ssr:13714

# Logging (stderr per Coolify log aggregation)
LOG_CHANNEL=stderr
LOG_LEVEL=error

# Mail (configurare con il proprio provider SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

## Setup Coolify

1. **Crea una nuova risorsa** in Coolify → "Docker Compose" → punta al repository Git.
2. **Imposta il compose file** su `compose.prod.yaml` (non `compose.yaml`).
3. **Crea un database MySQL 8.4** gestito in Coolify → copia le credenziali nelle env var.
4. **Verifica il volume** `app_storage` — Coolify lo rileva automaticamente dal compose file (tab "Storages").
5. **Imposta le variabili d'ambiente** come da elenco sopra.
6. **Imposta il Post-deployment command**: `bash /var/www/html/scripts/deploy.sh`
7. **Deploy**.

## First-deploy checklist

- [ ] Database MySQL creato in Coolify e credenziali impostate nell'env
- [ ] `APP_KEY` generato e impostato (non riusare mai la chiave di sviluppo)
- [ ] `APP_URL` corrisponde al dominio configurato in Coolify (necessario per gli URL Twill)
- [ ] Volume `app_storage` collegato
- [ ] Post-deploy command impostato nella UI Coolify
- [ ] Dopo il primo deploy: creare il primo utente admin Twill con `php artisan twill:superadmin`
- [ ] Verificare che `/up` risponda HTTP 200
- [ ] Verificare upload immagine in Twill → crop funzionante (testa l'intera pipeline Glide)

## Deploy successivi

- Push al branch monitorato da Coolify → redeploy automatico
- `scripts/deploy.sh` esegue le migration automaticamente (`--force`)
- Zero-downtime: Coolify aspetta il health check su `/up` prima di instradare il traffico

## Test build produzione in locale

```bash
make build-prod     # Builda l'immagine Docker di produzione
make run-prod       # Avvia lo stack prod locale
make deploy-local   # Build + start + esegui deploy hooks
```

---

# DOCUMENTATION

Read Documentation in **DOCS/{single-topic}.md**

- [Image Cropping System](DOCS/cropping.md)
- [Frontend General](DOCS/frontend-general.md)
- [ImageService Usage](DOCS/image-service-usage.md)
- [Navigation System](DOCS/navigations.md)
- [OpenAPI Type Generation](DOCS/openapi-types.md)
- [Block Editor Preview with Inertia/React](DOCS/preview-with-inertia.md)
- [SEO](DOCS/seo.md)
- [Translations](DOCS/translations.md)
- [Debugging PHP (Xdebug)](DOCS/debugging-php.md)
- [Todo](DOCS/todo.md)


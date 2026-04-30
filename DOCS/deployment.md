# Deployment

Documento di riferimento per la pipeline di deploy in produzione (Coolify) di bricks2026. Coppia delle istruzioni utente nel [README](../README.md#deployment-coolify) — qui c'è la **logica interna** del build, non la procedura operativa Coolify.

---

## Stack di produzione

| Componente | Tecnologia |
|---|---|
| Build orchestrator | Docker Compose (compose.prod.yaml) gestito da Coolify |
| Web server | nginx (Alpine) |
| PHP runner | php-fpm 8.4 (Alpine) |
| Process manager | supervisord (entrambi nel container `app`) |
| Asset bundler | Vite (client + SSR bundle) |
| SSR runtime | Node 22 (container dedicato) |
| Database | MySQL 8.4 (servizio Coolify esterno al compose) |
| Cache / session / queue | driver `database` (no Redis) |

---

## Dockerfile multi-stage

Il [Dockerfile](../Dockerfile) ha 4 stage. Ognuno produce solo quello che serve allo stage successivo: il runtime finale è snello.

```
┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│  php-base    │     │ node-builder │     │   runtime    │ ← target di
│ php:8.4-cli  │ ──▶ │ node:22      │ ──▶ │ php:8.4-fpm  │   app/queue/
│              │     │              │     │ + nginx      │   scheduler
└──────────────┘     └──────────────┘     └──────────────┘
       │                                          
       │                                   ┌──────────────┐
       └──────────────────────────────────▶│     ssr      │ ← target di
                                           │   node:22    │   ssr
                                           └──────────────┘
```

### Stage 1 — `php-base`
Base: `php:8.4-cli-alpine`. Composer install `--no-dev`, autoload ottimizzato, e poi:
- `php artisan translation-handler:import --force --fresh` → genera `resources/js/lang/*.json` (gitignored)
- `php artisan wayfinder:generate --with-form` → genera `resources/js/{actions,routes,wayfinder}/*.ts` (gitignored)

L'`ENV APP_KEY` dummy serve solo a far bootstrappare l'app per questi due comandi: la chiave reale arriva a runtime via env Coolify.

### Stage 2 — `node-builder`
Base: `node:22-alpine`. `npm ci` + Vite build SSR. Copia dai file generati nello stage 1:

```
COPY --from=php-base /app/resources/js/lang     ./resources/js/lang
COPY --from=php-base /app/resources/js/actions  ./resources/js/actions
COPY --from=php-base /app/resources/js/routes   ./resources/js/routes
COPY --from=php-base /app/resources/js/wayfinder ./resources/js/wayfinder
```

Build con `WAYFINDER_SKIP=1` per evitare che il Vite plugin Wayfinder ri-esegua `php artisan wayfinder:generate` (i file sono già copiati). Output:
- `public/build/` (manifest + asset client con hash)
- `bootstrap/ssr/ssr.js` (SSR bundle Node)

### Stage 3 — `runtime`
Base: `php:8.4-fpm-alpine`. È l'image che gira come `app`, `queue` e `scheduler` in compose.prod.yaml.

Step rilevanti:
1. `apk add nginx supervisor curl bash` + dipendenze GD/intl/zip (build deps in virtual package, rimosse a fine compilazione estensioni).
2. `docker-php-ext-install` per `pdo_mysql mbstring gd exif pcntl zip bcmath intl opcache`.
3. OPcache config (`validate_timestamps=0`, 256MB) → `/usr/local/etc/php/conf.d/opcache.ini`.
4. nginx riconfigurato per girare come utente `www-data` (allineato a php-fpm) via `sed` su `/etc/nginx/nginx.conf`.
5. Vhost `docker/nginx.conf` → `/etc/nginx/http.d/default.conf`.
6. Process tree `docker/supervisord.conf` → `/etc/supervisor/conf.d/supervisord.conf` (lancia `php-fpm -F` e `nginx -g "daemon off;"`).
7. `COPY --from=php-base /app .` → app PHP completa (vendor, codice, file generati).
8. `COPY --from=node-builder /app/public/build` → bundle Vite client.
9. `php artisan vendor:publish --provider="A17\Twill\TwillServiceProvider" --tag=assets --force` → pubblica gli asset admin Twill da `vendor/area17/twill/twill-assets/` a `public/assets/twill/`. **Vedi nota più sotto sul perché NON usiamo `twill:build`.**
10. chown `storage/` e `bootstrap/cache/` su `www-data:www-data`, chmod 775.
11. `CMD supervisord -c …` → entrypoint del container.

### Stage 4 — `ssr`
Base: `node:22-alpine`. Solo `bootstrap/ssr/` copiato dallo stage 2. `CMD ["node", "bootstrap/ssr/ssr.js"]`. Espone 13714.

---

## Servizi `compose.prod.yaml`

| Servizio | Target Dockerfile | Comando | Note |
|---|---|---|---|
| `app` | `runtime` | (default `CMD` = supervisord → nginx + php-fpm) | Healthcheck `curl /up`. Volume `app_storage` su `storage/`. |
| `queue` | `runtime` | `php artisan queue:work --sleep=3 --tries=3 --max-time=3600` | Sovrascrive il `CMD`: niente nginx in questo container. |
| `scheduler` | `runtime` | `sh -c "while true; do php artisan schedule:run --no-interaction; sleep 60; done"` | Idem: niente nginx. |
| `ssr` | `ssr` | `node bootstrap/ssr/ssr.js` (default `CMD`) | Espone 13714, healthcheck `wget /`. |

`queue` e `scheduler` riusano lo stesso target `runtime` e quindi includono nginx/supervisor inutilizzati in image. Trade-off accettato: una sola image PHP da costruire e mantenere. Se in futuro l'image cresce in modo problematico, si può estrarre uno stage `worker` ridotto.

---

## Asset admin Twill — `vendor:publish`, NON `twill:build`

Il comando `twill:build` ([vendor/area17/twill/src/Commands/Build.php](../vendor/area17/twill/src/Commands/Build.php)) **compila da sorgenti** Vue/JS dentro `vendor/area17/twill/` (`npm ci` + `npm run build`) e poi copia il risultato in `public/`. Richiede `node` e `npm`, **non disponibili nello stage runtime** (php:8.4-fpm-alpine).

Twill distribuisce gli asset admin **già precompilati** in `vendor/area17/twill/twill-assets/`, pubblicabili via:

```bash
php artisan vendor:publish --provider="A17\Twill\TwillServiceProvider" --tag=assets --force
```

Questo è il comando usato nel Dockerfile.

> **Limitazione**: se un giorno il progetto introducesse custom Vue blocks/components admin Twill (es. `resources/assets/js/blocks/*.vue`), questi richiedono un build effettivo e quindi `twill:build`. In tal caso bisognerà spostarne l'esecuzione nello stage `node-builder` (che ha node) e copiare `vendor/area17/twill/dist/` nello stage runtime. Oggi il progetto usa solo blocchi React/Inertia, quindi non serve.

---

## Wayfinder e translations: dove vengono generati

Sono entrambi **gitignored** ([resources/js/lang/](../resources/js/lang/), [resources/js/actions/](../resources/js/actions/), [resources/js/routes/](../resources/js/routes/), [resources/js/wayfinder/](../resources/js/wayfinder/)) e devono esistere prima del Vite build.

Flow:
1. Stage `php-base` esegue i due comandi artisan dopo `COPY . .` → file generati dentro l'image `php-base`.
2. Stage `node-builder` li copia con `COPY --from=php-base ...` prima di `npm run build:ssr`.
3. Vite consuma i file generati. Il Wayfinder Vite plugin viene **disattivato** in build via `WAYFINDER_SKIP=1` (vedi [vite.config.ts:28-30](../vite.config.ts#L28-L30)) per evitare doppia esecuzione.

In locale invece il Wayfinder plugin gira live ad ogni dev server start (`npm run dev`), perché nel container Sail `php artisan` è disponibile.

---

## SSR Inertia

Il rendering SSR avviene in un container Node dedicato che espone la porta 13714 sulla rete interna Docker. La config Laravel ([config/inertia.php](../config/inertia.php)):

```php
'ssr' => [
    'enabled' => env('INERTIA_SSR_ENABLED', true),
    'url' => env('INERTIA_SSR_URL', 'http://127.0.0.1:13714'),
    'bundle' => base_path('bootstrap/ssr/ssr.js'),
],
```

In produzione settare `INERTIA_SSR_URL=http://ssr:13714` (DNS Docker risolve `ssr` al servizio compose). Il bundle `bootstrap/ssr/ssr.js` esiste solo nell'image `ssr` (lo stage runtime non ne ha bisogno: parla via HTTP al servizio `ssr`).

---

## Post-deploy hook

[scripts/deploy.sh](../scripts/deploy.sh) — da configurare in Coolify "Post-deployment command":

```bash
bash /var/www/html/scripts/deploy.sh
```

Esegue, in ordine:
1. `php artisan migrate --force`
2. `php artisan storage:link --force`
3. `php artisan config:cache`
4. `php artisan route:cache`
5. `php artisan view:cache`
6. `php artisan event:cache`

Eseguito dopo che il container `app` ha passato l'healthcheck `/up`. Tutto lo stato applicativo è già pronto a quel punto: gli asset admin Twill sono pubblicati al build time, non qui.

---

## Variabili d'ambiente

Vedi [README · Variabili d'ambiente di produzione](../README.md#variabili-dambiente-di-produzione) per la lista completa.

Nota Coolify: la variabile `APP_ENV=production` può triggerare un warning al build time ("may affect dependency installation"). Il Dockerfile usa già `composer install --no-dev` esplicito, quindi è safe. Per silenziare il warning: marcare `APP_ENV` come "Runtime only" nel pannello env di Coolify.

---

## Smoke test locale

Stesso stack che gira su Coolify (escluso il database — va creato a parte se si vuole testare l'app fino in fondo).

```bash
make build-prod              # build dell'image Dockerfile target=runtime
make run-prod                # avvia compose.prod.yaml in locale
docker compose -f compose.prod.yaml ps                                       # tutti healthy
docker compose -f compose.prod.yaml exec app ls public/assets/twill          # css/, js/, fonts/, twill-manifest.json
docker compose -f compose.prod.yaml exec app ps -ef | grep -E "nginx|php-fpm" # entrambi presenti
docker compose -f compose.prod.yaml exec app curl -fsS http://localhost/up   # 200
make deploy-local            # esegue scripts/deploy.sh dentro app
```

---

## Troubleshooting

### Build fallisce su `vendor:publish` dei Twill assets
Verificare che `vendor/area17/twill/twill-assets/` esista nello stage runtime (deve essere stato copiato da `php-base` via `COPY --from=php-base /app .`). Se manca, controllare che `composer install --no-dev` nello stage 1 includa `area17/twill`.

### 502 Bad Gateway dal container `app`
nginx non riesce a parlare con php-fpm. Probabili cause:
- php-fpm non è up — `docker exec <app> ps -ef | grep php-fpm`
- pool php-fpm non in ascolto su 9000 — controllare `/usr/local/etc/php-fpm.d/www.conf` (dovrebbe essere `listen = 9000` di default nell'image ufficiale `php:8.4-fpm-alpine`)
- supervisord ha riavviato troppe volte uno dei due processi — log con `docker logs <app>`

### Healthcheck `/up` fallisce
- Curl non installato nel container → `apk add curl` (è già incluso nel Dockerfile)
- L'app non bootstrappa per env mancanti — `docker compose logs app` mostrerà l'eccezione Laravel

### File scrivibili — permessi
nginx e php-fpm girano entrambi come `www-data`. `storage/` e `bootstrap/cache/` sono chown `www-data:www-data` chmod 775 nel build. Il volume `app_storage` (named volume per `storage/`) eredita il chown alla prima creazione; se viene creato vuoto e poi il container scrive come root, possono nascere mismatch. In quel caso: `docker exec app chown -R www-data:www-data /var/www/html/storage`.

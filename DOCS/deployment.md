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
Base: `php:8.4-cli-alpine` con `nodejs` + `npm` aggiunti via `apk` (servono al `twill:build`). Composer install `--no-dev`, autoload ottimizzato, e poi:
- `php artisan translation-handler:import --force --fresh` → genera `resources/js/lang/*.json` (gitignored)
- `php artisan wayfinder:generate --with-form` → genera `resources/js/{actions,routes,wayfinder}/*.ts` (gitignored)
- `php artisan twill:build` → compila gli asset admin Twill (incluse eventuali custom Vue blocks/components da `resources/assets/js/{blocks,components}`) e li pubblica in `public/assets/twill/`. Subito dopo il build, `vendor/area17/twill/node_modules` e `dist/` vengono rimossi nello stesso layer per non gonfiare l'immagine.

L'`ENV APP_KEY` dummy serve solo a far bootstrappare l'app per questi comandi: la chiave reale arriva a runtime via env Coolify.

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
9. chown `storage/` e `bootstrap/cache/` su `www-data:www-data`, chmod 775.
10. `CMD supervisord -c …` → entrypoint del container.

Gli asset admin Twill non vengono pubblicati qui: arrivano già pronti in `public/assets/twill/` dal `COPY --from=php-base` perché `twill:build` è stato eseguito nello stage 1 (vedi sezione dedicata).

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

## Asset admin Twill — `twill:build` nello stage `php-base`

Usiamo lo stesso comando del workflow locale (`make init` → `php artisan twill:build`), così se in futuro vengono introdotti custom Vue blocks/components per l'admin Twill (`resources/assets/js/blocks/*.vue`, `resources/assets/js/components/*.vue`) la pipeline li raccoglie automaticamente.

`twill:build` ([vendor/area17/twill/src/Commands/Build.php](../vendor/area17/twill/src/Commands/Build.php)):
1. esegue `npm ci` dentro `vendor/area17/twill/` (richiede node/npm)
2. copia i custom Vue blocks/components nelle directory sorgenti di Twill
3. esegue `npm run build` → produce `vendor/area17/twill/dist/`
4. chiama `twill:update --fromBuild` che copia `dist/` → `public/assets/twill/`

Per evitare che `vendor/area17/twill/node_modules/` (200-500 MB di toolchain Vue/webpack) finisca nell'immagine finale, dopo il build facciamo `rm -rf` di `node_modules` e `dist` nello stesso layer Docker. Solo `public/assets/twill/` viene preservato e raggiunge il runtime tramite il `COPY --from=php-base /app .`.

Trade-off: +1-2 minuti al build time per `npm ci` e `npm run build` di Twill, ammortizzati dal layer cache se `composer.lock` non cambia. Lo stage `runtime` resta snello (no node, no toolchain Vue).

### Alternativa — solo asset stock
Twill distribuisce anche asset admin **già precompilati** in `vendor/area17/twill/twill-assets/` (`vendor:publish --tag=assets`), che è la strada più veloce ma ignora le tue customizzazioni Vue. Nel progetto bricks2026 attualmente non ci sono custom Vue admin, quindi tecnicamente sarebbe equivalente — ma manteniamo `twill:build` come default per essere future-proof.

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

### Build fallisce su `twill:build` (stage `php-base`)
- `npm ci` di Twill che fallisce: di solito è una mismatch di versione node. Lo stage installa `nodejs npm` da `apk` di Alpine (versione corrente del repository alpine). Se Twill in futuro richiedesse una versione node specifica, switchare al package `nodejs-current` o installare un major node fisso.
- `npm run build` che fallisce su un custom Vue block: errore di sintassi/import nel `.vue` — riproducibile in locale con `make init` o `vendor/bin/sail artisan twill:build`.
- Se vuoi temporaneamente bypassare il build Twill (es. per isolare un altro errore), commentare la riga `RUN php artisan twill:build ...` nel Dockerfile e ripristinare `vendor:publish --tag=assets` nello stage runtime — gli asset stock dell'admin sono comunque distribuiti dal pacchetto.

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

# Revisione DevOps — Fix puntuali al setup Coolify

## Context

Valutazione della deployment stack Coolify (Dockerfile + compose.prod.yaml) contro le linee guida https://coolify.io/docs/applications/laravel.

**Verdetto**: l'architettura attuale (4 container: app nginx+php-fpm via supervisord, queue, scheduler, ssr) è ben progettata e in più punti migliore del default Coolify (multi-stage build sofisticato, opcache production-tuned, fastcgi buffer dimensionati per Inertia/Twill, build deps rimosse via virtual package). **Non c'è overengineering significativo da rimuovere.**

Tre problemi reali da correggere senza toccare l'architettura:
1. Healthcheck SSR sempre verde (bug — annulla il `depends_on` in app)
2. Scheduler hand-rolled invece dell'idiomatico `schedule:work`
3. Entrypoint pulisce le cache ma non rigenera l'optimize (Coolify lo raccomanda)

Scope scelto: **solo fix puntuali, nessun consolidamento di container.**

---

## Modifiche

### 1. Fix healthcheck SSR — [compose.prod.yaml:90-95](../../compose.prod.yaml#L90-L95)

**Problema**: `wget -qO- http://localhost:13714 || exit 0` fa sempre passare il check.

**Fix**: rimuovere `|| exit 0` e usare un endpoint ragionevole. Inertia SSR risponde su `/` con il render server-side; basta verificare che il socket sia raggiungibile e risponda HTTP. Usare `wget --spider` o `wget -q -O /dev/null` con failure naturale.

```yaml
healthcheck:
    test: ['CMD-SHELL', 'wget -q -O /dev/null http://localhost:13714/health || exit 1']
    interval: 15s
    timeout: 5s
    retries: 3
    start_period: 10s
```

Nota: Inertia SSR (>=2.x) espone `/health` che ritorna 200 OK. Se per qualche motivo non fosse disponibile su questa versione, fallback su `/` (il server risponde comunque, anche con 422 — basta che non vada in connection refused). Verificare con un quick check del bundle SSR generato.

### 2. Sostituire scheduler loop con `schedule:work` — [compose.prod.yaml:62-66](../../compose.prod.yaml#L62-L66)

**Prima**:
```yaml
command: >
    sh -c "while true; do php artisan schedule:run --no-interaction; sleep 60; done"
```

**Dopo**:
```yaml
command: php artisan schedule:work
```

`schedule:work` è disponibile in Laravel 11/12 (questo progetto è L12), gestisce internamente il loop al minuto, intercetta SIGTERM correttamente (graceful shutdown su redeploy), evita esecuzioni sovrapposte. Adattare l'healthcheck di conseguenza non serve — `php -r 'exit(0);'` continua a funzionare.

### 3. Rigenerare cache dopo migrate — [docker/entrypoint.sh](../../docker/entrypoint.sh)

**Stato attuale** ([docker/entrypoint.sh:11-15](../../docker/entrypoint.sh#L11-L15)):
```bash
php artisan optimize:clear --no-interaction || true
php artisan migrate --force --no-interaction
php artisan storage:link --force --no-interaction || true
exec /usr/bin/supervisord ...
```

**Aggiungere dopo `migrate`, prima di `supervisord`**:
```bash
echo "[entrypoint] Rebuilding application caches..."
php artisan optimize --no-interaction || true
```

Questo esegue `config:cache + route:cache + view:cache + event:cache`. Con `opcache.validate_timestamps=0` settato nel Dockerfile, le cache compilate stanno in memoria stabile fino al prossimo deploy. Allinea al post-deploy raccomandato da Coolify.

**Nota**: NON fare `optimize:clear` ridondante se poi rigeneri subito. La sequenza giusta è `clear` (per rimuovere cache stale dal build container con APP_KEY dummy) → `migrate` → `optimize` (per avere cache fresche con env reali).

---

## File da modificare

- [compose.prod.yaml](../../compose.prod.yaml) — healthcheck ssr + command scheduler
- [docker/entrypoint.sh](../../docker/entrypoint.sh) — aggiungere `php artisan optimize`

Nessuna modifica a Dockerfile, .dockerignore, nginx.conf, supervisord.conf.

---

## Verification

Locale (su macchina dev con compose.prod.yaml):
1. `docker compose -f compose.prod.yaml build` → verificare che il build completi senza warning nuovi.
2. `docker compose -f compose.prod.yaml up -d` → tutti i 4 container devono diventare `healthy`.
3. **Test healthcheck SSR**: `docker compose -f compose.prod.yaml stop ssr` → `docker inspect <app_container> --format '{{.State.Health.Status}}'` dopo 30s deve riflettere correttamente lo stato (prima del fix sarebbe rimasto verde).
4. **Test scheduler**: `docker compose -f compose.prod.yaml logs scheduler` → deve mostrare output di `schedule:work` con tick al minuto e nessun loop bash visibile.
5. **Test entrypoint optimize**: `docker compose -f compose.prod.yaml logs app` al primo boot → deve mostrare `[entrypoint] Rebuilding application caches...` e `Configuration cached successfully`, `Routes cached successfully`, ecc.

Su Coolify:
6. Redeploy della release → verificare che lo start-period non venga superato (tutti healthy entro i timeout esistenti).
7. Smoke test: home page, una rotta Twill admin, una rotta Inertia con SSR attivo.

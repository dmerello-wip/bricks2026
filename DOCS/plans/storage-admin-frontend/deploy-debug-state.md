# Stato debug deploy Coolify

Snapshot al **2026-05-09** post sessione di debug intensiva. La causa radice del 500 sull'upload media è stata identificata e si è rivelata diversa da quella ipotizzata: **il valore della variabile d'ambiente `MEDIA_LIBRARY_IMAGE_SERVICE` in Coolify contiene doppi backslash**, non singoli — quindi non corrisponde a nessuna chiave del classmap di Composer.

## Catena di problemi

### 1. Build Coolify falliva (exit 255 a ~2:30 min) — RISOLTO

- **Causa**: `docker-php-ext-install -j$(nproc)` produceva troppo output rapido. Coolify ha un callback per riga di stdout che fa `json_decode(tutti_i_log) → json_encode → UPDATE postgres` (O(n²)). PHP non sta dietro al ritmo di gcc parallelo, la pipe del kernel (~64KB) si satura, Docker daemon riceve `EPIPE`, `docker exec` esce con 255.
- **Fix** (commit `5e8fa67`): `> /dev/null` su `docker-php-ext-configure` e `docker-php-ext-install` in entrambi gli stage del [Dockerfile](../../../Dockerfile). Stderr resta attivo per non perdere errori reali.

### 2. nginx 502 "upstream sent too big header" — RISOLTO

- **Causa**: header HTTP di Laravel/Twill (cookie sessione + XSRF + Inertia version + Set-Cookie multipli) superano il `fastcgi_buffer_size` default di 4KB.
- **Fix** (commit `656347e`): in [docker/nginx.conf](../../../docker/nginx.conf):
    ```
    fastcgi_buffer_size 32k;
    fastcgi_buffers 16 32k;
    fastcgi_busy_buffers_size 64k;
    ```

### 3. Gateway Timeout su HTTPS — IN PRODUZIONE LO RIVEDIAMO AD OGNI REDEPLOY

Sintomi: TCP/TLS OK, log nginx interno mostra 200/302, ma `curl https://new.bricksmusicfestival.com` timeout 30s. Si è ripresentato dopo ogni `git push` che triggera redeploy Coolify.

**Fix riproducibile**: `docker restart coolify-proxy` da SSH al server. Risolve in 8s. È discovery zombie di Traefik dopo lo swap container.

Da considerare: hookarlo come step automatico post-deploy in Coolify, oppure riconfigurare Traefik perché re-discovery fluido.

### 4. Upload media → 500 — CAUSA RADICE IDENTIFICATA, FIX = correggere env

#### Sintomo

In admin Twill → Media Library → upload qualsiasi immagine → "XHR returned response code 500", upload error. Prima della richiesta upload, anche `GET /admin/media-library/medias?page=1&type=image` torna 500.

Errori in log:
```
production.ERROR: Target class [App\Services\MediaLibrary\Glide] does not exist.
production.ERROR: Cannot redeclare class App\Services\MediaLibrary\Glide
```

#### Causa radice (finalmente identificata)

La variabile d'ambiente in Coolify era stata digitata **con doppi backslash**:

```
MEDIA_LIBRARY_IMAGE_SERVICE=App\\Services\\MediaLibrary\\Glide
```

Verifica via tinker dentro il container, hex dump:
```
$ docker exec <app> php -r 'echo bin2hex(env("MEDIA_LIBRARY_IMAGE_SERVICE"));'
4170705c5c5365727669636573...   ← ogni separatore di namespace è 5c 5c (due byte)
```

Le chiavi del classmap di Composer (`vendor/composer/autoload_classmap.php`) sono invece con backslash singolo: `App\Services\MediaLibrary\Glide` → ovvero `App\\Services\\MediaLibrary\\Glide` nel file PHP, ma rappresentano un FQCN con UN backslash per separatore. La stringa che arriva da `config('twill.media_library.image_service')` ha letteralmente DUE backslash → non matcha → `class_exists()` torna false → `app->make()` lancia `BindingResolutionException`.

#### Perché Laravel `.env` è sensibile

I file `.env` di Laravel (vlucas/phpdotenv) **non interpretano escape**: il valore è preso letteralmente. Se in Coolify scrivi `App\\Services\\MediaLibrary\\Glide`, l'env conterrà `App\\Services\\MediaLibrary\\Glide` (10 caratteri di "escape" letterali). Per ottenere un FQCN PHP corretto bisogna scrivere `App\Services\MediaLibrary\Glide` con backslash singoli — anche se "sembra strano" perché in PHP source code i FQCN nelle stringhe vanno raddoppiati.

[`.env.example`](../../../.env.example) infatti mostra il formato corretto:
```
MEDIA_LIBRARY_IMAGE_SERVICE=App\Services\MediaLibrary\Glide
```

#### Verifica diagnostica (riproducibile in qualsiasi container)

```bash
APP=$(docker ps --format '{{.Names}}' | grep '^app-qf8593940' | head -1)

# Stampa la stringa env con hex dump per beccare i doppi backslash
docker exec "$APP" php -r '
$v = env("MEDIA_LIBRARY_IMAGE_SERVICE");
echo "raw: [$v]\nlen: " . strlen($v) . "\nhex: " . bin2hex($v) . "\n";
'
# attesi 5c singoli (es. "...4d65646961...5c47..."); doppi 5c 5c = malformato

# Test resolve via container
docker exec "$APP" php artisan tinker --no-interaction --execute '
    echo get_class(app()->make(config("twill.media_library.image_service")));
'
# se torna "App\\Services\\MediaLibrary\\Glide" → tutto bene
# se "Target class [...] does not exist" → controlla i bytes dell'env
```

#### Fix

In Coolify, pannello Environment Variables del progetto, modificare:

```
MEDIA_LIBRARY_IMAGE_SERVICE=App\Services\MediaLibrary\Glide
```

Con backslash **singoli**. Salvare e fare redeploy.

> Probabilmente serve anche cancellare il valore esistente e riscrivere a mano, perché alcuni editor web normalizzano i backslash (li raddoppiano "per sicurezza" pensando di fare escape JSON). Dopo il salvataggio, verificare che il valore mostrato nel pannello sia con un solo `\` per separatore.

#### Coolify raddoppia i backslash in env injection — CONFERMATO

L'utente ha confermato: nel pannello Coolify il valore era scritto con **un solo** backslash (`App\Services\MediaLibrary\Glide`). Eppure `printenv` dentro il container mostra:

```
$ docker exec <app> sh -c 'printenv MEDIA_LIBRARY_IMAGE_SERVICE | xxd | head -2'
00000000: 4170 705c 5c53 6572 7669 6365 735c 5c4d  App\\Services\\M
00000010: 6564 6961 4c69 6272 6172 795c 5c47 6c69  ediaLibrary\\Gli
```

Bytes `5c 5c` = due backslash effettivi tra ciascun segmento namespace. **È Coolify** che raddoppia all'iniezione (probabile shell escape per `docker run --env`).

#### Fix applicato in [config/twill.php](../../../config/twill.php)

```php
'image_service' => preg_replace('/\\\\+/', '\\\\',
    env('MEDIA_LIBRARY_IMAGE_SERVICE', 'A17\Twill\Services\MediaLibrary\Glide')
),
```

Collassa qualsiasi run di `\` consecutivi in uno solo. Verificato in produzione con hot-patch `docker cp` + `php artisan config:cache` + `kill -USR2`:

```
hex: 4170705c53657276696365735c4d656469614c6962726172795c476c696465
contains 5c5c: NO ok
class_exists: YES
resolve: App\Services\MediaLibrary\Glide  ✓
```

Stesso pattern va applicato a qualsiasi altra env che si aspetti un FQCN (al momento solo `image_service` nel progetto, ma se in futuro si aggiungono altre — es. `MEDIA_LIBRARY_FILE_SERVICE` se dovesse essere usato — applicare il preg_replace.

#### Cosa succedeva con l'env malformato — analisi a posteriori

L'errore `Target class does not exist` è la conseguenza diretta: classmap lookup fallisce su nome con doppi backslash.

L'errore `Cannot redeclare class` osservato sotto al guard rotto era anche lui figlio dello stesso bug:

1. Il file [app/Services/MediaLibrary/Glide.php](../../../app/Services/MediaLibrary/Glide.php) conteneva un guard `if (class_exists(__NAMESPACE__ . '\\Glide', false)) { error_log(...); return; }` aggiunto in passato per debuggare un "Cannot redeclare" precedente.
2. Quel guard è scritto in un modo PHP-rotto: una `class Foo extends Bar { }` top-level non condizionale viene **registrata da PHP durante il parse del file** (per supportare forward reference e early binding). `class_exists($name, false)` ritorna `true` durante quel parsing, prima che il flow runtime raggiunga la `class` statement → guard scattava sempre al primo include → `return;` → la classe non veniva mai dichiarata runtime → Container fallisce.
3. Tolto il guard, in FPM il file finiva incluso due volte nello stesso request (per via di ReflectionClass chiamato in 2 punti del Container Laravel — `getConcreteBindingFromAttributes` a riga 1000 e `build()` a riga 1122 — su un nome che fallisce comunque l'autoload, ognuno re-tentativa l'include). Da qui "Cannot redeclare".

In altre parole: con env corretto, `app->make()` risolve normalmente al primo colpo, file incluso UNA volta, classe dichiarata, niente errore. **Tutti i sintomi precedenti erano effetti collaterali del lookup fallito.**

#### Stato attuale del file `app/Services/MediaLibrary/Glide.php`

Durante il debug è stato modificato più volte (rimozione guard, safety net, preload parent, log diagnostico). **Il repo contiene ora una versione con safety net + diagnostica che NON è quella che vogliamo committare.** Da fare:

```bash
git checkout app/Services/MediaLibrary/Glide.php
```

per ripristinare la versione pulita pre-debug. Il guard originale era anch'esso un workaround sbagliato — andrebbe valutato di rimuoverlo definitivamente al prossimo passaggio sul file. Ma prima sistemare l'env e verificare che l'upload va con il file ORIGINALE.

#### Hot-patch in produzione

Il container in prod ha ricevuto vari `docker cp` durante la sessione + `kill -USR2` su master php-fpm per refresh OPcache (`validate_timestamps=0`). **Tutto sarà sovrascritto al prossimo deploy** dal Dockerfile. Quindi nessun cleanup necessario lato server, basta che il deploy parta dal repo pulito.

### 5. Webp not supported by PHP installation — IN FIX

Dopo aver risolto il punto 4, l'upload media inizia a generare:
```
production.ERROR: Webp format is not supported by PHP installation.
  Intervention\Image\Exception\NotSupportedException at vendor/intervention/image/src/Intervention/Image/Gd/Encoder.php:68
```

**Causa**: in [Dockerfile](../../../Dockerfile) `docker-php-ext-configure gd` aveva `--with-freetype --with-jpeg` ma **mancava `--with-webp`**, e nei pacchetti apk mancavano `libwebp-dev` (build) e `libwebp` (runtime). `MEDIA_LIBRARY_DEFAULT_FORMAT=webp` (nell'env) attiva un encoder WebP che GD non sa fare.

**Fix applicato in entrambi gli stage del Dockerfile** (`php-base` + `runtime`):
- aggiunto `libwebp-dev` ai build deps
- aggiunto `libwebp` ai runtime deps (solo nel runtime stage)
- aggiunto `--with-webp` a `docker-php-ext-configure gd`

Non fixabile via hot-patch (richiede ricompilazione di GD, e quindi rebuild dell'image). **Richiede un nuovo deploy** dopo `git push`.

## Bug separato: route Glide `/img/...`

Il vecchio doc segnalava come bug il fatto che Twill registra la route Glide solo se `image_service === A17\Twill\...\Glide::class` (confronto stretto col parent). Verificato il 2026-05-09: **non è un bug per questo progetto.**

- `config/twill.php:86` sovrascrive `twill.glide.base_path` con `IMAGE_CACHE_PATH` (default `storage/img/crops`).
- `routes/web.php:24` registra `Route::get($cachePath.'/{path}', [ImageCropperController::class, 'processImage'])`.
- `App\Http\Controllers\ImageCropperController` è un sostituto deliberato del `GlideController` di Twill: parsea filename con params hex-encoded (`nome__<hex>.jpg`) e applica le trasformazioni con `Spatie\Glide\GlideImage`.

Registrare manualmente la route `GlideController` di Twill come suggerito dal doc precedente avrebbe causato collisione di route + il `GlideController` di Twill non sa parsare il formato URL custom. **Il fix proposto era dannoso.** Nessuna azione necessaria.

## TODO

- [x] Fix in [config/twill.php](../../../config/twill.php) (preg_replace per collassare run di backslash).
- [x] Rimosso il guard storico rotto da [app/Services/MediaLibrary/Glide.php](../../../app/Services/MediaLibrary/Glide.php) (non serve più ora che la causa radice è chiara).
- [x] Fix Dockerfile (`libwebp-dev`, `libwebp`, `--with-webp`) in entrambi gli stage.
- [ ] **Commit + push** delle 3 modifiche (`Dockerfile`, `config/twill.php`, `app/Services/MediaLibrary/Glide.php`).
- [ ] Redeploy.
- [ ] **Restart `coolify-proxy`** post-deploy (problema 3 ricorrente, finché non viene hookato).
- [ ] Verificare:
    - upload media → 200
    - thumbnail Twill admin → render OK
    - frontend con immagini cropped → URL `/storage/img/crops/.../nome__<hex>.jpg` → 200
- [ ] Aggiungere una validazione automatica dell'env all'avvio del container in [docker/deploy.sh](../../../docker/deploy.sh) o entrypoint: controllare che `config('twill.media_library.image_service')` corrisponda a una classe esistente, fail-fast con errore esplicito altrimenti. Eviterebbe ore di debug se ricapita.
- [ ] Considerare hookare `docker restart coolify-proxy` come post-deploy command in Coolify, oppure investigare la radice del Gateway Timeout post-deploy.

## Comandi utili per debug futuro

```bash
# Container app corrente (il nome cambia ad ogni deploy)
APP=$(docker ps --format '{{.Names}}' | grep '^app-qf8593940' | head -1)

# Ispeziona env critico per debug class resolution
docker exec "$APP" php -r 'echo bin2hex(env("MEDIA_LIBRARY_IMAGE_SERVICE")) . "\n";'

# Test resolve via tinker (nota: tinker ha alias loader extra che può falsare —
# se vuoi un test pulito, usa CLI script come sotto)
docker exec "$APP" php artisan tinker --no-interaction --execute '
    echo get_class(app()->make(config("twill.media_library.image_service")));
'

# Test resolve in CLI puro
docker exec "$APP" php -r '
    require "/var/www/html/vendor/autoload.php";
    $app = require "/var/www/html/bootstrap/app.php";
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    echo get_class(app()->make(config("twill.media_library.image_service"))) . "\n";
'

# Reload graceful php-fpm (necessario dopo file edit con OPcache validate_timestamps=0)
docker exec "$APP" sh -c 'kill -USR2 $(pgrep -f "php-fpm: master" | head -1)'

# Smoke test HTTPS dal server
curl -i -m 10 https://new.bricksmusicfestival.com/ 2>&1 | head -5

# Restart proxy se Gateway Timeout post-deploy
docker restart coolify-proxy
```

# Piano: Fix upload immagini Twill in produzione (500 errors)

## Stato diagnostica

| Check                               | Risultato                                                    |
| ----------------------------------- | ------------------------------------------------------------ |
| Struttura storage/                  | OK — directory presenti con permessi 775 e owner www-data    |
| Symlink public/storage              | OK — punta a /var/www/html/storage/app/public                |
| Scrittura come www-data su uploads/ | OK — touch test passato                                      |
| PHP upload limits                   | `upload_max_filesize=2M`, `post_max_size=8M` (da correggere) |
| Classe in classmap                  | OK — `App\Services\MediaLibrary\Glide` trovata               |
| `class_exists()` via CLI            | OK — restituisce `OK`                                        |

---

## Problema attuale

Il `class_exists` restituisce OK **eseguito come root via CLI**. L'errore nei log:

```
ReflectionException: Class "App\Services\MediaLibrary\Glide" does not exist
```

...si verifica all'interno di **php-fpm che gira come `www-data`**. I test CLI e FPM usano
ambienti diversi (utente, OPcache, bootstrap Laravel). Il test come root non è sufficiente
per escludere il problema.

---

## Step 1 — Riprodurre l'errore attuale (1 minuto)

Tenta un upload dal browser e subito dopo:

```bash
docker logs app-qf8593940icne7cinvidkoz9-225431662437 --since "2m" 2>&1 | grep -A3 "ERROR"
```

**Obiettivo**: capire se l'errore `Glide does not exist` si verifica ancora, o se è stato
risolto da un redeploy recente e c'è ora un errore DIVERSO.

**Risultato**:
root@dz-ubuntu-4gb-nbg1-1:~# docker exec app-qf8593940icne7cinvidkoz9-225431662437 php -r "require '/var/www/html/vendor/autoload.php'; echo class_exists('App\^C
root@dz-ubuntu-4gb-nbg1-1:~# docker logs app-qf8593940icne7cinvidkoz9-225431662437 --since "2m" 2>&1 | grep -A3 "ERROR"
[2026-05-07 06:49:28] production.ERROR: Target class [App\\Services\\MediaLibrary\\Glide] does not exist. {"userId":1,"exception":"[object] (Illuminate\\Contracts\\Container\\BindingResolutionException(code: 0): Target class [App\\\\Services\\\\MediaLibrary\\\\Glide] does not exist. at /var/www/html/vendor/laravel/framework/src/Illuminate/Container/Container.php:1124)
[stacktrace]
#0 /var/www/html/vendor/laravel/framework/src/Illuminate/Container/Container.php(933): Illuminate\\Container\\Container->build()
#1 /var/www/html/vendor/laravel/framework/src/Illuminate/Foundation/Application.php(1078): Illuminate\\Container\\Container->resolve()
--
[2026-05-07 06:49:38] production.ERROR: Target class [App\\Services\\MediaLibrary\\Glide] does not exist. {"userId":1,"exception":"[object] (Illuminate\\Contracts\\Container\\BindingResolutionException(code: 0): Target class [App\\\\Services\\\\MediaLibrary\\\\Glide] does not exist. at /var/www/html/vendor/laravel/framework/src/Illuminate/Container/Container.php:1124)
[stacktrace]
#0 /var/www/html/vendor/laravel/framework/src/Illuminate/Container/Container.php(933): Illuminate\\Container\\Container->build()
#1 /var/www/html/vendor/laravel/framework/src/Illuminate/Foundation/Application.php(1078): Illuminate\\Container\\Container->resolve()

---

## Step 2 — Ripetere il test come `www-data` (l'utente reale di php-fpm)

```bash
docker exec -u www-data app-qf8593940icne7cinvidkoz9-225431662437 \
  php -r "require '/var/www/html/vendor/autoload.php'; echo class_exists('App\Services\MediaLibrary\Glide') ? 'OK' : 'MISSING';"
```

- **OK** → il problema è altrove (vedi Step 3)
- **MISSING** → c'è una divergenza di permessi/ambiente tra root e www-data su qualche file
  della catena di autoloading; verificare:
  `bash
docker exec <CTR> ls -la /var/www/html/vendor/composer/
docker exec <CTR> ls -la /var/www/html/vendor/area17/twill/src/Services/MediaLibrary/
`
  **risultato**
  root@dz-ubuntu-4gb-nbg1-1:~# docker exec -u www-data app-qf8593940icne7cinvidkoz9-225431662437 \
   php -r "require '/var/www/html/vendor/autoload.php'; echo class_exists('App\Services\MediaLibrary\Glide') ? 'OK' : 'MISSING';"
  OK

---

## Step 3 — Verificare il config cache in produzione

Se `class_exists` come www-data è OK, il problema potrebbe essere nel config cache che
contiene un valore errato per `twill.media_library.image_service`:

```bash
docker exec app-qf8593940icne7cinvidkoz9-225431662437 \
  php artisan config:show twill | grep image_service
```

Deve restituire `App\Services\MediaLibrary\Glide`. Se restituisce altro → il config cache
è corrotto; applicare subito:

```bash
docker exec app-qf8593940icne7cinvidkoz9-225431662437 \
  php artisan config:clear && php artisan config:cache
```

**risultato**
root@dz-ubuntu-4gb-nbg1-1:~docker exec app-qf8593940icne7cinvidkoz9-225431662437 \ \
 php artisan config:show twill | grep image_service
media_library ⇁ image_service ........... App\\Services\\MediaLibrary\\Glide

---

## Fix da applicare indipendentemente dai risultati dei test

### Fix 1 — PHP upload limits (Dockerfile, runtime stage)

File: [Dockerfile](Dockerfile) — aggiungere DOPO il blocco `opcache.ini` (dopo riga 151):

```dockerfile
RUN { \
    echo 'upload_max_filesize=80M'; \
    echo 'post_max_size=80M'; \
    echo 'memory_limit=256M'; \
} > /usr/local/etc/php/conf.d/uploads.ini
```

Attualmente `upload_max_filesize=2M` mentre Twill è configurato per 50MB. Anche se il 500
attuale ha una causa diversa, questo va corretto.

### Fix 2 — Rimuovere `--classmap-authoritative` (Dockerfile, php-base stage)

File: [Dockerfile](Dockerfile) righe 51-54:

Da:

```dockerfile
RUN composer dump-autoload \
    --no-dev \
    --optimize \
    --classmap-authoritative
```

A:

```dockerfile
RUN composer dump-autoload \
    --no-dev \
    --optimize
```

Con `--optimize` senza `--classmap-authoritative` si ottiene lo stesso beneficio di performance
(classmap pre-generata) con fallback PSR-4 come rete di sicurezza. Questo non causa regressioni
ed elimina tutta una categoria di problemi di autoloading con classi applicative custom.

---

## Dipendenza dai risultati del Step 1

- **Errore `Glide does not exist` ancora presente** → applicare Fix 2 + step 2 per confermare
- **Errore diverso** → fornire il nuovo stacktrace per diagnosi mirata
- **Nessun errore** → l'errore era legato a un deploy precedente e ora è risolto; applicare
  solo Fix 1 (upload limits) e Fix 2 (precauzione)

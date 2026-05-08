# Piano: Fix upload immagini Twill in produzione (500 errors)

mentre in locale con sail riesco, in produzione con coolify per il deploy non riesco a caricare immagini in twill e ottengo dei 500 error.
I log vanno nei log di docker e non il storage/logs per impostazione di coolify.

Appena apro il media manager di twill vedo questo errore nei log:

NOTICE: PHP message: PHP Fatal error: Cannot redeclare class App\Services\MediaLibrary\Glide (previously declared in /var/www/html/app/Services/MediaLibrary/Glide.php:14) in /var/www/html/app/Services/MediaLibrary/Glide.php on line 14
[2026-05-08 07:12:02] production.ERROR: Cannot redeclare class App\Services\MediaLibrary\Glide (previously declared in /var/www/html/app/Services/MediaLibrary/Glide.php:14) {"userId":1,"exception":"[object] (Symfony\\Component\\ErrorHandler\\Error\\FatalError(code: 0): Cannot redeclare class App\\Services\\MediaLibrary\\Glide (previously declared in /var/www/html/app/Services/MediaLibrary/Glide.php:14) at /var/www/html/app/Services/MediaLibrary/Glide.php:14)
[stacktrace]
#0 {main}
"}
127.0.0.1 - 08/May/2026:07:12:02 +0000 "GET /index.php" 200
NOTICE: PHP message: PHP Fatal error: Cannot redeclare class App\Services\MediaLibrary\Glide (previously declared in /var/www/html/app/Services/MediaLibrary/Glide.php:14) in /var/www/html/app/Services/MediaLibrary/Glide.php on line 14
127.0.0.1 - 08/May/2026:07:12:24 +0000 "POST /index.php" 500
[2026-05-08 07:12:24] production.ERROR: Cannot redeclare class App\Services\MediaLibrary\Glide (previously declared in /var/www/html/app/Services/MediaLibrary/Glide.php:14) {"userId":1,"exception":"[object] (Symfony\\Component\\ErrorHandler\\Error\\FatalError(code: 0): Cannot redeclare class App\\Services\\MediaLibrary\\Glide (previously declared in /var/www/html/app/Services/MediaLibrary/Glide.php:14) at /var/www/html/app/Services/MediaLibrary/Glide.php:14)
[stacktrace]
#0 {main}
"}

Alcuni log per capire di più:

root@dz-ubuntu-4gb-nbg1-1:~# docker exec app-qf8593940icne7cinvidkoz9-070536881868 \
 grep "App.\*Glide" /var/www/html/vendor/composer/autoload_classmap.php
'App\\Services\\MediaLibrary\\Glide' => $baseDir . '/app/Services/MediaLibrary/Glide.php',
root@dz-ubuntu-4gb-nbg1-1:~# docker logs app-qf8593940icne7cinvidkoz9-070536881868 --since "5m" 2>&1 | grep -A30 "Cannot redeclare" | head -50
root@dz-ubuntu-4gb-nbg1-1:~#
root@dz-ubuntu-4gb-nbg1-1:~#
root@dz-ubuntu-4gb-nbg1-1:~# docker exec app-qf8593940icne7cinvidkoz9-070536881868 \
 php -r "require '/var/www/html/vendor/autoload.php'; var_dump(count(spl_autoload_functions()));"
int(1)
root@dz-ubuntu-4gb-nbg1-1:~# docker exec app-qf8593940icne7cinvidkoz9-070536881868 php -r "require '/var/www/html/vendor/autoload.php'; var_dump(count(spl_autoload_functions()));"
int(1)
root@dz-ubuntu-4gb-nbg1-1:~# docker logs app-qf8593940icne7cinvidkoz9-070536881868 --since "5m" 2>&1 | grep -A30 "Cannot redeclare" | head -50
NOTICE: PHP message: PHP Fatal error: Cannot redeclare class App\Services\MediaLibrary\Glide (previously declared in /var/www/html/app/Services/MediaLibrary/Glide.php:14) in /var/www/html/app/Services/MediaLibrary/Glide.php on line 14
[2026-05-07 07:50:06] production.ERROR: Cannot redeclare class App\Services\MediaLibrary\Glide (previously declared in /var/www/html/app/Services/MediaLibrary/Glide.php:14) {"userId":1,"exception":"[object] (Symfony\\Component\\ErrorHandler\\Error\\FatalError(code: 0): Cannot redeclare class App\\Services\\MediaLibrary\\Glide (previously declared in /var/www/html/app/Services/MediaLibrary/Glide.php:14) at /var/www/html/app/Services/MediaLibrary/Glide.php:14)
[stacktrace]
#0 {main}
"}
127.0.0.1 - 07/May/2026:07:50:10 +0000 "POST /index.php" 500
NOTICE: PHP message: PHP Fatal error: Cannot redeclare class App\Services\MediaLibrary\Glide (previously declared in /var/www/html/app/Services/MediaLibrary/Glide.php:14) in /var/www/html/app/Services/MediaLibrary/Glide.php on line 14
[2026-05-07 07:50:10] production.ERROR: Cannot redeclare class App\Services\MediaLibrary\Glide (previously declared in /var/www/html/app/Services/MediaLibrary/Glide.php:14) {"userId":1,"exception":"[object] (Symfony\\Component\\ErrorHandler\\Error\\FatalError(code: 0): Cannot redeclare class App\\Services\\MediaLibrary\\Glide (previously declared in /var/www/html/app/Services/MediaLibrary/Glide.php:14) at /var/www/html/app/Services/MediaLibrary/Glide.php:14)
[stacktrace]
#0 {main}
"}
root@dz-ubuntu-4gb-nbg1-1:~# docker exec app-qf8593940icne7cinvidkoz9-070536881868 php -r "require '/var/www/html/vendor/autoload.php'; var_dump(count(spl_autoload_functions()));"
int(1)
root@dz-ubuntu-4gb-nbg1-1:~# docker exec app-qf8593940icne7cinvidkoz9-070536881868 php -r "require '/var/www/html/vendor/autoload.php'; var_dump(count(spl_autoload_functions()));"
int(1)

non capisco dove Glide sia importato due volte, nella codebase (escluso dockerfile) se lo fosse non funzionerebbe neache in locale.

Ho fatto fare a Sonnet un'analisi della codebase che indica:

--- sonnet
Il file app/Services/MediaLibrary/Glide.php non è incluso manualmente da nessuna parte: l'unico modo per cui possa essere caricato è l'autoloader Composer. Eppure l'errore "Cannot redeclare … previously declared in /var/www/html/app/Services/MediaLibrary/Glide.php" dice che lo stesso identico file viene included due volte nello stesso processo.

Date le condizioni del runtime, le ipotesi credibili per Sonnet sono tre in ordine di probabilità:

1. OPcache (sospetto principale)
   Hai opcache.validate_timestamps=0 + opcache.enable=1. In Coolify il rebuild non resetta sempre la SHM di OPcache se il container viene riavviato/swappato in modo "soft", o se c'è zero-downtime con worker overlap. Il sintomo classico in quel caso è proprio "Cannot redeclare X" la prima volta che il worker riprende un file con bytecode stale.

Test rapido: disabilita opcache e fai redeploy.
Dockerfile:143 cambia opcache.enable=1 → opcache.enable=0.
Se l'errore sparisce, è opcache: aggiungi opcache_reset() in entrypoint o esegui kill -USR2 su php-fpm ad ogni deploy.

2. Due autoloader registrati in FPM (non CLI)
   Il tuo test count(spl_autoload_functions()) è in CLI con solo vendor/autoload.php — fa uno. In FPM dopo il bootstrap di Laravel/Twill ne ho visti casi con 2+ (Twill registra cose). Se due loader risolvono lo stesso file, Composer lo include una sola volta, ma se uno dei due fa require manuale, esplode.

Test: aggiungi temporaneamente in cima a public/index.php (o in un middleware che gira prima di Twill):

register_shutdown_function(fn() => error_log('Loaders: '.print_r(array_map(fn($f) => is_array($f) ? get_class($f[0]).'::'.$f[1] : (is_string($f) ? $f : 'closure'), spl_autoload_functions()), true))); 3. Tracing diretto sul file (l'unico modo definitivo)
Inserisci 6 righe in cima a app/Services/MediaLibrary/Glide.php:1 — ti dice esattamente da dove parte il secondo include:

<?php
namespace App\Services\MediaLibrary;

if (class_exists(__NAMESPACE__.'\Glide', false)) {
    error_log('GLIDE DOUBLE-LOAD: '.json_encode(array_slice(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), 0, 15)));
    return;
}

use A17\Twill\Services\MediaLibrary\Glide as GlideExtend;
// ...resto invariato
Il return evita il fatale; il backtrace nei docker logs mostra chi sta facendo il secondo include. Una volta identificato, rimuovi le 6 righe.

Fix preventivi indipendenti
Indipendentemente dalla causa, ti consiglio di aggiungere all'entrypoint.sh prima di supervisord:


php artisan optimize:clear || true
# rigenera il classmap nel runtime, non nel build container
composer dump-autoload --no-dev --optimize --classmap-authoritative -d /var/www/html || true
Il motivo: il classmap è generato nello stage php-base che ha WORKDIR /app. Ricompilarlo nel runtime garantisce path coerenti con /var/www/html (anche se $baseDir dovrebbe risolverlo correttamente). E optimize:clear butta via qualunque view/route/event cache lasciato dal build container.

Quale dei tre vuoi provare per primo? Io partirei da #3 (tracing) perché è l'unico che dà una risposta definitiva — i fix #1 e #2 li applichi dopo aver visto il backtrace.
---

# Unificazione storage video Subscription + auto-Title

## Context

Oggi le `Subscription` vivono in due sistemi paralleli:

- **Frontend** (`SubscriptionController@store`): video salvato con `Storage::disk('public')->putFile('subscriptions', ...)` → percorso stringa nella colonna varchar `video_file_path`.
- **Admin Twill** (`Twill\SubscriptionController`): già configurato con `Files::make()->name('video_file')` → record nelle tabelle `twill_files` + `twill_fileables` (Twill File Library, cartella `storage/app/public/uploads/`).

Conseguenze:
1. Dall'admin **non si possono creare/aggiornare Subscription complete** in modo coerente: il file caricato dall'admin va in `uploads/`, ma la colonna legacy `video_file_path` resta vuota e il display `video_file_url` lavora su quella → il video caricato dall'admin **non viene mostrato in lista**.
2. Due sorgenti di file (`subscriptions/` e `uploads/`).
3. Il campo `title` (creato di default da `createDefaultTableFields()`) resta `NULL` per le submission frontend; l'admin ne è disturbato perché alcune view Twill possono cadere su `title` quando manca un identificatore.

**Obiettivo**: un'unica pipeline (Twill File Library, disk pubblico di default) per i video, valida sia da admin sia da frontend; campo `title` auto-popolato lato frontend con il valore di `band` e nascosto dall'admin form.

**Scelte già confermate dall'utente:**
- Disk: pubblico (default Twill File Library, `storage/app/public/uploads`) — security by obscurity tramite UUID, come oggi.
- Legacy: eliminare colonna `video_file_path` + i 2 file in `storage/app/public/subscriptions/`.
- Title: auto-popolato con `band` lato frontend, **non** mostrato nell'admin form.

---

## File da modificare

| Path | Modifica |
|---|---|
| [app/Http/Controllers/SubscriptionController.php](../../../app/Http/Controllers/SubscriptionController.php) | Cambiare upload: usare Twill File Library invece di `Storage::disk('public')`. Auto-popolare `title = band`. |
| [app/Http/Requests/StoreSubscriptionRequest.php](../../../app/Http/Requests/StoreSubscriptionRequest.php) | Nessuna modifica funzionale alle regole; lasciare `video_file_path` come nome chiave del file in input (è il `name` del campo nel form React). |
| [app/Models/Subscription.php](../../../app/Models/Subscription.php) | Rimuovere `video_file_path` dal `$fillable`. Sostituire l'accessor `videoFileUrl` con uno che usa `$this->file('video_file')` (Twill HasFiles). Aggiungere `title` al `$fillable`. |
| [app/Repositories/SubscriptionRepository.php](../../../app/Repositories/SubscriptionRepository.php) | Aggiungere proprietà `$fieldsGroups['files'] = ['video_file']` per consentire al trait `HandleFiles` di gestire la relazione standard nei salvataggi admin. Aggiungere metodo helper `attachVideoFile($model, $uploadedFile)` per il flusso frontend. |
| [app/Http/Controllers/Twill/SubscriptionController.php](../../../app/Http/Controllers/Twill/SubscriptionController.php) | Rimuovere `Input::make()->name('video_file_path')` (campo legacy). Aggiornare la colonna index "Video File" per leggere via `$model->file('video_file')` invece di `$model->video_file_url`. |
| `database/migrations/<new>_drop_legacy_video_path_from_subscriptions.php` | Nuova migration: drop colonna `video_file_path`. |
| `storage/app/public/subscriptions/` | Eliminare cartella e i 2 file legacy (manuale via shell). |

Nessuna modifica al frontend React: la `key` POSTata resta `video_file_path` (è solo un nome di campo HTTP); lato server lo trattiamo come "file del video" e lo persistiamo in Twill File Library.

---

## Implementazione

### 1. Helper per upload nel Twill File Library (frontend)

In `app/Repositories/SubscriptionRepository.php` aggiungere:

```php
use A17\Twill\Models\File as TwillFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * Carica un file caricato dal frontend nel Twill File Library e lo associa
 * al model con il role 'video_file'. Disk: config('twill.file_library.disk').
 */
public function attachVideoFile(\App\Models\Subscription $subscription, UploadedFile $upload): void
{
    $disk = config('twill.file_library.disk');
    $folder = (string) Str::uuid();
    $cleanName = Str::slug(pathinfo($upload->getClientOriginalName(), PATHINFO_FILENAME))
        . '.' . $upload->getClientOriginalExtension();

    $path = $upload->storeAs($folder, $cleanName, $disk);

    $file = TwillFile::create([
        'uuid' => $path,
        'filename' => $upload->getClientOriginalName(),
        'size' => $upload->getSize(),
    ]);

    $subscription->files()->attach($file->id, [
        'role' => 'video_file',
        'locale' => app()->getLocale(),
        'position' => 1,
    ]);
}
```

Riferimento al codice di Twill: il `FileLibraryController::storeFile` di Twill usa lo stesso pattern `storeAs($folder, $cleanFilename, $disk)` — vedi `vendor/area17/twill/src/Http/Controllers/Admin/FileLibraryController.php`.

### 2. SubscriptionController (frontend)

Modificare `store` in `app/Http/Controllers/SubscriptionController.php`:

```php
public function store(StoreSubscriptionRequest $request): RedirectResponse
{
    $data = $request->validated();

    $upload = $request->file('video_file_path');
    unset($data['video_file_path']); // non è una colonna del DB

    $data['title'] = $data['band']; // auto-popola il title Twill
    $data['privacy'] = (bool) ($data['privacy'] ?? false);
    $data['data_iscrizione'] = now();
    $data['published'] = true;

    $subscription = $this->subscriptions->create($data);

    if ($upload) {
        $this->subscriptions->attachVideoFile($subscription, $upload);
    }

    return back()->with('success', 'Iscrizione inviata con successo.');
}
```

Rimuovere l'import di `Illuminate\Support\Facades\Storage`.

### 3. Subscription model

In `app/Models/Subscription.php`:

- Rimuovere `'video_file_path'` da `$fillable`.
- Aggiungere `'title'` a `$fillable`.
- Sostituire l'accessor `videoFileUrl` con:

```php
protected function videoFileUrl(): Attribute
{
    return Attribute::make(
        get: fn () => $this->file('video_file') ?: null,
    );
}
```

Il metodo `file($role)` arriva dal trait `HasFiles` di Twill e ritorna direttamente l'URL pubblico del file (oppure null).

- Rimuovere l'import `use Illuminate\Support\Facades\Storage;`.

### 4. SubscriptionRepository

In `app/Repositories/SubscriptionRepository.php` dichiarare i ruoli file gestiti dall'admin (utile per il trait HandleFiles):

```php
public array $fieldsGroups = [
    'files' => ['video_file'],
];
```

Twill usa questa convenzione per scoprire automaticamente i `Files::make()->name('video_file')` nel form e gestirne il salvataggio.

### 5. Twill SubscriptionController (admin)

In `app/Http/Controllers/Twill/SubscriptionController.php`:

- Rimuovere il campo legacy:
  ```php
  $form->add(
      Input::make()->name('video_file_path')->label('Path file video (legacy)')->...
  );
  ```
- Aggiornare la colonna "Video File" dell'index. L'accessor `videoFileUrl` ora ritorna direttamente l'URL del Twill File Library (o null), quindi il `customRender` esistente continua a funzionare invariato — verificare solo che restituisca `string|null` (è già così).

Nessun campo `title` viene aggiunto al form: Twill non lo richiede esplicitamente in form custom.

### 6. Migration: drop colonna legacy

Generare con:
```
vendor/bin/sail artisan make:migration drop_legacy_video_path_from_subscriptions --table=subscriptions
```

Contenuto:
```php
public function up(): void
{
    Schema::table('subscriptions', function (Blueprint $table) {
        $table->dropColumn('video_file_path');
    });
}

public function down(): void
{
    Schema::table('subscriptions', function (Blueprint $table) {
        $table->string('video_file_path')->nullable();
    });
}
```

### 7. Pulizia file legacy

Dopo il deploy della migration, eliminare i 2 file:
```
rm -rf storage/app/public/subscriptions
```

---

## Verifica end-to-end

1. **Avviare ambiente**:
   ```
   vendor/bin/sail up -d
   vendor/bin/sail artisan migrate
   vendor/bin/sail npm run dev
   ```

2. **Frontend → admin**:
   - Aprire una pagina che contiene il blocco `SubscriptionForm`, compilare il form caricando un file video, inviare.
   - In admin (`/admin/subscriptions`) verificare che la nuova subscription appaia con `band` come identificatore (visibile nella colonna "Band").
   - Verificare che la colonna "Video File" mostri il link "Scarica video" che porta al file in `storage/app/public/uploads/<uuid>/<filename>`.
   - Verificare via `database-query` che `subscriptions.title` sia uguale al valore `band`.
   - Verificare che `twill_files` contenga il record e `twill_fileables` la relazione con `role='video_file'`.

3. **Admin → admin**:
   - In `/admin/subscriptions/create` compilare tutti i campi inclusa la `band` e caricare un video tramite il File Picker di Twill.
   - Salvare. Riaprire in edit e verificare che il video sia ancora associato.
   - Verificare che la lista admin mostri il link al video appena caricato.

4. **Update da admin**:
   - Modificare una subscription creata da frontend, sostituire il video con uno nuovo, salvare. Confermare che `twill_fileables` abbia il nuovo file e il vecchio venga sostituito.

5. **Pulizia legacy**:
   - Confermare che `storage/app/public/subscriptions` non esista più e che la colonna `video_file_path` sia assente:
     ```
     vendor/bin/sail artisan db:show --counts subscriptions
     ```

6. **Stile / lint**:
   ```
   vendor/bin/sail bin pint --dirty --format agent
   ```

7. **Test (se esistono test del modulo)**: aggiornare/creare test feature per `SubscriptionController@store` che verifichi:
   - `Storage::disk('public')->assertMissing('subscriptions/...')` → niente più scritture lì
   - Esistenza di un record in `twill_files` e `twill_fileables` con role=`video_file`
   - `subscriptions.title === subscriptions.band`

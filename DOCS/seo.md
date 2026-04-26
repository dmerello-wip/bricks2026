# SEO

Gestione SEO polimorfica: i dati SEO di ogni modulo vengono salvati in una tabella `seo_data` condivisa.

---

## Aggiungere SEO a un nuovo modulo

Sono necessari **4 passi**:

### 1. Model — `use HasSeoData`

```php
use App\Models\Concerns\HasSeoData;

class Evento extends Model
{
    use HasMedias, HasTranslation, /* ... */;
    use HasSeoData;
    // nessuna altra modifica
}
```

Il trait:
- dichiara `$mediasParams` come proprietà PHP (non passa per `__set` di Eloquent)
- inietta automaticamente il ruolo `seo_og_image` in `$mediasParams` via `initializeHasSeoData()`
- espone la relazione `seoData(): MorphOne`

### 2. Repository — `use HandleSeoData`

```php
use App\Repositories\Concerns\HandleSeoData;

class EventoRepository extends ModuleRepository
{
    use HandleMedias, HandleTranslations, /* ... */;
    use HandleSeoData;
}
```

Twill chiama automaticamente `afterSaveHandleSeoData` e `afterDeleteHandleSeoData` tramite `traitsMethods()`.

### 3. Twill Controller — aggiungi il fieldset

```php
use App\Twill\Fieldsets\SeoFieldset;

protected function form(?int $id, TwillModelContract $item = null): Form
{
    $form = parent::form($id, $item);
    $form->addFieldset(SeoFieldset::make());
    return $form;
}
```

### 4. Frontend Controller — inietta `SeoService`

```php
use App\Services\SeoService;

public function show(string $slug, EventoRepository $repo, SeoService $seoService)
{
    $evento = $repo->forSlug($slug);
    $evento->load('seoData', 'medias');

    return Inertia::render('Evento', [
        'seo' => $seoService->resolve($evento),
    ]);
}
```

Nella React page:

```tsx
import SeoHead from '@/components/seo/SeoHead';

export default function Evento() {
    const { seo } = usePage<SharedData & { seo: SeoData }>().props;
    return (
        <PageLayout>
            <SeoHead seo={seo} />
            {/* ... */}
        </PageLayout>
    );
}
```

---

## Flusso della funzionalità

### Salvataggio (CMS)

```
Twill form submit
  → ModuleRepository::create() / update()
      → prepareFieldsBeforeCreate / prepareFieldsBeforeSave
          → HandleTranslations: ristruttura i campi tradotti del modulo
            (seo_title, og_title ecc. NON sono in translatedAttributes
             → rimangono nell'array $fields come locale-keyed arrays)
      → $model->fill(Arr::except($fields, reservedFields))
          → non tocca i campi SEO (non sono in $fillable del modulo)
      → $model->save()                          ← Page salvata
      → afterSave() → traitsMethods() → afterSaveHandleSeoData()
          → legge $fields['seo_title']['it'], $fields['seo_title']['en'] ecc.
          → seoData()->updateOrCreate([], ['no_index' => ..., 'translations' => {...}])
          → l'immagine OG viene salvata da HandleMedias sul modello padre
            (mediasParams['seo_og_image'] iniettato dal trait, mediable_type = modulo)
```

### Lettura (frontend)

```
Frontend Controller
  → $model->load('seoData', 'medias')      ← eager load: 1 query extra
  → SeoService::resolve($model)
      → legge $model->seoData->getForLocale($locale)   ← translations JSON
      → legge $model->image('seo_og_image', 'default', [], true)  ← media Twill
      → fallback su SeoDefault (singleton, lazy-cached per request)
      → restituisce array normalizzato { title, description, keywords,
                                         og_title, og_description, og_image, no_index }
  → Inertia::render('...', ['seo' => $resolved])

React page
  → <SeoHead seo={seo} />
      → Inertia <Head> con title, meta description, keywords, robots, og:*
```

### Cascata di fallback in `SeoService::resolve()`

| Campo | Priorità |
|-------|----------|
| `title` | SEO Title del record → `$model->title` → default_title di SeoDefault |
| `og_title` | OG Title → SEO Title del record → `$model->title` → default_og_title di SeoDefault |
| `og_image` | Immagine OG del record → default_og_image di SeoDefault |
| altri campi | Valore del record → corrispondente default di SeoDefault |

---

## File coinvolti

| File | Ruolo |
|------|-------|
| `app/Models/Concerns/HasSeoData.php` | Trait model: inietta mediasParams, espone `seoData()` |
| `app/Repositories/Concerns/HandleSeoData.php` | Trait repository: afterSave/afterDelete |
| `app/Twill/Fieldsets/SeoFieldset.php` | Fieldset riusabile per i form Twill |
| `app/Models/SeoData.php` | Model polimorficon con JSON `translations` |
| `app/Models/SeoDefault.php` | Singleton per i valori di fallback |
| `app/Services/SeoService.php` | Risolve il payload SEO finale con fallback |
| `resources/js/components/seo/SeoHead.tsx` | Componente React → `<Head>` Inertia |
| `database/migrations/*_create_seo_data_table.php` | Una sola migration, condivisa da tutti i moduli |

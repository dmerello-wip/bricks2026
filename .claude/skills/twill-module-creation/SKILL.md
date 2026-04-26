---
name: twill-module-creation
description: Standard per la creazione di moduli in Twill 3 utilizzando la Fluent API.
---

# Skill: Twill 3 Form Builder (Fluent API)

Questa skill definisce lo standard per la creazione di form in Twill 3. Utilizza sempre la Fluent API e l'oggetto `$form`.

## Struttura Base del Metodo Form
Ogni metodo `form(Form $form): Form` deve seguire questo pattern:

```php
public function form(Form $form): Form
{
    $form->add(
        Input::make()->name('title')->label('Titolo')->translatable()
    );

    $form->add(
        Select::make()
            ->name('author')
            ->label('Autore')
            ->options(
                Options::make([
                    Option::make('mario_rossi', 'Mario Rossi'),
                    Option::make('giulia_verdi', 'Giulia Verdi'),
                ])
            )
    );

    return $form;
}
```

### Regole d'oro
- **No Array**: Non definire i campi tramite l'array $fields nel Model. Usa solo il Form Builder.
- **Type-hinting**: Importa sempre le classi corrette (A17\Twill\Services\Forms\Fields\...).
- **Traduzioni**: Usa ->translatable() per campi multi-lingua.
- **Validazione**: Assicurati che i nomi dei campi corrispondano alla FormRequest.

---

## SEO Integration

Quando il modulo rappresenta una **pagina pubblica** (accessibile via URL frontend), aggiungi il supporto SEO seguendo questi 4 passi.

> **Quando applicarlo:** ogni modulo che ha un controller frontend con `Inertia::render()` deve includere il SEO.

### 1. Model — aggiungi `HasSeoData`

```php
use App\Models\Concerns\HasSeoData;

class Articolo extends Model
{
    use HasMedias, HasTranslation; // trait esistenti
    use HasSeoData;                // aggiungere dopo gli altri
}
```

Il trait inietta automaticamente `seo_og_image` in `$mediasParams` e dichiara la relazione `seoData(): MorphOne`.

### 2. Repository — aggiungi `HandleSeoData`

```php
use App\Repositories\Concerns\HandleSeoData;

class ArticoloRepository extends ModuleRepository
{
    use HandleMedias, HandleTranslations; // trait esistenti
    use HandleSeoData;                    // aggiungere dopo gli altri
}
```

Twill invoca automaticamente `afterSaveHandleSeoData` e `afterDeleteHandleSeoData` tramite `traitsMethods()`.

### 3. Twill Controller — aggiungi `SeoFieldset`

```php
use App\Twill\Fieldsets\SeoFieldset;

protected function form(?int $id, TwillModelContract $item = null): Form
{
    $form = parent::form($id, $item);

    // ... altri campi del modulo ...

    $form->addFieldset(SeoFieldset::make()); // sempre in fondo al form
    return $form;
}
```

### 4. Frontend Controller — risolvi il payload SEO

```php
use App\Services\SeoService;

public function show(string $slug, ArticoloRepository $repo, SeoService $seoService): \Inertia\Response
{
    $articolo = $repo->forSlug($slug);
    $articolo->load('seoData', 'medias');

    return Inertia::render('Articolo/Show', [
        'articolo' => ArticoloResource::make($articolo),
        'seo'      => $seoService->resolve($articolo),
    ]);
}
```

Nella React page, usa `<SeoHead>`:

```tsx
import SeoHead from '@/components/seo/SeoHead';
import { usePage } from '@inertiajs/react';
import type { SharedData, SeoData } from '@/types';

export default function ArticoloShow() {
    const { seo } = usePage<SharedData & { seo: SeoData }>().props;

    return (
        <PageLayout>
            <SeoHead seo={seo} />
            {/* contenuto pagina */}
        </PageLayout>
    );
}
```

### Checklist SEO

- [ ] `HasSeoData` aggiunto al Model
- [ ] `HandleSeoData` aggiunto al Repository
- [ ] `SeoFieldset::make()` aggiunto in fondo al form del ModuleController
- [ ] Frontend controller carica `seoData` e `medias` ed inietta `$seoService->resolve($model)`
- [ ] React page importa e usa `<SeoHead seo={seo} />`

---

## Preview con React/Inertia

Questo progetto usa entry point React standalone per le preview nel CMS (block editor e preview globale), senza il router Inertia che causa crash nell'iframe `about:srcdoc`.

> Documentazione completa: `DOCS/preview-with-inertia.md`

### Block editor preview

Già configurato globalmente tramite IoC binding in `AppServiceProvider`. Nessun intervento richiesto per nuovi moduli.

### Preview globale del modulo

Ogni ModuleController **con blocchi** deve includere il trait `HasBlockPreview`:

```php
use App\Twill\Concerns\HasBlockPreview;

class ProjectController extends BaseModuleController
{
    use HasBlockPreview;

    protected $moduleName = 'projects';

    // ...
}
```

Il trait gestisce automaticamente:
- il routing verso la view generica `admin.module-preview`
- il formatting dei blocchi via `TwillBlockService`

Nessuna Blade view, nessun TSX specifico per modulo.

### ⚠️ Link nei componenti editorial: usare `<AppLink>`

Nei componenti editorial (blocchi, CTA, ecc.) non usare mai `<Link>` di `@inertiajs/react` direttamente: nell'iframe preview `window.location.href` è `about:srcdoc` e il costruttore `URL` di Inertia crasha.

Usare `<AppLink>` che legge `PreviewContext` e sceglie automaticamente tra `<Link>` (produzione) e `<a>` (preview):

```tsx
// ✗ causa crash nel preview iframe
import { Link } from '@inertiajs/react';
<Link href={href}>Label</Link>

// ✓ corretto per componenti editorial
import AppLink from '@/components/ui/AppLink';
<AppLink href={href}>{label}</AppLink>
```

`<Link>` di Inertia rimane corretto per componenti di navigazione globale (`Header`, `Footer`, paginazione) che non vengono mai renderizzati nell'iframe preview.

### Checklist preview

- [ ] Twill ModuleController include `use HasBlockPreview` (se il modulo ha blocchi)
- [ ] Componenti editorial usano `<AppLink>` invece di `<Link>` per link interni

---

## OpenAPI / TypeScript Types

### Fonte di verità

`#[OA\Schema(...)]` nel Model PHP è l'**unica** fonte di verità per i tipi TypeScript frontend.

- `resources/js/lib/types/swagger.ts` è **generato automaticamente** — non modificarlo mai a mano
- Modificare solo `swagger.ts` senza aggiornare `#[OA\Schema]` lascia i due file inconsistenti

### Flusso obbligatorio per aggiungere/rimuovere/modificare un campo

```
1. Modifica #[OA\Schema] nel Model PHP   ← punto di partenza, mai saltare
2. vendor/bin/sail artisan l5-swagger:generate
3. npm run generate-swagger-types
```

```bash
vendor/bin/sail artisan l5-swagger:generate && npm run generate-swagger-types
```

### Checklist OpenAPI

- [ ] `#[OA\Schema]` nel Model PHP aggiornato (aggiunta/rimozione/modifica property)
- [ ] `$fillable` del Model aggiornato di conseguenza
- [ ] `sail artisan l5-swagger:generate` eseguito
- [ ] `npm run generate-swagger-types` eseguito
- [ ] `swagger.ts` **non** toccato manualmente
# Block Editor Preview con Inertia/React

Twill usa per default Blade per il rendering delle preview (block editor e preview globale). Questo progetto le sostituisce con React tramite entry point standalone, evitando il router Inertia nell'iframe.

---

## Architettura

### Perché non usare `Inertia::render()` direttamente

L'iframe di Twill carica il preview come `srcdoc`. Quando si usa Inertia, il router tenta di costruire URL con `window.location.href = about:srcdoc` come base, causando `TypeError: Failed to construct 'URL': Invalid URL` su qualsiasi componente che usa `<Link>`.

La soluzione è usare entry point React **standalone** (senza Inertia router) che ricevono i dati via `window.__PREVIEW_*__`.

---

## 1. Block Editor Preview (singolo blocco)

Attivato dall'editor a blocchi quando si modifica un blocco. Twill fa POST a `admin/blocks/preview` e usa la risposta come `srcdoc` dell'iframe.

### Controller override

`app/Http/Controllers/Admin/BlocksController.php` estende il controller Twill via IoC binding.

**Binding** in `AppServiceProvider::register()`:
```php
$this->app->bind(
    \A17\Twill\Http\Controllers\Admin\BlocksController::class,
    \App\Http\Controllers\Admin\BlocksController::class,
);
```

Il controller usa `BlockRenderer::fromCmsArray()` per idratare i dati del blocco dal POST, poi `TwillBlockService::formatBlock()` per formattarlo, e restituisce la Blade view come stringa.

**Firma del metodo**: deve corrispondere esattamente al parent (`ViewFactory $viewFactory`, return type `string`).

### Entry point React

`resources/js/block-preview.tsx` — wrappa `<BlockRenderer>` in `<PreviewContext.Provider value={true}>` e dispatcha `resize` sul parent window dopo il mount per triggerare il calcolo dell'altezza iframe da parte di Twill.

### Blade template

`resources/views/admin/block-preview.blade.php` — inietta `window.__PREVIEW_BLOCK__` come JSON, carica `@vite(['resources/js/block-preview.tsx'])`.

---

## 2. Preview Globale del Modulo

Attivata dal pulsante "Preview" nel form Twill. Mostra l'intera pagina con tutti i blocchi.

### Trait `HasBlockPreview`

`app/Twill/Concerns/HasBlockPreview.php` — da aggiungere a qualsiasi Twill ModuleController che ha blocchi.

```php
use App\Twill\Concerns\HasBlockPreview;

class ProjectController extends BaseModuleController
{
    use HasBlockPreview;
    // ...
}
```

Il trait implementa:
- `preview(int $id)`: imposta `admin.module-preview` come view e delega al parent
- `previewData($item)`: filtra i blocchi root (senza `parent_id`), li formatta con `TwillBlockService`, li restituisce come `['blocks' => [...]]`

### Entry point React

`resources/js/module-preview.tsx` — itera i blocchi e monta `<BlockRenderer>` per ciascuno.

### Blade template

`resources/views/admin/module-preview.blade.php` — inietta `window.__PREVIEW_BLOCKS__` (array), carica `@vite(['resources/js/module-preview.tsx'])`.

---

## Link nei componenti editorial: usare `<AppLink>`

`<Link>` di `@inertiajs/react` usato direttamente causa crash nell'iframe preview (`about:srcdoc` non è una base URL valida per il costruttore `URL`).

La soluzione è `<AppLink>` (`resources/js/components/ui/AppLink.tsx`): legge `PreviewContext` e renderizza `<Link>` in produzione, `<a>` nel preview. Tutti i componenti editorial che necessitano di navigazione interna devono usarlo.

```tsx
import AppLink from '@/components/ui/AppLink';

// in qualsiasi componente editorial:
<AppLink href={href}>{label}</AppLink>
```

---

## File coinvolti

| File | Scopo |
|---|---|
| `app/Http/Controllers/Admin/BlocksController.php` | Override block preview, IoC-bound |
| `app/Providers/AppServiceProvider.php` | IoC binding del BlocksController |
| `app/Twill/Concerns/HasBlockPreview.php` | Trait per preview globale moduli |
| `resources/views/admin/block-preview.blade.php` | Template singolo blocco |
| `resources/views/admin/module-preview.blade.php` | Template preview intera pagina |
| `resources/js/block-preview.tsx` | React entry — singolo blocco |
| `resources/js/module-preview.tsx` | React entry — blocchi pagina |
| `resources/js/lib/context/preview.ts` | `PreviewContext` |
| `resources/js/components/ui/AppLink.tsx` | Link condizionale Inertia/anchor |
| `vite.config.ts` | Entry point registrati come input |

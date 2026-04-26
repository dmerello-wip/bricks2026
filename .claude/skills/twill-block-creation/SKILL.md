---
name: twill-block-creation
description: Standard per la creazione di blocchi Twill 3 con corrispondente componente React/Inertia, usando BlockFields per campi condivisi.
---

# Skill: Creazione di Blocchi Twill 3

Questa skill definisce il processo completo per aggiungere un nuovo blocco editoriale al sistema Twill + Inertia/React. Ogni blocco richiede **4 file** e **1 registrazione**.

---

## Architettura del sistema blocchi

```
PHP Block Component  →  Blade placeholder  →  BlockRenderer  →  React Component
  (form + render)         (minimo)             (mapping)          (UI reale)
```

Il rendering visivo avviene **esclusivamente in React**. Il Blade template è un placeholder minimale richiesto da Twill.

---

## Passo 1 — PHP Block Component

**File:** `app/View/Components/Twill/Blocks/{BlockName}.php`

```php
<?php

namespace App\View\Components\Twill\Blocks;

use A17\Twill\Services\Forms\Form;
use A17\Twill\View\Components\Blocks\TwillBlockComponent;
use Illuminate\Contracts\View\View;
use App\Twill\Fields\BlockFields;
use A17\Twill\Services\Forms\Columns;
// importa solo i field usati:
use A17\Twill\Services\Forms\Fields\Input;
use A17\Twill\Services\Forms\Fields\Wysiwyg;
use A17\Twill\Services\Forms\Fields\Checkbox;
use A17\Twill\Services\Forms\Fields\Select;
use A17\Twill\Services\Forms\Fields\Medias;
use A17\Twill\Services\Forms\InlineRepeater;

class BlockName extends TwillBlockComponent
{
    public function render(): View
    {
        return view('components.twill.blocks.block-name');
    }

    public function getForm(): Form
    {
        return Form::make([
            // Usa BlockFields per campi standardizzati (vedi sezione BlockFields)
            ...BlockFields::inputWithSeoTag('title', 'Title'),

            // Campi specifici del blocco
            Wysiwyg::make()->name('text')->translatable(),

            // Colonne per affiancamento
            Columns::make()
                ->left([BlockFields::textColor()])
                ->right([BlockFields::textAlignment()]),

            // Media
            Medias::make()
                ->name('image')
                ->label('Image')
                ->max(1),

            // Repeater inline
            InlineRepeater::make()
                ->name('items')
                ->label('Items')
                ->fields([...BlockFields::ctaFields()])
                ->max(5),
        ]);
    }
}
```

### Regole

- Estendi sempre `TwillBlockComponent`, non `BlockComponent`.
- `render()` punta alla view Blade con notazione dot: `components.twill.blocks.nome-kebab`.
- `getForm()` restituisce `Form::make([...])`, **mai** array `$fields` sul Model.
- Importa solo i field che usi effettivamente.

---

## Passo 2 — Blade Placeholder

**File:** `resources/views/components/twill/blocks/block-name.blade.php`

```blade
<div>
    <h2>{{ $input('title') }}</h2>
    {!! $input('text') !!}
</div>
```

Il Blade serve solo per il preview interno di Twill. Il rendering reale è in React. Includi 1–2 `$input()` rappresentativi, non devi mappare tutti i campi.

---

## Passo 3 — React Component

**File:** `resources/js/components/editorial/BlockName.tsx`

```tsx
import { cva } from 'class-variance-authority';
import type { Block } from '@/lib/types/block';
import { cn } from '@/lib/utils';
// Atomi riutilizzabili:
import Eyelet from '@/components/editorial/atom/Eyelet';
import Title from '@/components/editorial/atom/Title';
import Subtitle from '@/components/editorial/atom/Subtitle';
import Text from '@/components/editorial/atom/Text';
import Cta from '@/components/editorial/atom/Cta';
import Picture from './Picture';

// Varianti CVA per classi condizionali
const sectionClasses = cva('block-name relative', {
    variants: {
        alignment: {
            'text-left': 'text-left',
            'text-center': 'text-center',
            'text-right': 'text-right',
        },
    },
});

export default function BlockName({ block }: { block: Block }) {
    if (!block) return null;

    // Campi content (testo, checkbox, select)
    const alignment = block.content.text_alignment;

    // Immagini: block.images?.{media_name}?.default
    const imageData = block.images?.image?.default || null;

    // Repeater figli: filtrati per tipo
    const ctas =
        block.children?.filter(
            (child) => child.type === 'dynamic-repeater-ctas',
        ) ?? [];

    return (
        // IMPORTANTE: aggiungere 'group' e block.content.text_color come classe
        // Gli atomi (Title, Subtitle, Eyelet, Text) reagiscono automaticamente
        <section className={cn(sectionClasses({ alignment }), 'group', block.content.text_color)}>
            {imageData && (
                <Picture
                    image={imageData}
                    className="..."
                />
            )}

            <div className="container mx-auto p-6">
                <Title
                    content={block.content.title}
                    seoTag={block.content.title_seo}
                />
                <Text content={block.content.text} />

                {ctas.length > 0 && (
                    <div className="flex gap-4">
                        {ctas.map((cta) => (
                            <Cta
                                key={cta.id}
                                cta={cta}
                            />
                        ))}
                    </div>
                )}
            </div>
        </section>
    );
}
```

### Accesso ai dati dal `Block`

| Tipo dato                       | Accesso                                                           |
| ------------------------------- | ----------------------------------------------------------------- |
| Campi testo / select / checkbox | `block.content.field_name`                                        |
| Immagini Twill                  | `block.images?.media_name?.default`                               |
| Figli / Repeater                | `block.children?.filter(c => c.type === 'dynamic-repeater-name')` |

### Atomi disponibili

| Componente   | Props                               | Uso                                       |
| ------------ | ----------------------------------- | ----------------------------------------- |
| `<Title>`    | `content`, `seoTag`, `className`    | Titolo con tag HTML dinamico (h1–h4, div) |
| `<Subtitle>` | `content`, `seoTag`, `className`    | Sottotitolo semantico                     |
| `<Eyelet>`   | `content`, `seoTag`, `className`    | Testo decorativo sopra il titolo          |
| `<Text>`     | `content`, `className`              | HTML body con `dangerouslySetInnerHTML`   |
| `<Cta>`      | `cta`, `className`                  | Button interno/esterno/download           |
| `<Picture>`  | `image`, `imageMobile`, `className` | Immagine responsive desktop/mobile        |

### Sistema colore testo — Tailwind group

Il colore del testo degli atomi è gestito internamente tramite la metodologia `group` di Tailwind. 

**Pattern:** aggiungere `group` e il valore di `text_color` come classe alla `<section>`:

```tsx
<section className={cn('...', 'group', block.content.text_color)}>
```

Gli atomi reagiscono automaticamente all'antenato con le classi `block-text-dark` o `block-text-light`:

| Classe sul `<section>` | Comportamento degli atomi     |
| ---------------------- | ----------------------------- |
| `block-text-dark`      | ogni atomo applica il proprio colore "dark" (es. primary, secondary, black) |
| `block-text-light`     | ogni atomo applica il proprio colore "light" (es. white)                    |

Le varianti specifiche per atomo (primary, secondary, black, white) sono definite direttamente nei `defaultClasses` di ogni componente e non vanno modificate dal blocco chiamante.

---

## Passo 4 — Registrazione in BlockRenderer, HomepageController and PageController

**File:** `resources/js/components/editorial/BlockRenderer.tsx`

Aggiungi l'import e la voce nel `BLOCK_COMPONENTS`:

```tsx
import BlockName from './BlockName';

const BLOCK_COMPONENTS: Record<string, ...> = {
    'hero': Hero,
    'paragraph': Paragraph,
    'block-name': BlockName,  // ← aggiungi qui
};
```

Il tipo del blocco segue la convenzione `{nome-kebab}` (es. `card-list`, `gallery`).

Aggiungi il tipo anche nei file:

- app/Http/Controllers/Twill/HomepageController.php
- app/Http/Controllers/Twill/PageController.php

---

## BlockFields — Campi condivisi

`app/Twill/Fields/BlockFields.php` espone metodi statici per campi riutilizzati tra blocchi. **Usa sempre questi** invece di ridefinire campi equivalenti.

### `BlockFields::textColor()` → `Select`

```php
BlockFields::textColor()
// Campo: text_color | Valori: 'block-text-dark' | 'block-text-light'
```

### `BlockFields::textAlignment()` → `Select`

```php
BlockFields::textAlignment()
// Campo: text_alignment | Valori: 'text-left' | 'text-center' | 'text-right'
```

### `BlockFields::inputWithSeoTag(name, label, required?, translatable?)` → `array`

Restituisce un array con due campi affiancati in `Columns`: un `Input` e un `Select` per il tag HTML semantico (h1–h4, div).

```php
...BlockFields::inputWithSeoTag('title', 'Title'),
// Campi: title (translatable input) + title_seo (select: div|h1|h2|h3|h4)

...BlockFields::inputWithSeoTag('eyelet', 'Eyelet'),
...BlockFields::inputWithSeoTag('subtitle', 'Subtitle'),
```

Usa lo spread `...` perché il metodo restituisce un array (un `Columns` per ogni chiamata).

### `BlockFields::ctaFields()` → `array`

Restituisce i campi per un CTA completo: label (translatable), style (primary/secondary), tipo link (external/internal/download), link esterno o browser per pagine interne, target blank.

```php
InlineRepeater::make()
    ->name('ctas')
    ->fields([...BlockFields::ctaFields()])
    ->max(3)
```

Nel React, i figli del repeater `ctas` hanno `type === 'dynamic-repeater-ctas'` e il loro `content` espone: `cta_label`, `cta_style`, `cta_type`, `cta_external_link`, `cta_target_blank`. Il campo `cta_link` nel componente `Cta` viene valorizzato dalla risoluzione del browser interno.

---

## Aggiungere un campo a BlockFields

Se un nuovo campo è usato in 2+ blocchi, aggiungilo come metodo statico a `BlockFields.php` invece di duplicarlo.

```php
public static function nomeDelCampo(): FieldType
{
    return FieldType::make()
        ->name('field_name')
        ->label('Label')
        ->options([...]);
}
```

---

## Checklist creazione blocco

- [ ] `app/View/Components/Twill/Blocks/{Name}.php` — extend `TwillBlockComponent`, implementa `render()` e `getForm()`
- [ ] `resources/views/components/twill/blocks/{name}.blade.php` — placeholder Blade minimale
- [ ] `resources/js/components/editorial/{Name}.tsx` — componente React con accesso corretto a `block.content`, `block.images`, `block.children`
- [ ] `BlockRenderer.tsx` — import + entry `'{name}'` in `BLOCK_COMPONENTS`
- [ ] Usa `BlockFields::*` per textColor, textAlignment, inputWithSeoTag, ctaFields invece di ridefinirli
- [ ] Usa atomi (`Title`, `Subtitle`, `Eyelet`, `Text`, `Cta`, `Picture`) dove applicabile
- [ ] Se il blocco usa `BlockFields::textColor()`: aggiungere `group` e `block.content.text_color` alla `<section>`, **non** usare `getBlockColorPreferences` né passare `textColorClass` agli atomi
- [ ] Nessuna logica in `render()`, nessuna query in PHP — solo form definition
- [ ] Componente React SSR-safe: no `window`/`document` nel body, solo in `useEffect`

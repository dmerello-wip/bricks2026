# Translations

The project uses a two-layer translation system: PHP files for backend strings, JSON files for the frontend, kept in sync by a dedicated package.

---

## Architecture

```
lang/{locale}/*.php          ← source of truth (edit here)
    ↓
artisan translation-handler:import
    ↓
resources/js/lang/{locale}/translation.json   ← consumed by i18next
```

**Backend** uses Laravel's native `trans()` helpers, backed by files in `lang/`.
**Frontend** uses [i18next](https://www.i18next.com/) + [react-i18next](https://react.i18next.com/), reading from `resources/js/lang/`.
The [`brunoscode/laravel-translation-handler`](https://github.com/brunoscode/laravel-translation-handler) package bridges the two: it converts PHP arrays to nested JSON when importing, and JSON back to PHP when exporting.

---

## Supported Locales

| Locale | Language |
|---|---|
| `en` | English (default) |
| `it` | Italian |

Configured in `config/laravellocalization.php` and `config/translatable.php`.

---

## Translation Files

**PHP (backend / source):**
- `lang/en/app.php` — UI strings
- `lang/en/routes.php` — Localized route prefixes (for URL segment translation)
- `lang/it/app.php`, `lang/it/routes.php` — Italian equivalents

**JSON (generated / frontend):**
- `resources/js/lang/en/translation.json`
- `resources/js/lang/it/translation.json`

---

## Syncing PHP → JSON

After editing PHP translation files, regenerate the JSON:

```bash
make translations
# equivalent to:
vendor/bin/sail artisan translation-handler:import --force --fresh
```

The `--fresh` flag clears existing JSON before regenerating. Commit the updated JSON files.

---

## URL Localization

Routes are prefixed with the locale via [`mcamara/laravel-localization`](https://github.com/mcamara/laravel-localization):

```
/en/about
/it/chi-siamo
```

Route prefixes themselves can be translated via `lang/{locale}/routes.php`:

```php
// lang/it/routes.php
return [
    'projects' => 'progetti',
];
```

```php
// routes/web.php — using the localizedModule macro
Route::localizedModule('projects', ProjectController::class, 'project');
// generates /en/projects/{slug} and /it/progetti/{slug}
```

The current locale and all localized URL variants are shared with every Inertia page via `HandleInertiaRequests::share()` (`locale`, `locales`, `localizedURL`).

---

## Using Translations in React

Setup is in [`resources/js/i18n.ts`](../resources/js/i18n.ts) and initialized in both `app.tsx` (client) and `ssr.tsx` (server). The `i18n` instance is provided via `<I18nextProvider>`.

**In components:**

```tsx
import { useTranslation } from 'react-i18next';

export default function MyComponent() {
    const { t } = useTranslation();
    return <p>{t('app.description')}</p>;
}
```

Keys mirror the nested structure of the PHP files:

```php
// lang/en/app.php
return ['description' => 'A starter CMS...'];
```
```json
// resources/js/lang/en/translation.json
{ "app": { "description": "A starter CMS..." } }
```
```tsx
t('app.description') // → 'A starter CMS...'
```

**Current locale** is available from Inertia shared props:

```tsx
import { usePage } from '@inertiajs/react';
import type { SharedData } from '@/lib/types';

const { locale } = usePage<SharedData>().props;
```

---

## Using Translations in PHP

```php
trans('app.description');              // current locale
trans('app.description', [], 'it');    // explicit locale
trans('routes.projects');              // localized route prefix
```

---

## Database Model Translations

CMS content (titles, descriptions, slugs) stored per-locale in the database uses Twill's built-in translatable fields — not the translation files above. Those are for UI strings only.

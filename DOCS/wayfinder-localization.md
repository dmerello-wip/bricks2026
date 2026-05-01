# Wayfinder + Localizzazione

## Contesto

Il routing multilingua non usa `mcamara/laravel-localization`. Il locale viene estratto dal primo segmento dell'URL tramite il middleware `SetLocale` e applicato con `App::setLocale()`.

Il gruppo di route ha `{locale}` come prefisso fisso, quindi tutte le route localizzate vivono sotto `/{locale}/...`.

---

## Route Macros

Definite in `AppServiceProvider::registerRouteMacros()`, costruiscono route che accettano tutti i prefissi tradotti tramite regex.

### `localizedModule` — modulo semplice

```php
// Pattern: /{locale}/{prefix}/{slug}
Route::localizedModule('projects', ProjectController::class, 'project');
// → /it/progetti/{slug}
// → /en/projects/{slug}
```

Macro:

```php
Route::macro('localizedModule', function (string $routeKey, string $controller, string $routeName, string $slugPattern = '[^/]+'): void {
    $prefixes = collect(array_keys(config('app.supported_locales')))
        ->map(fn ($locale) => trans("routes.$routeKey", [], $locale))
        ->unique()
        ->join('|');

    Route::get('{prefix}/{slug}', [$controller, 'show'])
        ->where('prefix', $prefixes)
        ->where('slug', $slugPattern)
        ->name($routeName);
});
```

### `localizedCategorizedArticle` — modulo con categoria

```php
// Pattern: /{locale}/{prefix}/{categorySlug}/{slug}
Route::localizedCategorizedArticle('articles', ArticleController::class, 'article');
// → /it/articoli/{categorySlug}/{slug}
// → /en/articles/{categorySlug}/{slug}
```

Macro:

```php
Route::macro('localizedCategorizedArticle', function (string $routeKey, string $controller, string $routeName, string $slugPattern = '[^/]+'): void {
    $prefixes = collect(array_keys(config('app.supported_locales')))
        ->map(fn ($locale) => trans("routes.$routeKey", [], $locale))
        ->unique()
        ->join('|');

    Route::get('{prefix}/{categorySlug}/{slug}', [$controller, 'show'])
        ->where('prefix', $prefixes)
        ->where('categorySlug', '[^/]+')
        ->where('slug', $slugPattern)
        ->name($routeName);
});
```

---

## Traduzione dei segmenti route

I prefissi tradotti vivono in `lang/{locale}/routes.php`. Il valore è **solo il segmento** (senza slash):

```php
// lang/it/routes.php
return ['articles' => 'articoli'];

// lang/en/routes.php
return ['articles' => 'articles'];
```

---

## Generazione URL nel backend

Usa sempre `route()` con i parametri espliciti:

```php
// localizedModule (es. projects)
route('project', [
    'locale'  => app()->getLocale(),
    'prefix'  => trans('routes.projects'),
    'slug'    => $project->getSlug(),
]);

// localizedCategorizedArticle
route('article', [
    'locale'       => app()->getLocale(),
    'prefix'       => trans('routes.articles'),
    'categorySlug' => $category->getSlug(),
    'slug'         => $article->getSlug(),
]);
```

---

## Prefissi condivisi via Inertia

`HandleInertiaRequests::share()` espone i prefissi tradotti come prop condivisa:

```php
'routePrefixes' => collect(config('translatable.locales'))
    ->mapWithKeys(fn (string $l) => [$l => trans('routes', [], $l)])
    ->toArray(),
// → { "it": { "articles": "articoli" }, "en": { "articles": "articles" } }
```

Tipizzata in `SharedData`:

```typescript
routePrefixes: Record<string, Record<string, string>>;
```

---

## Hook `useModuleUrl`

`resources/js/lib/useModuleUrl.ts` combina i prefissi condivisi con le funzioni Wayfinder:

```typescript
import { usePage } from '@inertiajs/react';
import { show as articleShow } from '@/actions/App/Http/Controllers/ArticleController';
import type { SharedData } from '@/lib/types';

export function useModuleUrl() {
    const { locale, routePrefixes } = usePage<SharedData>().props;
    const prefixes = routePrefixes[locale] ?? {};

    return {
        article: {
            url: (slug: string) =>
                articleShow.url({ prefix: prefixes.article ?? 'article', slug }),
            show: (slug: string) =>
                articleShow({ prefix: prefixes.article ?? 'article', slug }),
        },
    };
}
```

---

## Uso nei componenti Inertia/React

### Link a un articolo

```tsx
import { Link } from '@inertiajs/react';
import { useModuleUrl } from '@/lib/useModuleUrl';
import type { ArticleModel } from '@/lib/types';

function ArticleCard({ article, categorySlug }: { article: ArticleModel; categorySlug: string }) {
    const { article: articleUrl } = useModuleUrl();

    return (
        <Link href={articleUrl.url(categorySlug, article.slug)}>
            {article.title}
        </Link>
    );
}
```

`articleUrl.url()` restituisce una stringa, `articleUrl.show()` restituisce `{ url, method }` — utile con `<Form>` o `router.visit()`.

### Navigazione imperativa

```tsx
import { router } from '@inertiajs/react';
import { useModuleUrl } from '@/lib/useModuleUrl';

function ArticleList({ articles }) {
    const { article: articleUrl } = useModuleUrl();

    const goToArticle = (categorySlug: string, slug: string) => {
        router.visit(articleUrl.url(categorySlug, slug));
    };
    // ...
}
```

### Language switcher

Il `LanguageSelector` (`resources/js/components/LanguageSelector.tsx`) reindirizza alla homepage della nuova locale. Gli slug possono cambiare tra lingue (es. `chi-siamo` / `about-us`), quindi non è possibile costruire automaticamente l'URL alternativo solo dai prefissi.

Per pagine che supportano il cambio lingua preservando il contenuto, il controller deve passare gli URL già risolti come prop:

```php
// Nel controller
'localizedUrls' => collect(array_keys(config('app.supported_locales')))
    ->mapWithKeys(fn (string $locale) => [
        $locale => route('article', [
            'locale'       => $locale,
            'prefix'       => trans('routes.articles', [], $locale),
            'categorySlug' => $category->translate($locale)?->slug ?? $category->getSlug(),
            'slug'         => $article->translate($locale)?->slug ?? $article->getSlug(),
        ])
    ])
    ->toArray(),
```

Nel componente:

```tsx
import { usePage } from '@inertiajs/react';

const { localizedUrls } = usePage<{ localizedUrls: Record<string, string> }>().props;
// localizedUrls.it → /it/articoli/sport/chi-siamo
// localizedUrls.en → /en/articles/sport/about-us
```

---

## Cosa genera Wayfinder

Per `localizedModule` (`/{locale}/{prefix}/{slug}`):

```typescript
// @/actions/App/Http/Controllers/ProjectController
show({ prefix: string | number, slug: string | number })
// → { url: '/{locale}/{prefix}/{slug}', method: 'get' }
```

Per `localizedCategorizedArticle` (`/{locale}/{prefix}/{categorySlug}/{slug}`):

```typescript
// @/actions/App/Http/Controllers/ArticleController
show({ prefix: string | number, categorySlug: string | number, slug: string | number })
// → { url: '/{locale}/{prefix}/{categorySlug}/{slug}', method: 'get' }
```

Il parametro `locale` è gestito dal gruppo di route — Wayfinder lo vede come parte del prefisso del gruppo, non come parametro esplicito della singola route.

---

## Aggiungere un nuovo modulo

1. Aggiungere la chiave in `lang/it/routes.php` e `lang/en/routes.php`:
   ```php
   'projects' => 'progetti',   // it
   'projects' => 'projects',   // en
   ```

2. Registrare la route in `routes/web.php` (prima del catch-all):
   ```php
   Route::localizedModule('projects', ProjectController::class, 'project');
   ```

3. Estendere `useModuleUrl.ts`:
   ```typescript
   import { show as projectShow } from '@/actions/App/Http/Controllers/ProjectController';

   // dentro useModuleUrl():
   project: {
       url: (slug: string) =>
           projectShow.url({ prefix: prefixes.projects ?? 'projects', slug }),
       show: (slug: string) =>
           projectShow({ prefix: prefixes.projects ?? 'projects', slug }),
   },
   ```

4. Nel Twill module controller, aggiungere `getLocalizedPermalinkBase()` con le lingue abilitate:
   ```php
   protected function getLocalizedPermalinkBase(): array
   {
       return [
           'it' => trans('routes.projects', [], 'it'),
           'en' => trans('routes.projects', [], 'en'),
       ];
   }
   ```

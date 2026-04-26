# Wayfinder vs Ziggy — Scelta e motivazioni

## Contesto

Entrambi i package risolvono lo stesso problema: rendere le route Laravel accessibili
dal frontend JavaScript/TypeScript senza scrivere URL a mano.

Questo progetto usa **Wayfinder** (`laravel/wayfinder` + `@laravel/vite-plugin-wayfinder`).

---

## Confronto

### Wayfinder

**Pro**
- **Type-safe a compile time**: ogni rotta diventa una funzione TypeScript con parametri
  tipizzati. Gli errori (parametro mancante, nome sbagliato) emergono nell'IDE prima ancora
  di avviare il browser.
- **Autocompletamento completo**: IDE mostra i parametri disponibili e i loro tipi.
- **Zero overhead a runtime**: i tipi vengono risolti a build time, nessun oggetto globale
  iniettato nel DOM.
- **Nessun bundle overhead**: genera solo le rotte che esistono, non le esporta tutte.
- **Integrazione nativa con Inertia**: `show.form()`, `show.url()`, `.get`, `.head` —
  pensato per il pattern controller → Inertia page.

**Contro**
- Richiede il plugin Vite: i tipi si aggiornano solo con dev server attivo o dopo un rebuild.
- I file generati in `resources/js/actions/` e `resources/js/routes/` devono essere
  committati oppure rigenerati in CI prima del build.
- API più verbosa rispetto a una stringa:
  ```ts
  // Wayfinder
  show({ locale, prefix: trans('articles'), categorySlug: cat.slug, slug: article.slug })

  // vs Ziggy
  route('article', { categorySlug: cat.slug, slug: article.slug })
  ```

---

### Ziggy

**Pro**
- Replica `route()` di Laravel 1:1 lato frontend — curva di apprendimento zero per chi
  conosce Laravel.
- Runtime: le rotte sono sempre aggiornate senza rebuild, utile in sviluppo rapido.
- Funziona con qualsiasi stack JS, non richiede TypeScript né Vite.

**Contro**
- **Nessun type safety**: `route('article', { slug: 123 })` restituisce una stringa;
  errori di parametro appaiono solo a runtime nel browser.
- **Inietta tutte le rotte nel bundle** (incluse quelle Twill admin, API, ecc.) — richiede
  filtro manuale con `only`/`except` per non esporre route interne.
- Dipende da un oggetto globale `Ziggy` iniettato nel DOM o via Inertia shared data.
- In un progetto con SSR abilitato richiede configurazione aggiuntiva per garantire che
  l'oggetto `Ziggy` sia disponibile lato server.

---

## Perché Wayfinder in questo progetto

Questo progetto è costruito su **TypeScript + React + Inertia v2 con SSR abilitato**.
Wayfinder è progettato esattamente per questo caso d'uso:

1. **SSR compatibility**: non dipende da oggetti globali runtime — i file generati sono
   moduli ES puri, importabili sia lato client che lato server Node.

2. **Rotte con parametri multipli**: la rotta articolo
   `/{locale}/{prefix}/{categorySlug}/{slug}` ha 4 parametri. Wayfinder genera una
   funzione con tutti e 4 tipizzati, rendendo impossibile dimenticare `categorySlug`:
   ```ts
   // resources/js/actions/App/Http/Controllers/ArticleController.ts
   show({ locale, prefix, categorySlug, slug })
   ```
   Con Ziggy lo stesso errore sarebbe silenzioso fino al 404 in produzione.

3. **Refactoring sicuro**: se una rotta cambia (es. si rinomina `categorySlug` in
   `category`), il file TypeScript viene rigenerato e il compilatore evidenzia tutti i
   punti del frontend da aggiornare.

4. **Nessuna route sensibile esposta**: il bundle frontend non contiene le rotte Twill
   admin o API — Wayfinder genera solo i file per i controller che hanno rotte nominate
   accessibili pubblicamente.

---

## Quando considerare Ziggy invece

- Progetto senza TypeScript (plain JavaScript).
- Stack non-Vite (es. Mix, Webpack standalone).
- Necessità di chiamare route dinamicamente con nomi costruiti a runtime
  (es. `route(\`module.${action}\`)`).
- Prototipo rapido dove la velocità di sviluppo conta più della type safety.

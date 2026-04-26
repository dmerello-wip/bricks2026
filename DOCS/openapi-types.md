# OpenAPI Type Generation Pipeline

TypeScript types for all Laravel-driven data shapes are **generated** from PHP `#[OA\Schema]` attributes — not written by hand. This keeps the frontend types in sync with the backend automatically.

---

## Pipeline

```
PHP #[OA\Schema] attributes
    ↓
sail artisan l5-swagger:generate
    ↓
storage/api-docs/api-docs.json
    ↓
npm run generate-swagger-types
    ↓
resources/js/Types/swagger.ts   ← auto-generated, do not edit
```

**Commands:**
```bash
make swagger
# equivalent to:
vendor/bin/sail artisan l5-swagger:generate
vendor/bin/sail npm run generate-swagger-types
```

Run after adding or modifying a schema. The generated `swagger.ts` is committed to the repository.

---

## Schema Placement Rules

Where a schema lives depends on what it describes:

| What it describes | Where the schema goes |
|---|---|
| A Laravel Model | On the model class itself (`app/Models/*.php`) |
| A non-model data shape (service output, vendor data) | `app/OpenApi/Schemas/` |

**Do not** place schemas on service classes — services are for behavior, not data description.

### Current schemas

**On model classes:**
- [`app/Models/Homepage.php`](../app/Models/Homepage.php) — `HomepageModel` schema
- [`app/Models/Page.php`](../app/Models/Page.php) — `PageModel` schema

**In `app/OpenApi/Schemas/`:**

| File | Schema(s) | Describes |
|---|---|---|
| [`Block.php`](../app/OpenApi/Schemas/Block.php) | `Block` | Formatted block from `TwillBlockService::formatBlock()` |
| [`Cta.php`](../app/OpenApi/Schemas/Cta.php) | `CtaContent`, `CtaBlock` | CTA block content; `CtaBlock` extends `Block` via `allOf` |
| [`ImageData.php`](../app/OpenApi/Schemas/ImageData.php) | `ImageData` | Processed image data from `ImageService::buildImageData()` |
| [`SeoData.php`](../app/OpenApi/Schemas/SeoData.php) | `SeoData` | Resolved SEO payload from `SeoService::resolve()` |
| [`TwillMedia.php`](../app/OpenApi/Schemas/TwillMedia.php) | `TwillMedia` | Raw Twill media model from the `medias()` relationship |

---

## Adding a New Schema

### 1. Model schema — place it on the model class

```php
// app/Models/Post.php
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PostModel',
    required: ['id', 'published'],
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'published', type: 'boolean'),
        new OA\Property(property: 'title', type: 'string', nullable: true),
        new OA\Property(property: 'medias', type: 'array', items: new OA\Items(ref: '#/components/schemas/TwillMedia')),
    ]
)]
class Post extends Model { ... }
```

### 2. Non-model schema — create a class in `app/OpenApi/Schemas/`

```php
// app/OpenApi/Schemas/PostContent.php
namespace App\OpenApi\Schemas;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PostContent',
    required: ['body'],
    properties: [
        new OA\Property(property: 'body', type: 'string', nullable: true),
    ]
)]
class PostContent {}
```

### 3. Extending another schema (`allOf`)

Use `allOf` when one schema extends another (e.g. a typed block variant):

```php
#[OA\Schema(
    schema: 'PostBlock',
    allOf: [
        new OA\Schema(ref: '#/components/schemas/Block'),
        new OA\Schema(
            properties: [
                new OA\Property(property: 'content', ref: '#/components/schemas/PostContent'),
            ]
        ),
    ]
)]
class PostContent {}
```

This generates `Block & { content?: PostContent }` in TypeScript.

### 4. Regenerate

```bash
vendor/bin/sail artisan l5-swagger:generate
vendor/bin/sail npm run generate-swagger-types
```

### 5. Export from the types barrel

Add your new type to the named exports in [`resources/js/lib/types/index.ts`](../resources/js/lib/types/index.ts):

```typescript
export type { HomepageModel, PageModel, ..., PostModel, PostContent, PostBlock } from '@/Types/swagger';
```

---

## Frontend Types (`resources/js/lib/types/`)

All frontend types are imported from `@/lib/types`. The barrel [`index.ts`](../resources/js/lib/types/index.ts) re-exports everything from two sources:

| Source | Types | Reason |
|---|---|---|
| `@/Types/swagger` (generated) | `HomepageModel`, `PageModel`, `Block`, `CtaContent`, `CtaBlock`, `ImageData`, `SeoData`, `TwillMedia` | These mirror Laravel data shapes — generated from PHP keeps them in sync |
| Manual files in `@/lib/types/` | `Auth`, `MenuItem`, UI types, `SharedData` | These reference Inertia/React/Lucide — no PHP representation possible |

**Rule of thumb:** if the shape is defined by a Laravel service, model, or block formatter — it belongs in the swagger pipeline. If it's frontend-specific (Inertia page props structure, icon variants, component state) — it stays manual.

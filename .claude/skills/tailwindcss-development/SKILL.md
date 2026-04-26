---
name: tailwindcss-development
description: "Styles applications using Tailwind CSS v4 utilities. Activates when adding styles, restyling components, working with gradients, spacing, layout, flex, grid, responsive design, dark mode, colors, typography, or borders; or when the user mentions CSS, styling, classes, Tailwind, restyle, hero section, cards, buttons, or any visual/UI changes."
license: MIT
metadata:
  author: laravel
---

# Tailwind CSS Development

## When to Apply

Activate this skill when:

- Adding styles to components or pages
- Working with responsive design
- Implementing dark mode
- Extracting repeated patterns into components
- Debugging spacing or layout issues

## Documentation

Use `search-docs` for detailed Tailwind CSS v4 patterns and documentation.

## Basic Usage

- Use Tailwind CSS classes to style HTML. Check and follow existing Tailwind conventions in the project before introducing new patterns.
- Offer to extract repeated patterns into components that match the project's conventions (e.g., Blade, JSX, Vue).
- Consider class placement, order, priority, and defaults. Remove redundant classes, add classes to parent or child elements carefully to reduce repetition, and group elements logically.

## Tailwind CSS v4 Specifics

- Always use Tailwind CSS v4 and avoid deprecated utilities.
- `corePlugins` is not supported in Tailwind v4.

### CSS-First Configuration

In Tailwind v4, configuration is CSS-first using the `@theme` directive — no separate `tailwind.config.js` file is needed:

<!-- CSS-First Config -->
```css
@theme {
  --color-brand: oklch(0.72 0.11 178);
}
```

### Import Syntax

In Tailwind v4, import Tailwind with a regular CSS `@import` statement instead of the `@tailwind` directives used in v3:

<!-- v4 Import Syntax -->
```diff
- @tailwind base;
- @tailwind components;
- @tailwind utilities;
+ @import "tailwindcss";
```

### Replaced Utilities

Tailwind v4 removed deprecated utilities. Use the replacements shown below. Opacity values remain numeric.

| Deprecated | Replacement |
|------------|-------------|
| bg-opacity-* | bg-black/* |
| text-opacity-* | text-black/* |
| border-opacity-* | border-black/* |
| divide-opacity-* | divide-black/* |
| ring-opacity-* | ring-black/* |
| placeholder-opacity-* | placeholder-black/* |
| flex-shrink-* | shrink-* |
| flex-grow-* | grow-* |
| overflow-ellipsis | text-ellipsis |
| decoration-slice | box-decoration-slice |
| decoration-clone | box-decoration-clone |

## Spacing

Use `gap` utilities instead of margins for spacing between siblings:

<!-- Gap Utilities -->
```html
<div class="flex gap-8">
    <div>Item 1</div>
    <div>Item 2</div>
</div>
```

## Dark Mode

If existing pages and components support dark mode, new pages and components must support it the same way, typically using the `dark:` variant:

<!-- Dark Mode -->
```html
<div class="bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
    Content adapts to color scheme
</div>
```

## Common Patterns

### Flexbox Layout

<!-- Flexbox Layout -->
```html
<div class="flex items-center justify-between gap-4">
    <div>Left content</div>
    <div>Right content</div>
</div>
```

### Grid Layout

<!-- Grid Layout -->
```html
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <div>Card 1</div>
    <div>Card 2</div>
    <div>Card 3</div>
</div>
```

## Class Variance Authority (CVA)

When a component has multiple conditional class sets — especially across multiple elements — use `class-variance-authority` instead of inline ternaries or long `cn()` calls.

**When to use CVA:**
- A component has 2+ boolean or enum props that affect classes
- The same variant logic is repeated across multiple elements in the component
- `cn()` calls become long and hard to read

**When NOT to use CVA:**
- A single simple condition (`isActive ? 'bg-blue' : 'bg-gray'`)
- One-off utility classes with no variants

**Pattern:** Define CVA functions outside the component, one per element that has variants. Pass the prop values as variant keys at render time.

```tsx
import { cva } from 'class-variance-authority';

const cardVariants = cva('rounded-lg border p-4', {
    variants: {
        intent: {
            primary: 'border-blue-500 bg-blue-50',
            danger: 'border-red-500 bg-red-50',
        },
        size: {
            sm: 'text-sm',
            lg: 'text-lg',
        },
    },
});

const cardIconVariants = cva('shrink-0', {
    variants: {
        intent: {
            primary: 'text-blue-500',
            danger: 'text-red-500',
        },
    },
});

// Usage
<div className={cardVariants({ intent, size })}>
    <Icon className={cardIconVariants({ intent })} />
</div>
```

**CVA vs `group-data-[...]` selectors:** Prefer CVA over Tailwind's `group-data-[]` pattern when variant logic is driven by React props. CVA resolves classes at render time and is far more readable. Reserve `group-data-[]` only for pure CSS interactions with no React-side logic.

## Class Ordering (prettier-plugin-tailwindcss)

This project uses `prettier-plugin-tailwindcss` configured to sort Tailwind classes inside `cn()`, `clsx()`, and `cva()` calls (via `tailwindFunctions` in `.prettierrc`).

**Rules:**
- After writing or modifying Tailwind classes, run `vendor/bin/sail npm run format` — the plugin auto-sorts everything.
- Do NOT manually reorder classes to match the plugin output; let the formatter do it to avoid noisy diffs.
- Always preserve `cn()` for merging/conditional logic and `cva()` for variant-driven class sets. Never flatten them into static strings.
- Write classes in rough canonical order to keep diffs readable before formatting: layout → positioning → box-model → sizing → spacing → typography → colors/visual → transitions.

**Common pitfalls:**
- Replacing `cn()` with a static string just to "clean up" — loses conditional merge logic.
- Replacing `cva()` with inline ternaries — makes variant logic unreadable and harder to extend.
- Forgetting to run `format` after a styling change — class order diverges from the project convention.

## Common Pitfalls

- Using deprecated v3 utilities (bg-opacity-*, flex-shrink-*, etc.)
- Using `@tailwind` directives instead of `@import "tailwindcss"`
- Trying to use `tailwind.config.js` instead of CSS `@theme` directive
- Using margins for spacing between siblings instead of gap utilities
- Forgetting to add dark mode variants when the project uses dark mode
# Getting Started

**initial setup**
`cp .env.example .env.local`
`ln -s .env.local .env`
`make init`

**start development mode**
`make dev`

**SSR Testing**
`make ssr`

_you should see in view-source the server side generated content_

**Regenerate OpenAPI types** (after editing PHP schemas)
`make swagger`

**Sync PHP translations → frontend JSON**
`make translations`

**Regenerate all generated types** (translations + swagger)
`make types`

**log di mysql if needed**
`sail logs -f mysql`

# Directories

## General

- `/public` => resources served as is
- `/scripts` => command line scripts for general repository/project/environments maintainance.

## Front-End

- `resources/views` => Twill blocks configurations
- `resources/css/app.css` => Tailwind configurations
- `resources/js`
    - `/components` => React component root
        - `/editorial` => All editorial components and their `atoms`.
        - `/form` => All form components and preconfigured `fields`
        - `/layout` => Layout components and their dependencies, grouped by section/area/domain.
        - `/ui` => General ui components (buttons, links, inputs, ...)
    - `/pages` => Inertia-React controller components tree. Should reflect application routing and contain route specific React components.
    - `/lib` => Non react application code, possibly grouped by section/area/domain
        - `/types` => Global types
        - `/utils.ts` => Global utilities


--- 

# DOCUMENTATION

Read Documentation in **DOCS/{single-topic}.md**

- [Image Cropping System](DOCS/cropping.md)
- [Frontend General](DOCS/frontend-general.md)
- [ImageService Usage](DOCS/image-service-usage.md)
- [Navigation System](DOCS/navigations.md)
- [OpenAPI Type Generation](DOCS/openapi-types.md)
- [Block Editor Preview with Inertia/React](DOCS/preview-with-inertia.md)
- [SEO](DOCS/seo.md)
- [Translations](DOCS/translations.md)
- [Debugging PHP (Xdebug)](DOCS/debugging-php.md)
- [Todo](DOCS/todo.md)


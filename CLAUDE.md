# Rules

- Additional project-specific rules are located in the `.claude/rules/` directory. 
- Please prioritize rules found in `.claude/rules/twill.md` when working on CMS modules.

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.5.2
- inertiajs/inertia-laravel (INERTIA_LARAVEL) - v2
- laravel/fortify (FORTIFY) - v1
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- laravel/socialite (SOCIALITE) - v5
- laravel/wayfinder (WAYFINDER) - v0
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- @inertiajs/react (INERTIA_REACT) - v2
- react (REACT) - v19
- tailwindcss (TAILWINDCSS) - v4
- @laravel/vite-plugin-wayfinder (WAYFINDER_VITE) - v0
- eslint (ESLINT) - v9
- prettier (PRETTIER) - v3

## Skills Activation

This project has domain-specific skills available. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

- `wayfinder-development` — Activates whenever referencing backend routes in frontend components. Use when importing from @/actions or @/routes, calling Laravel routes from TypeScript, or working with Wayfinder route functions.
- `pest-testing` — Tests applications using the Pest 4 PHP framework. Activates when writing tests, creating unit or feature tests, adding assertions, testing Livewire components, browser testing, debugging test failures, working with datasets or mocking; or when the user mentions test, spec, TDD, expects, assertion, coverage, or needs to verify functionality works.
- `inertia-react-development` — Develops Inertia.js v2 React client-side applications. Activates when creating React pages, forms, or navigation; using <Link>, <Form>, useForm, or router; working with deferred props, prefetching, or polling; or when user mentions React with Inertia, React pages, React forms, or React navigation.
- `tailwindcss-development` — Styles applications using Tailwind CSS v4 utilities. Activates when adding styles, restyling components, working with gradients, spacing, layout, flex, grid, responsive design, dark mode, colors, typography, or borders; or when the user mentions CSS, styling, classes, Tailwind, restyle, hero section, cards, buttons, or any visual/UI changes.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `vendor/bin/sail npm run build`, `vendor/bin/sail npm run dev`, or `vendor/bin/sail composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan Commands

- Run Artisan commands directly via the command line (e.g., `vendor/bin/sail artisan route:list`, `vendor/bin/sail artisan tinker --execute "..."`).
- Use `vendor/bin/sail artisan list` to discover available commands and `vendor/bin/sail artisan [command] --help` to check parameters.

## URLs

- Whenever you share a project URL with the user, you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain/IP, and port.

## Debugging

- Use the `database-query` tool when you only need to read from the database.
- Use the `database-schema` tool to inspect table structure before writing migrations or models.
- To execute PHP code for debugging, run `vendor/bin/sail artisan tinker --execute "your code here"` directly.
- To read configuration values, read the config files directly or run `vendor/bin/sail artisan config:show [key]`.
- To inspect routes, run `vendor/bin/sail artisan route:list` directly.
- To check environment variables, read the `.env` file directly.

## Reading Browser Logs With the `browser-logs` Tool

- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)

- Boost comes with a powerful `search-docs` tool you should use before trying other approaches when working with Laravel or Laravel ecosystem packages. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic-based queries at once. For example: `['rate limiting', 'routing rate limiting', 'routing']`. The most relevant results will be returned first.
- Do not add package names to queries; package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'.
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit".
3. Quoted Phrases (Exact Position) - query="infinite scroll" - words must be adjacent and in that order.
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit".
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms.

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.

## Constructors

- Use PHP 8 constructor property promotion in `__construct()`.
    - `public function __construct(public GitHub $github) { }`
- Do not allow empty `__construct()` methods with zero parameters unless the constructor is private.

## Type Declarations

- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<!-- Explicit Return Types and Method Params -->
```php
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
```

## Enums

- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.

## Comments

- Prefer PHPDoc blocks over inline comments. Never use comments within the code itself unless the logic is exceptionally complex.

## PHPDoc Blocks

- Add useful array shape type definitions when appropriate.

=== sail rules ===

# Laravel Sail

- This project runs inside Laravel Sail's Docker containers. You MUST execute all commands through Sail.
- Start services using `vendor/bin/sail up -d` and stop them with `vendor/bin/sail stop`.
- Open the application in the browser by running `vendor/bin/sail open`.
- Always prefix PHP, Artisan, Composer, and Node commands with `vendor/bin/sail`. Examples:
    - Run Artisan Commands: `vendor/bin/sail artisan migrate`
    - Install Composer packages: `vendor/bin/sail composer install`
    - Execute Node commands: `vendor/bin/sail npm run dev`
    - Execute PHP scripts: `vendor/bin/sail php [script]`
- View all available Sail commands by running `vendor/bin/sail` without arguments.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `vendor/bin/sail artisan test --compact` with a specific filename or filter.

=== inertia-laravel/core rules ===

# Inertia

- Inertia creates fully client-side rendered SPAs without modern SPA complexity, leveraging existing server-side patterns.
- Components live in `resources/js/pages` (unless specified in `vite.config.js`). Use `Inertia::render()` for server-side routing instead of Blade views.
- ALWAYS use `search-docs` tool for version-specific Inertia documentation and updated code examples.
- IMPORTANT: Activate `inertia-react-development` when working with Inertia client-side patterns.

# Inertia v2

- Use all Inertia features from v1 and v2. Check the documentation before making changes to ensure the correct approach.
- New features: deferred props, infinite scrolling (merging props + `WhenVisible`), lazy loading on scroll, polling, prefetching.
- When using deferred props, add an empty state with a pulsing or animated skeleton.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `vendor/bin/sail artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `vendor/bin/sail artisan list` and check their parameters with `vendor/bin/sail artisan [command] --help`.
- If you're creating a generic PHP class, use `vendor/bin/sail artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

## Database

- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries.
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `vendor/bin/sail artisan make:model --help` to check the available options.

### APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## Controllers & Validation

- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

## Authentication & Authorization

- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Queues

- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

## Configuration

- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `vendor/bin/sail artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `vendor/bin/sail npm run build` or ask the user to run `vendor/bin/sail npm run dev` or `vendor/bin/sail composer run dev`.

=== laravel/v12 rules ===

# Laravel 12

- CRITICAL: ALWAYS use `search-docs` tool for version-specific Laravel documentation and updated code examples.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

## Laravel 12 Structure

- In Laravel 12, middleware are no longer registered in `app/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- The `app\Console\Kernel.php` file no longer exists; use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Console commands in `app/Console/Commands/` are automatically available and do not require manual registration.

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== wayfinder/core rules ===

# Laravel Wayfinder

Wayfinder generates TypeScript functions for Laravel routes. Import from `@/actions/` (controllers) or `@/routes/` (named routes).

- IMPORTANT: Activate `wayfinder-development` skill whenever referencing backend routes in frontend components.
- Invokable Controllers: `import StorePost from '@/actions/.../StorePostController'; StorePost()`.
- Parameter Binding: Detects route keys (`{post:slug}`) — `show({ slug: "my-post" })`.
- Query Merging: `show(1, { mergeQuery: { page: 2, sort: null } })` merges with current URL, `null` removes params.
- Inertia: Use `.form()` with `<Form>` component or `form.submit(store())` with useForm.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/sail bin pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/sail bin pint --test --format agent`, simply run `vendor/bin/sail bin pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `vendor/bin/sail artisan make:test --pest {name}`.
- Run tests: `vendor/bin/sail artisan test --compact` or filter: `vendor/bin/sail artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.
- CRITICAL: ALWAYS use `search-docs` tool for version-specific Pest documentation and updated code examples.
- IMPORTANT: Activate `pest-testing` every time you're working with a Pest or testing-related task.

=== inertia-react/core rules ===

# Inertia + React

- IMPORTANT: Activate `inertia-react-development` when working with Inertia React client-side patterns.

=== tailwindcss/core rules ===

# Tailwind CSS

- Always use existing Tailwind conventions; check project patterns before adding new ones.
- IMPORTANT: Always use `search-docs` tool for version-specific Tailwind CSS documentation and updated code examples. Never rely on training data.
- IMPORTANT: Activate `tailwindcss-development` every time you're working with a Tailwind CSS or styling-related task.

</laravel-boost-guidelines>

<!-- wip-gsd:tech-stack — auto-generated by SessionStart hook. Edit IDs to override detection; delete the block to re-detect next session. -->
<!-- wip-gsd:tech-stack -->
inertia-v2
laravel
pint
shadcn
tailwind
sail
react-inertia
twill3
vite
wayfinder
<!-- /wip-gsd:tech-stack -->

<!-- wip-gsd:skill-triggers — auto-generated by SessionStart hook. Maps task keywords to project skills. Do not edit by hand; edit the `triggers:` list in each SKILL.md. -->
<!-- wip-gsd:skill-triggers -->
Match the user's task against the keywords below. On a match, invoke the named skill via the Skill tool **before** answering from general knowledge. See the **Project Skills** rule in the shared base for when to skip.

| If the task involves… | Invoke |
|---|---|
| use Inertia v2 Form component, deferred props with Inertia::defer, usePoll for polling in Inertia, WhenVisible lazy loading Inertia, Link prefetch Inertia, @inertiajs/react ^2 features | `wip-inertia-v2-v2-features` |
| add an artisan command, create a new artisan command, make:command, add a scheduled command, new console command in Laravel, schedule a command in Kernel.php | `wip-laravel-new-command` |
| add a Laravel controller, create a new controller, make:controller, resource controller in Laravel, invokable controller, route model binding in controller | `wip-laravel-new-controller` |
| add a queued job in Laravel, create a new Laravel job, make:job, dispatch a job to the queue, ShouldQueue job class, background job with retry and backoff | `wip-laravel-new-job` |
| add a database migration, create a new migration in Laravel, make:migration, add a column to an existing table, foreignIdFor in migration, Schema::create new table | `wip-laravel-new-migration` |
| create a new Eloquent model, add a Laravel model, make:model, model with migration factory and seeder, add a relationship to a model, $fillable and $casts on a model | `wip-laravel-new-model` |
| add a Laravel policy, create an authorization policy, make:policy, Gate::authorize in controller, restrict access to a model action, $this->authorize in a controller method | `wip-laravel-new-policy` |
| run pint before commit, pint --dirty, pint --test, pint.json preset, format php files with pint, pint CI check failing | `wip-pint-fix-and-stage` |
| add a form with useForm() in an Inertia React page, submit an Inertia form with form.post or form.put, render server-side validation errors in React Inertia form, use InputError component in an Inertia form, disable submit button with form.processing in Inertia, file upload in an Inertia React form with forceFormData | `wip-react-inertia-new-form-component` |
| add a new Inertia page in React, controller returns Inertia::render for a new screen, create a .tsx page component under Pages for Inertia, set a persistent layout with Page.layout in Inertia React, type Inertia page props with a Props interface in TypeScript, use usePage to read Inertia props in a React component | `wip-react-inertia-new-page` |
| share a prop on every Inertia page via HandleInertiaRequests, add flash messages to Inertia shared props, share auth user across all Inertia pages without repeating it in controllers, use Inertia::lazy for expensive shared props, read shared props with usePage in React, wire up Ziggy routes in Inertia shared props | `wip-react-inertia-share-props` |
| run artisan inside Sail container, ./vendor/bin/sail artisan migrate, sail artisan vs php artisan on host, sail artisan queue:work, sail artisan tinker, sail artisan test command | `wip-sail-exec-artisan` |
| run composer inside Sail container, sail composer require a package, sail composer install vs host composer, composer.lock consistency in Sail project, sail composer update with dependencies, composer --no-dev --optimize-autoloader for CI | `wip-sail-exec-composer` |
| share local Sail environment with client, sail share command, expose Sail to public internet for demo, sail share --subdomain pinning, test OAuth callback with Sail local environment, configure APP_URL for sail share tunnel | `wip-sail-share-port` |
| start the Sail docker stack, sail up -d background startup, sail down to stop containers, sail shell into laravel.test container, bootstrap Sail on a fresh checkout, configure Docker resources for Sail project | `wip-sail-start-stack` |
| replace raw HTML with shadcn components after Figma, figma to shadcn review, swap raw button with shadcn Button, Figma-generated code uses raw HTML instead of shadcn, substitute design system components after figma-implement | `wip-shadcn-figma-review` |
| npx shadcn add, npx shadcn@latest init, shadcn component install, components.json shadcn, shadcn --preset code, add shadcn ui component | `wip-shadcn-overview` |
| organize Tailwind classes in JSX, extract repeated Tailwind class string to constant, use CVA for Tailwind variants, class-variance-authority Tailwind, replace child selector [&_a] with component prop, prettier-plugin-tailwindcss class order | `wip-tailwind-tailwind-classes-organization` |
| add a block to a Twill page, create a new Twill block component, TwillBlockComponent new block, register block in BlockRenderer, new editorial block with React | `wip-twill3-block-creation` |
| enable Twill module preview, HasBlockPreview trait, Inertia Link crash in preview iframe, AppLink preview iframe about:srcdoc, add block preview to Twill admin | `wip-twill3-block-preview` |
| create a Twill module, artisan twill:make:module, generate new Twill module, add module to routes/twill.php, scaffold Twill controller repository migration | `wip-twill3-make-twill-module` |
| update OA\Schema on a model, regenerate swagger.ts types, npm run generate-swagger-types, add OA\Property to model for frontend, l5-swagger:generate TypeScript sync | `wip-twill3-openapi-swagger-types` |
| add SEO fields to a Twill module, SeoFieldset HasSeoData, make a Twill page SEO-editable in admin, SeoService::resolve Inertia page meta, wire SeoHead component to Twill settings | `wip-twill3-seo-fieldset` |
| set up Twill buckets featured content, editor-curated featured items in Twill, FeaturedRepository getForBucket, enable buckets in config/twill.php, multi-module bucketables morph map | `wip-twill3-twill-buckets` |
| configure config/twill.php key, admin_app_subdomain admin_app_url setting, set media_library max_file_size_in_kb, enable Twill feature flag in config, Twill Glide image service configuration | `wip-twill3-twill-config` |
| setUpController Twill module, disableCreate disablePublish enableRevisions in Twill, formData indexData ModuleController override, add TableAction button to Twill listing, Browser field routePrefix modules() in controller | `wip-twill3-twill-controllers` |
| add a field to a Twill form with getForm, Twill Form Builder Input Select Wysiwyg field, Fieldset getSideFieldsets BladePartial in Twill, connectedTo conditional field visibility Twill, getCreateForm Twill admin create modal fields | `wip-twill3-twill-form-builder` |
| add validation to a Twill module form, rulesForCreate rulesForUpdate TwillRequest, repeater field validation rules Twill, failedValidation HANDLE_ERRORS inline repeater errors, rulesForTranslatedFields per-locale validation Twill | `wip-twill3-twill-form-requests` |
| add a link to Twill admin sidebar navigation, TwillNavigation::addLink NavigationLink, registerTwillNavigation AppServiceProvider, config/twill-navigation.php not working in Twill 3, forModule forRoute setChildren navigation Twill | `wip-twill3-twill-navigation` |
| create a nested Twill module, twill:make:module --hasNesting --parentModel, parent-child Twill modules dotted moduleName, self-nesting Twill pages nestedItemsDepth, TwillRoutes::module parents.children route registration | `wip-twill3-twill-nested-modules` |
| afterSave updateBrowser in Twill repository, sync browser relation updateBrowser updateMultiSelect Twill, prepareFieldsBeforeSave ModuleRepository hook, updateRepeater getFormFieldsForRepeater Twill, add filter or search scope to Twill listing query | `wip-twill3-twill-repositories` |
| add a global setting editable in Twill admin, TwillAppSettings::get read a setting value, registerSettingsGroup SettingsGroup in AppServiceProvider, make a setting translatable in Twill settings section, create settings blade section file Twill | `wip-twill3-twill-settings` |
| add a column to Twill listing table, additionalIndexTableColumns getIndexTableColumns Twill, Twill Table Builder Text Relation Image column, add QuickFilter or TableFilter to Twill listing, customRender sortable column in Twill admin index | `wip-twill3-twill-table-builder` |
| add a Vite entry point, new bundle in vite.config.js, add admin entry to vite.config, HMR not working after adding entry, public/build/manifest.json missing entry, laravel-vite-plugin multiple inputs | `wip-vite-new-entry-point` |
| import from @/actions Wayfinder, wayfinder:generate artisan command, replace Ziggy route() call with Wayfinder, store.form() spread into Form component, typed route import from @/routes, Wayfinder named route import | `wip-wayfinder-usage` |
<!-- /wip-gsd:skill-triggers -->


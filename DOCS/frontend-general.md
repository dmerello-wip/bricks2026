## Hooks

 - Prefer `CamelCase` for components, `useCamelCase` for hooks
 - Hooks should be treated as components
 - If a hook doesn't use other hooks, it is a function and not a hook (remove `use` prefix from its name)


## Types

- Types should be declared near/next to the entities they describe as much as possible
- **Laravel-driven types** (models, service outputs, block shapes) are generated via the OpenAPI pipeline — see [OpenAPI Type Generation](openapi-types.md)
- **Frontend-only types** (Inertia props structure, UI state, component variants) are written manually in `resources/js/lib/types/`
- All types are exported from the `@/lib/types` barrel — import from there, not from `@/Types/swagger` directly
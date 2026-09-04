---
paths:
  - 'app/Http/Controllers/**'
---

# Controllers

## Inline request validation
Validate input with `request()->validate([...])` or `$request->validate([...])` directly in the controller method. Do not introduce Form Request classes for new controllers.

## Raw request() input access
Read input via the global `request('field')` helper (or `$request->input()`), not typed accessors like `->string()`/`->integer()`/`->enum()`.

## Multi-method resource-shaped controllers
Group related actions (index/show/create/edit/store/update/destroy plus custom actions) into one controller class. Reserve `__invoke` for framework-scaffolded single-purpose controllers.

## Fat controllers, no Actions/Services/Repository layer
Controllers own validation, Eloquent queries (with inline `->with()` eager-loading), and business rules directly in the method. There is no Actions/Services/Repository/Query-object layer to delegate to. Put reusable procedural logic in `app/Helpers/Helper.php` as a global function instead.

## Implicit route model binding
Type-hint models directly in controller method signatures for route model binding rather than manual `findOrFail()` lookups.

## $this->authorize() call site
Check authorization inline in controller actions with `$this->authorize('ability', $model)`, not `Gate::authorize()`, `->can()`, or route `can:` middleware.

## Pass raw Eloquent data as Inertia props
Return `Inertia::render()` with models/collections/paginators passed directly as props (optionally `Inertia::lazy()` for deferred data). Do not build API Resources or manual `toArray()` transforms.

## Use route() for links
Prefer `route('name', ...)` for all links. `url('/')` is used only as the fixed target for generic notification action buttons.

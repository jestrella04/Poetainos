---
paths:
  - 'app/Http/Controllers/**'
  - app/Http/Controllers/AdminController.php
---

# Controllers

## Inline request validation
Validate input with `request()->validate([...])` or `$request->validate([...])` directly in the controller method. Do not introduce Form Request classes for new controllers.

## Raw request() input access
Read input via the global `request('field')` helper (or `$request->input()`), not typed accessors like `->string()`/`->integer()`/`->enum()`.

## Multi-method resource-shaped controllers
Group related actions (index/show/create/edit/store/update/destroy plus custom actions) into one controller class. Reserve `__invoke` for framework-scaffolded single-purpose controllers.

## Multi-step logic lives in app/Services
Controllers validate, authorize, call, and respond. Eloquent queries with inline `->with()` eager-loading stay in the controller. Multi-step business logic (image processing, aura/karma calculation, verification codes) goes in a plain class under `app/Services`. Small stateless utilities still go in `app/Helpers/Helper.php` as global functions. There is no Actions/Repository/Query-object layer.

## Implicit route model binding
Type-hint models directly in controller method signatures for route model binding rather than manual `findOrFail()` lookups.

## $this->authorize() call site
Check authorization inline in controller actions with `$this->authorize('ability', $model)`, not `Gate::authorize()`, `->can()`, or route `can:` middleware.

## Pass raw Eloquent data as Inertia props
Return `Inertia::render()` with models/collections/paginators passed directly as props (optionally `Inertia::optional()` for deferred data). Do not build API Resources or manual `toArray()` transforms.

## Use route() for links
Prefer `route('name', ...)` for all links. `url('/')` is used only as the fixed target for generic notification action buttons.

## Flash messages are i18n keys sent with Inertia::flash()
Confirm an action with `Inertia::flash(['message' => 'namespace.key', 'color' => 'success'])` before redirecting; the message is a key from `resources/js/i18n/es.json`, not a translated sentence. Don't use `session()->flash('message', …)`; the layouts' snackbar reads Inertia's flash data.

## Delete content through ContentDeleter
Delete writings, comments and users with `App\Services\ContentDeleter`, never `->delete()` in a controller: it also removes the polymorphic likes, notifications and stored images the database can't cascade to, deleting files only after the transaction commits.

## Writing listings go through Controller::writingsIndex()
Pass the base writings query to `writingsIndex()`; it resolves the requested sort and applies `visibleTo()` (blocked authors), `withListingRelations()` and `sorted()`. Don't repeat those scopes or `resolveSort()` in a controller.

## counter.dev token in the analytics iframe URL is an accepted risk
AdminController::analytics() embeds the counter.dev dashboard with its access token in the URL query, because counter.dev offers no other way to authenticate an embedded dashboard. Treat that token as exposed to admins' browser history and counter.dev's logs: it only grants read access to site analytics. Don't reuse the pattern for any credential that grants more.

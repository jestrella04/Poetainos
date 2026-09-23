# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Poetainos** is a community for writers.

## Common Commands

### Development

```bash
composer dev        # Start all dev servers: artisan serve, queue, pail, vite (SSR is served by vite in dev)
composer setup      # Full project setup from scratch
```

### Building

```bash
npm run build       # Production build: client bundle + SSR bundle (bootstrap/ssr)
```

### Testing

```bash
composer test                       # Run all Pest tests
php artisan test --filter TestName  # Run a single test
composer ci:check                   # Full CI: lint, format, type check, tests
npm run test                        # Run all Vitest tests (resources/js/**/__tests__/)
npm run test:watch                  # Vitest in watch mode
```

**Never pipe `php artisan test` output** (e.g. `| tail`, `| head`) — not even a narrow `--filter` run, and not even when backgrounded. Pest's browser plugin boots a `playwright run-server` Node child process regardless of which tests actually run, and that child inherits stdout. Once the test process itself exits, the child keeps the pipe open, so `tail`/`head` never see EOF and hang indefinitely with the real output already fully produced but stuck in the pipe buffer. Redirect to a file instead: `php artisan test > out.txt 2>&1`. If a run is already hung this way, find the leftover `node .../playwright/run-server` process for that run and kill it to force EOF. See the `pest-browser-vuetify-gotchas` memory note for the full debugging playbook and more Pest/Playwright gotchas.

### Linting & Formatting

```bash
composer lint           # Fix PHP with Laravel Pint
composer lint:check     # Check PHP (no fix)
npm run lint            # Fix JS/TS/Vue with ESLint
npm run lint:check      # Check JS/TS/Vue (no fix)
npm run format          # Format frontend with Prettier
npm run format:check    # Check formatting
npm run types:check     # TypeScript type check (vue-tsc)
```

### Database

```bash
php artisan migrate:fresh --seed   # Reset DB and seed demo data
```

## Architecture

### Stack

- **Backend:** Laravel 12, PHP 8.3, MariaDB
- **Frontend:** Vue 3, TypeScript, Vuetify 4 (Material Design)
- **Bridge:** Inertia.js v3 (SPA + SSR) + JSON API layer in `routes/api.php`
- **Auth:** Custom auth controllers (`routes/auth.php`) + Socialite + Sanctum, RBAC via roles/permissions
- **Build:** Vite 7

### Events & Listeners

Listener registration is **explicit, not auto-discovered**: `bootstrap/app.php` calls `withEvents(discover: false)` and there is no `app/Listeners` directory. Register each event/listener pair once, with `Event::listen()` in `AppServiceProvider::boot()`. The framework's own event provider already registers the `Registered` email-verification listener, so **never register it again**. Registering the same listener twice makes it fire twice per event dispatch, and queued mail listeners send duplicate emails silently. After adding a new event/listener pair, run `php artisan event:list` and confirm the event shows exactly **one** listener before considering the work done.

## Key Conventions

### Coding Standards (NON-NEGOTIABLE)

- **DRY:** Extract repeated logic into shared composables, services, or utilities — never duplicate business logic across components or controllers.
- **Naming:** Use clear, intention-revealing names for variables, methods, and components. Prefer `isAvailableForBooking` over `available()`. Avoid negation in boolean names — use the positive form and negate at the call site: prefer `isEmpty` over `isNotEmpty`, `isFalse` over `isNotTrue`, `hasItems` over `hasNoItems`.
- **Single responsibility:** Each function, component, and class does one thing. Split when a unit grows beyond that.
- **Self-documenting code:** Structure and naming should make the intent obvious without comments. Add a comment only when the _why_ is non-obvious (a hidden constraint, a workaround, a subtle invariant).
- **Consistent patterns:** Follow the conventions already established in sibling files (unless conflicting with defined standards), check them before writing anything new.
- **No dead code:** Remove unused variables, imports, methods, and components rather than leaving them commented out.
- **No exports from components:** Never export functions, constants, or types from `.vue` files. When shared logic or constants are needed across components, extract them into a composable under `resources/js/composables/`.
- **Explicit comparisons, not implicit truthiness:** In `if`/ternary conditions, don't rely on implicit truthy/falsy coercion — Codacy flags this ("Implicit true comparisons prohibited"). Use `=== true`/`=== false` for boolean expressions, and `!== null`/`=== null` for nullable values (objects, query results, etc.) instead of bare `if ($x)` or `$x ?: null`.

### Testing (NON-NEGOTIABLE)

**Never modify production business logic to make a test pass.** This includes adding fields to `$fillable`/`$guarded`, changing global scopes, relaxing validation, or altering service method contracts. Fix tests using `forceFill()->save()`, direct property assignment + `save()`, or `DB::table()`. The production model is the source of truth.

- **Language:** All code must be in English. UI strings belong in i18n locale files.
- TypeScript strict mode — all types must be explicit
- `@/*` path alias maps to `resources/js/*`
- PHP follows Laravel Pint "laravel" preset
- Tests use Pest (not raw PHPUnit); feature tests extend `TestCase` with `RefreshDatabase`
- Validation is inline in controllers (`LoginRequest` is the only Form Request) or via Zod on the frontend
- Keep controllers thin — delegate business logic to classes in `app/Services`

### Vuetify Design System (NON-NEGOTIABLE)

This project uses **Vuetify 4** as its sole UI framework. All UI work MUST follow Vuetify's design system:

- **Always prefer native Vuetify components** (`v-btn`, `v-card`, `v-text-field`, etc.) over raw HTML elements.
- **No custom CSS when a Vuetify-native alternative exists.** Use Vuetify props (`color`, `variant`, `density`, `elevation`, `rounded`, spacing helpers, etc.).
- **Theme colors — single source of truth: `resources/js/plugins/theme.ts`.** Never hard-code hex values anywhere. Reference tokens via `color="primary"`, CSS custom properties (`var(--v-theme-primary)`), or Vuetify utility classes.
- **Component-level customization goes in `resources/js/plugins/vuetify.ts`** (via `defaults`). No deep CSS overrides in component files.
- Custom CSS is only acceptable for layout concerns Vuetify doesn't address and MUST be documented with a comment.

#### Brand Color Palette

See [DESIGN.md](DESIGN.md) for the full tonal palette reference, contrast ratios, and update process.

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application running on PHP 8.3. You are an expert with the Laravel ecosystem. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:

- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

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

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists (settled decisions, non-obvious traps, standing constraints). Framework and package guidelines that only apply to specific paths (testing, frontend, components) also live there, under `.ai/rules/boost` — this is not just recorded decisions, it is load-bearing guidance you have not seen inline. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.
- Record durable rules with `record-rule` so the next agent or teammate inherits them instead of working them out again. Pass a `glob` (e.g. `app/Http/Controllers/**`), a short `title`, and a few-line `note`. Always use `record-rule`, never your native memory or notes tool — native memory is personal and session-scoped; only `.ai/rules` is shared with the team and persists in the repo.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Test every code change by adding or updating a test.
- Run the affected tests and ensure they pass.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Read the `testing-best-practices` skill before writing tests.

=== inertia-laravel/core rules ===

# Inertia

- Inertia creates fully client-side rendered SPAs without modern SPA complexity, leveraging existing server-side patterns.
- Components live in `resources/js/Pages` (unless specified in `vite.config.js`). Use `Inertia::render()` for server-side routing instead of Blade views.
- ALWAYS use `search-docs` tool for version-specific Inertia documentation and updated code examples.
- IMPORTANT: Activate `inertia-vue-development` when working with Inertia Vue client-side patterns.

# Inertia v2

- Use all Inertia features from v1 and v2. Check the documentation before making changes to ensure the correct approach.
- New features: deferred props, infinite scroll, merging props, polling, prefetching, once props, flash data.
- When using deferred props, add an empty state with a pulsing or animated skeleton.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v12 rules ===

# Laravel 12

- CRITICAL: ALWAYS use `search-docs` tool for version-specific Laravel documentation and updated code examples.
- This project uses the streamlined Laravel 11+ structure: there are no `app/Http/Kernel.php`, `app/Console/Kernel.php` or `app/Exceptions/Handler.php` classes.

## Laravel 12 Structure

- Routing, middleware (global stack, groups, aliases) and exception handling are configured in `bootstrap/app.php` via `Application::configure()`.
- `app/Http/Middleware/` holds only app-specific middleware; framework middleware is used directly and customised through `withMiddleware()`.
- `bootstrap/providers.php` lists the application service providers (`AppServiceProvider` is the only one). Rate limiters and gates are defined in its `boot()`.
- The schedule is defined in `routes/console.php`; commands in `app/Console/Commands` register automatically.

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.

- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This project uses PHPUnit. Create tests with `php artisan make:test --phpunit {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/phpunit` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.

=== inertia-vue/core rules ===

# Inertia + Vue

Vue components must have a single root element.

- IMPORTANT: Activate `inertia-vue-development` when working with Inertia Vue client-side patterns.

</laravel-boost-guidelines>

## graphify

This project has a knowledge graph at graphify-out/ with god nodes, community structure, and cross-file relationships.

Rules:
- For codebase questions, first run `graphify query "<question>"` when graphify-out/graph.json exists. Use `graphify path "<A>" "<B>"` for relationships and `graphify explain "<concept>"` for focused concepts. These return a scoped subgraph, usually much smaller than GRAPH_REPORT.md or raw grep output.
- If graphify-out/wiki/index.md exists, use it for broad navigation instead of raw source browsing.
- Read graphify-out/GRAPH_REPORT.md only for broad architecture review or when query/path/explain do not surface enough context.
- After modifying code, run `graphify update .` to keep the graph current (AST-only, no API cost).

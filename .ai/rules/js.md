---
paths:
  - 'resources/js/**/*.vue'
  - 'resources/js/**'
---

# Js

## Vue translations use dotted namespaced keys
Call `$t('namespace.short-key')` against nested keys defined in `resources/js/i18n/es.json`. Never use full sentences as keys here.

## Requests go through Inertia's useForm and useHttp
Submit page forms with `useForm` (controllers answer with a redirect and `Inertia::flash()`), and make in-page JSON requests with `useHttp`; axios is not a dependency. Spread `useRequestFailure()`'s `onHttpException`/`onNetworkError` into each request, and wrap `useHttp` promises in its `whenSettled()`, since useHttp also rejects after reporting a failure.

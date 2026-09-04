---
paths:
  - 'routes/*.php'
---

# Routes

## Controller-class route handlers
Define routes with controller-class array notation (`[Controller::class, 'method']`), not closures.

## Middleware via route groups
Assign middleware at the route/group level with `->middleware()`. Avoid controller constructor `$this->middleware()` calls.

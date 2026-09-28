---
paths:
  - 'routes/*.php'
  - routes/auth.php
---

# Routes

## Controller-class route handlers
Define routes with controller-class array notation (`[Controller::class, 'method']`), not closures.

## Middleware via route groups
Assign middleware at the route/group level with `->middleware()`. Avoid controller constructor `$this->middleware()` calls.

## Email-first login is an accepted enumeration oracle
POST /email (email.check) tells the login UI whether an address has an account, and registration's unique:users error does the same. This is a deliberate UX trade-off: keep both behind the `email-check` rate limiter (5/min + 50/day per IP). Every other auth endpoint (forgot-password, reset) must answer identically for known and unknown addresses.

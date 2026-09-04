---
paths:
  - 'app/Policies/**'
---

# Policies

## Policy classes, auto-discovered
Authorize model actions via Policy classes named `{Model}Policy`. Rely on Laravel's auto-discovery instead of registering them in `AuthServiceProvider::$policies`.
